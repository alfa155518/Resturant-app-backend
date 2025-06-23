<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\handelUploadPhoto;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{

    protected $uploadHandler;

    public function __construct(handelUploadPhoto $uploadHandler)
    {
        $this->uploadHandler = $uploadHandler;
    }

    /**
     * Get all team members
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembers()
    {
        // Cache the team members for 24 hours (1440 minutes)
        $team = Cache::remember('team_members', 1440, function () {
            return Team::all();
        });

        if ($team->isEmpty()) {
            return self::notFound('Team');
        }

        return response()->json([
            'status' => 'success',
            'data' => $team
        ], 200);
    }

    /**
     * Create a new team member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMember(Request $request): JsonResponse
    {
        // Preprocess is_active to ensure it's a valid boolean
        $input = $request->all();
        $input['is_active'] = filter_var($input['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Validate the modified input
        $validator = Validator::make($input, Team::$rules, Team::$messages);

        try {
            if ($validator->fails()) {
                Log::error('Failed to create team member: ' . $validator->errors()->first(), [
                    'exception' => $validator->errors()->first()
                ]);
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            // Sanitize input
            $validatedData['name'] = strip_tags($validatedData['name']);
            $validatedData['bio'] = strip_tags($validatedData['bio']);
            $validatedData['email'] = filter_var($validatedData['email'], FILTER_SANITIZE_EMAIL);

            // Handle image upload
            if ($request->hasFile('image')) {
                $uploadResult = Team::handleUploadItemImage($request, $this->uploadHandler);
                $validatedData = array_merge($validatedData, $uploadResult);
            }

            // Create the team member
            Team::create($validatedData);

            // Invalidate cache
            Team::invalidateTeamCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Team member created successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create team member: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }
    /**
     * Update a team member.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMember(Request $request, $id): JsonResponse
    {
        $teamMember = Team::find($id);

        if (!$teamMember) {
            return self::notFound('Team member');
        }

        $validator = Validator::make($request->all(), Team::$rulesUpdate, Team::$messages);

        $updateData = [];

        try {
            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            // Handle image upload if present
            if ($request->hasFile('image')) {
                Team::handleImageUpdate($request, $teamMember, $this->uploadHandler, $updateData);
            }

            // Get all request data except image and _method
            $requestData = $request->except(['image', '_method']);

            // Filter out undefined/null values and prepare data for update
            $updateData = array_merge($updateData, array_filter($requestData, function ($value) {
                return $value !== 'undefined' && $value !== null;
            }));

            // Sanitize input and prepare update data
            $updateData = array_merge($updateData, [
                'name' => strip_tags($validatedData['name']),
                'bio' => strip_tags($validatedData['bio']),
                'is_active' => filter_var($validatedData['is_active'], FILTER_VALIDATE_BOOLEAN),
                'updated_at' => now()
            ]);

            // Update the team member with the prepared data
            $teamMember->update($updateData);

            // Invalidate cache
            Team::invalidateTeamCache();

            return response()->json([
                'status' => 'success',
                'message' => 'Team member updated successfully',
                'data' => $teamMember->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update team member: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }

    /**
     * Delete a team member.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMember($id): JsonResponse
    {
        $teamMember = Team::find($id);

        if (!$teamMember) {
            return self::notFound('Team member');
        }

        try {
            // Delete the team member within a transaction
            DB::beginTransaction();

            // Delete the team member
            $teamMember->delete();

            // Delete the team member image
            $this->uploadHandler->deletePhoto($teamMember->image_public_id);

            // Invalidate cache
            Team::invalidateTeamCache();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Team member deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to delete team member: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            DB::rollBack();
            return self::serverError();
        }
    }

}
