<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Traits\UserId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BlogComments;

class BlogCommentsController extends Controller
{
    use UserId;

    /**
     * Add a comment to a blog
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(Request $request)
    {
        $userId = $this->getUserId($request);
        try {

            $validator = Validator::make($request->all(), BlogComments::rules());

            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            BlogComments::create([
                'blog_id' => $validatedData['blog_id'],
                'user_id' => $userId,
                'name' => $validatedData['name'],
                'comment' => $validatedData['comment'],
            ]);
            Blog::forgetBlogCache($request->blog_id);
            return response()->json([
                'status' => 'success',
                'message' => 'Thank`s for your comment',
            ]);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }
}
