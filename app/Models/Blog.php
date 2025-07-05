<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Log;


class Blog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'excerpt',
        'content',
        'image',
        'image_public_id',
        'author_name',
        'published_at',
        'category',
        'status',
        'tags',
        'comments',
        'likes',
        'dislikes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
        'tags' => 'array',
        'comments' => 'array',
        'likes' => 'array',
        'dislikes' => 'array',
    ];

    /**
     * Convert tags string to array if it's a string
     *
     * @param mixed $tags
     * @return array
     */
    public static function convertTagsToArray($tags)
    {
        if (is_string($tags)) {
            $tagsArray = array_map('trim', explode(',', $tags));
            return $tagsArray;
        }

        return $tags;
    }


    /**
     * Get the validation rules for blog creation/update
     *
     * @return array
     */
    public static function rules()
    {
        return [
            'title' => 'required|string',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'author_name' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string',
            'tags' => 'required',
        ];
    }

    public static function updateRoles()
    {
        return [
            'title' => 'required|string',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'image' => [
                'sometimes',
                'nullable',
                function ($attribute, $value, $fail) {
                    if (is_string($value) && !is_file($value)) {
                        // Validate URL
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $fail('The avatar must be either a valid URL or an image file.');
                        }
                    } else {
                        // Validate file
                        $validator = Validator::make(
                            ['image' => $value],
                            ['image' => 'image|mimes:jpeg,png,jpg,webp|max:2048']
                        );
                        if ($validator->fails()) {
                            $fail($validator->errors()->first('image'));
                        }
                    }
                }
            ],
            'author_name' => 'required|string',
            'category' => 'required|string',
            'status' => 'required|string',
            'tags' => 'required',
        ];
    }



    /**
     * Handle upload item image
     */
    public static function handleUploadItemImage($request, $uploadHandler, &$validatedData)
    {
        try {
            $uploadResult = $uploadHandler->uploadPhoto(
                $request->file('image'),
                'blogs'
            );
            $validatedData['image'] = $uploadResult['avatar'];
            $validatedData['image_public_id'] = $uploadResult['avatar_public_id'];
        } catch (\Exception $e) {
            Log::error('Failed to upload blog image: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return self::validationFailed("Failed to upload image, try again");
        }
    }


    /**
     * Handle image update for a blog
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Blog $blog
     * @param \App\Helpers\handelUploadPhoto $uploadHandler
     * @param array &$updateData
     * @return void
     */
    public static function handleImageUpdate($request, $blog, $uploadHandler, &$updateData)
    {
        // Delete old image if exists
        if ($request->hasFile('image')) {
            $uploadHandler->deletePhoto($blog->image_public_id);

            // Upload new image
            $uploadResult = $uploadHandler->uploadPhoto(
                $request->file('image'),
                'blogs'
            );

            // Update the blog model directly
            $blog->image = $uploadResult['avatar'];
            $blog->image_public_id = $uploadResult['avatar_public_id'];

            // Also update the updateData array for any additional processing
            $updateData['image'] = $uploadResult['avatar'];
            $updateData['image_public_id'] = $uploadResult['avatar_public_id'];

        }
    }


    /**
     * Delete image for blog
     *
     * @param string $imagePublicId
     * @param \App\Helpers\handelUploadPhoto $uploadHandler
     * @param int $id
     * @return void
     */
    public static function deleteItemImage($imagePublicId, $uploadHandler, $id)
    {
        try {
            $uploadHandler->deletePhoto($imagePublicId);
        } catch (\Exception $e) {
            // Log the error but don't fail the request
            Log::error('Failed to delete blog image: ' . $e->getMessage(), [
                'blog_id' => $id,
                'public_id' => $imagePublicId,
                'exception' => $e
            ]);
            return self::validationFailed("Failed to delete image");
        }
    }


    /**
     * Forget blog cache
     */
    public static function forgetBlogCache($id)
    {
        return Cache::forget('blogs') && Cache::forget('blog_' . $id);
    }
}
