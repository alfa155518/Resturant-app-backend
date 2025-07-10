<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Reviews extends Model
{
    protected $fillable = [
        'user_id',
        'client_name',
        'client_email',
        'rating',
        'review',
        'status',
        'reply',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Validation rules
    public static function rules()
    {
        return [
            'client_name' => 'required|string|max:100',
            'client_email' => 'required|email|unique:reviews,client_email',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:1000',
        ];
    }

    // Validation messages
    public static function messages()
    {
        return [
            'client_email.unique' => 'You have already submitted a review with this email address.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot be more than 5 stars.'
        ];
    }
}
