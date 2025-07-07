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

    /**
     * Get all blogs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBlogs()
    {
        try {
            $blogs = Cache::rememberForever('blogs', function () {
                return Blog::with('comments')->get();
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

    /**
     * Get a single blog.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function singleBlog($id)
    {
        try {
            $blog = Blog::with('comments')->findOrFail($id);

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


    /**
     * Like a blog.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeBlog(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            $blog = Blog::find($id);

            if (!$blog) {
                return self::notFound('Blog');
            }

            // Get current likes and dislikes as arrays
            $likes = $blog->likes ?? [];
            $dislikes = $blog->dislikes ?? [];

            // Add user to likes if not already there
            if (!in_array($userId, $likes)) {
                $likes[] = $userId;
                // Remove from dislikes if present
                $dislikes = array_diff($dislikes, [$userId]);
            } else {
                // If user already liked, remove the like (toggle behavior)
                $likes = array_diff($likes, [$userId]);
            }

            // Update the blog with new arrays
            $blog->likes = array_values($likes); // Re-index array
            $blog->dislikes = array_values($dislikes); // Re-index array
            $blog->save();

            // Clear cache for this blog
            Blog::forgetBlogCache($id);

            $response = response()->json([
                'status' => 'success',
            ], 200);

            return SecurityHeaders::secureHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }


    /**
     * Dislike a blog.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dislikeBlog(Request $request, $id)
    {
        try {
            $userId = $this->getUserId($request);
            $blog = Blog::find($id);

            if (!$blog) {
                return self::notFound('Blog');
            }

            // Get current likes and dislikes as arrays
            $likes = $blog->likes ?? [];
            $dislikes = $blog->dislikes ?? [];

            // Add user to dislikes if not already there
            if (!in_array($userId, $dislikes)) {
                $dislikes[] = $userId;
                // Remove from likes if present
                $likes = array_diff($likes, [$userId]);
            } else {
                // If user already disliked, remove the dislike (toggle behavior)
                $dislikes = array_diff($dislikes, [$userId]);
            }

            // Re-index array
            $blog->likes = array_values($likes);
            $blog->dislikes = array_values($dislikes);
            $blog->save();

            // Clear cache for this blog
            Blog::forgetBlogCache($id);

            $response = response()->json([
                'status' => 'success',
                'data' => [
                    'likes' => $blog->likes,
                    'dislikes' => $blog->dislikes,
                    'likes_count' => count($blog->likes),
                    'dislikes_count' => count($blog->dislikes)
                ]
            ], 200);

            return SecurityHeaders::secureHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }


}


// 15|t5sWwWAXOSOaBNTMvbOj16K0EIUIx7rnWAjh8vbL0f8ce849

// 14|sWM92BGom1ExhwugTENNrOf60cPLcuTTMJGnF9FP8656eede