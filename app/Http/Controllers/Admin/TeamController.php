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

        $validator = Validator::make($request->all(), Team::$rules, Team::$messages);

        try {

            if ($validator->fails()) {
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

            // Create the team member within a transaction
            DB::beginTransaction();

            // Create the team member
            Team::create($validatedData);

            // Invalidate cache
            Team::invalidateTeamCache();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Team member created successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create team member: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            DB::rollBack();
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

        try {

            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            // Sanitize input
            $validatedData['name'] = strip_tags($validatedData['name']);
            $validatedData['bio'] = strip_tags($validatedData['bio']);

            // Set updated_at to current time
            $validatedData['updated_at'] = now();

            // Update the team member within a transaction
            DB::beginTransaction();

            // Update the team member
            $teamMember->update($validatedData);

            // Invalidate cache
            Team::invalidateTeamCache();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Team member updated successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to update team member: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            DB::rollBack();
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
