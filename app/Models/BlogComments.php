<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogComments extends Model
{
    protected $fillable = [
        'blog_id',
        'user_id',
        'name',
        'comment',
    ];

    public static function rules()
    {
        return [
            'blog_id' => 'required|exists:blogs,id',
            'name' => 'required|string|max:15',
            'comment' => 'required|string',
        ];
    }
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
