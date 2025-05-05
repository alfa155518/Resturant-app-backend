<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\handelUploadPhoto;
use App\Helpers\SecurityHeaders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

class ChangePersonalUserDataController extends Controller
{

    protected $uploadHandler;

    public function __construct(handelUploadPhoto $uploadHandler)
    {
        $this->uploadHandler = $uploadHandler;
    }

    public function changePersonalData(Request $request)
    {
        try {
            // Validate token presence and format
            $bearerToken = $request->header('Authorization');

            $token = str_replace('Bearer ', '', $bearerToken);

            // Find token in database
            $tokenModel = PersonalAccessToken::findToken($token);

            // Get user from token
            $user = $tokenModel->tokenable;

            if (!$user) {
                return self::notFound('User');
            }
            // Validate request data
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|min:3|max:25',
                'email' => [
                    'sometimes',
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => 'sometimes|required|string|min:10|max:15|regex:/^[0-9+\-\s()]*$/',
                'avatar' => [
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
                                ['avatar' => $value],
                                ['avatar' => 'image|mimes:jpeg,png,jpg,webp|max:2048']
                            );
                            if ($validator->fails()) {
                                $fail($validator->errors()->first('avatar'));
                            }
                        }
                    }
                ],
                'address' => 'sometimes|required|string|min:10|max:255',
            ]);

            if ($validator->fails()) {
                return self::validationFailed($validator->errors());
            }
            // Get validated data
            $validatedData = $validator->validated();
            // Update user data
            $user->fill($validatedData);
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                //  Delete old avatar by public id if exists 
                if ($user->avatar_public_id) {
                    $this->uploadHandler->deletePhoto($user->avatar_public_id);
                }

                // Upload the new photo using the injected helper
                $uploadResult = $this->uploadHandler->uploadPhoto($request->file('avatar'));

                $user->avatar = $uploadResult['avatar'];
                $user->avatar_public_id = $uploadResult['avatar_public_id'];
            }elseif ($request->has('avatar') && is_string($request->avatar)) {
                // Handle URL
                $user->avatar = $request->avatar;
            }
            $user->save();
            // Prepare response data
            $safeUserData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'avatar' => $user->avatar,
            ];

            $response = response()->json([
                'status' => 'success',
                'message' => 'Personal data updated successfully',
                'user' => $safeUserData
            ], 200);

            // Apply security headers
            return SecurityHeaders::secureHeaders($response);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
