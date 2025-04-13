<?php

namespace App\Http\Controllers;

use App\Helpers\ValidateId;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{

    // Get all team members
    public function allTeamMember()
    {
        try {
            // Only get active team members and paginate results
            $teamMembers = Team::where('is_active', true)
                ->orderBy('name')
                ->select(['id', 'name', 'role', 'hire_date', 'email', 'image', 'bio'])
                ->paginate(10);

            // Create a custom response
            return response()->json([
                'status' => 'success',
                'data' => [
                    'current_page' => $teamMembers->currentPage(),
                    'total' => $teamMembers->total(),
                    'team' => $teamMembers->items(),
                    'last_page' => $teamMembers->lastPage(),
                    ]
                
                
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Team retrieval error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Single team member
    public function teamMember($id)
    {
        try {

            // Validate ID is numeric to prevent injection attacks
            $notValidId = ValidateId::validateNumeric($id);
            if ($notValidId) {
                return $notValidId;
            }

            // Get the team member by ID
            $teamMember = Team::find($id);

            // Check if the team member exists
            $notFoundMember = Team::checkMemberIsExit($teamMember);
            if ($notFoundMember) {
                return $notFoundMember;
            }

            // Return the team member
            return response()->json([
                'status' => 'success',
                'team_member' => $teamMember
            ], 200);

            // Check if the team member exists
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Team member retrieval error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
