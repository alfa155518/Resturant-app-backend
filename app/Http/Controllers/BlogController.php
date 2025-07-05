<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Traits\UserId;
use Illuminate\Support\Facades\Cache;
use App\Helpers\SecurityHeaders;
use Illuminate\Http\Request;

class BlogController extends Controller
{

    use UserId;

    public function getBlogs()
    {
        try {
            $blogs = Cache::rememberForever('blogs', function () {
                return Blog::all();
            });

            $response = response()->json([
                'status' => 'success',
                'data' => $blogs
            ], 200);

            return SecurityHeaders::secureHeaders($response);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve blogs'
            ], 500);
        }
    }

    public function singleBlog($id)
    {
        try {
            $blog = Cache::rememberForever('blog_' . $id, function () use ($id) {
                return Blog::find($id);
            });

            $response = response()->json([
                'status' => 'success',
                'data' => $blog
            ], 200);

            return SecurityHeaders::secureHeaders($response);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve blog'
            ], 500);
        }
    }
}


// 15|t5sWwWAXOSOaBNTMvbOj16K0EIUIx7rnWAjh8vbL0f8ce849

// 14|sWM92BGom1ExhwugTENNrOf60cPLcuTTMJGnF9FP8656eede