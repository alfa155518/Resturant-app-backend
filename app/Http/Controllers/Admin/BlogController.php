<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\handelUploadPhoto;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Traits\AdminSecurityHeaders;
use App\Traits\UserId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    use UserId, AdminSecurityHeaders;

    protected $uploadHandler;

    public function __construct(handelUploadPhoto $uploadHandler)
    {
        $this->uploadHandler = $uploadHandler;
    }

    /**
     * Create a new blog.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBlog(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), Blog::rules());

            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();


            // Convert tags string to array if needed
            if (isset($validatedData['tags'])) {
                $validatedData['tags'] = Blog::convertTagsToArray($validatedData['tags']);
            }

            if ($request->hasFile('image')) {
                Blog::handleUploadItemImage($request, $this->uploadHandler, $validatedData);
            }

            $blog = Blog::create($validatedData);

            Blog::forgetBlogCache($blog->id);


            $response = response()->json([
                'status' => 'success',
                'message' => 'Blog Added Successfully'
            ], 201);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            Log::error('Failed to add blog: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }

    /**
     * Update blog 
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBlog(Request $request, $id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return self::notFound('Blog');
            }

            $validator = Validator::make($request->all(), Blog::updateRoles());

            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            // Convert tags string to array if needed
            if (isset($validatedData['tags'])) {
                $validatedData['tags'] = Blog::convertTagsToArray($validatedData['tags']);
            }



            // Handle image update if provided
            if ($request->hasFile('image')) {
                // This will handle deleting the old image and setting the new one
                Blog::handleImageUpdate($request, $blog, $this->uploadHandler, $validatedData);
            } elseif ($request->has('image') && is_string($request->image)) {
                $blog->image = $request->image;
            }

            // Update other fields
            $blog->fill($validatedData);
            $blog->save();
            Blog::forgetBlogCache($blog->id);

            $response = response()->json([
                'status' => 'success',
                'message' => 'Blog Updated Successfully'
            ], 200);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            Log::error('Failed to update blog: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::serverError();
        }
    }

    /**
     * Delete a blog.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBlog(Request $request, $id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return self::notFound('Blog');
            }
            // Store references to image data before deletion if needed for cleanup
            $imagePublicId = $blog->image_public_id;

            $blog->delete();

            // Delete image from storage
            if ($imagePublicId) {
                Blog::deleteItemImage($imagePublicId, $this->uploadHandler, $id);
            }

            Blog::forgetBlogCache($id);

            $response = response()->json([
                'status' => 'success',
                'message' => 'Blog deleted successfully'
            ], 200);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }
}
