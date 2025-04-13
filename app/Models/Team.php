<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;



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
        'bio',
        'is_active',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'salary',
    ];

    protected $dates = [
        'deleted_at',
        'hire_date',
        'is_active'
    ];
}
