<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Log;

class Team extends Model
{

    // if Member not exit
    public static function checkMemberIsExit($teamMember)
    {
        if (!$teamMember) {
            return response()->json([
                'status' => 'error',
                'message' => 'Team member not found'
            ], 404);
        }
    }

    protected $fillable = [
        'name',
        'role',
        'hire_date',
        'salary',
        'email',
        'image',
        'image_public_id',
        'bio',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        // 'salary',
    ];

    protected $dates = [
        'deleted_at',
        'hire_date',
        'is_active'
    ];



    /**
     * Validation create Member rules
     */
    public static $rules = [
        'name' => 'required|string|max:55',
        'role' => ['required', 'string', 'in:Head Chef,Sous Chef,Pastry Chef,Line Cook,Prep Cook,Dishwasher,Server,Bartender,Hostess,Busser,Manager'],
        'hire_date' => 'required|date|before_or_equal:today',
        'salary' => 'required|integer|min:0|max:1000000',
        'email' => [
            'required',
            'email:rfc,dns',
            'max:255',
            'unique:teams,email',
            'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
        ],
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'bio' => 'required|string|max:1000',
        'is_active' => 'required|boolean',
    ];

    /**
     * Validation update Member rules
     */
    public static $rulesUpdate = [
        'name' => 'required|string|max:255',
        'bio' => 'required|string',
        'role' => ['required', 'string', 'in:Head Chef,Sous Chef,Pastry Chef,Line Cook,Prep Cook,Dishwasher,Server,Bartender,Hostess,Busser,Manager'],
        'salary' => 'required|integer|min:0|max:1000000',
        'is_active' => 'required|boolean',
    ];




    /**
     * Validation messages
     */
    public static $messages = [
        'role.in' => 'The selected role is invalid. Valid roles are: Head Chef, Sous Chef, Pastry Chef, Line Cook, Prep Cook, Dishwasher, Server, Bartender, Hostess, Busser, Manager',
        'email.regex' => 'Please enter a valid email address.',
        'name.regex' => 'Name can only contain letters, spaces, and hyphens.',
        'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
        'image.max' => 'The image must not be larger than 2MB.',
    ];



    /**
     * Handle uploading a team member's image
     *
     * @param \Illuminate\Http\Request $request
     * @return array|array[]
     */
    protected static function handleUploadItemImage($request, $uploadHandler)
    {
        try {
            $uploadResult = $uploadHandler->uploadPhoto(
                $request->file('image'),
                'team'
            );
            return [
                'image' => $uploadResult['avatar'],
                'image_public_id' => $uploadResult['avatar_public_id']
            ];
        } catch (\Exception $e) {
            Log::error('Failed to upload team member image: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::validationFailed("Failed to upload image, try again");
        }
    }


    /**
     * Invalidate the team members cache
     */
    protected static function invalidateTeamCache()
    {
        Cache::forget('team_members');
    }

}
