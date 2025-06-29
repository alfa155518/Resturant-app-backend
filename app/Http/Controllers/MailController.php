<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Mail\DemoMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Illuminate\Support\Facades\Validator;

class MailController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function forgetPassword(Request $request)
    {
        try {

            $request->validate([
                'email' => 'required|email',
            ]);

            // create filter  sanitize user email
            $userMail = trim(filter_var($request->email, FILTER_SANITIZE_EMAIL));

            // check if user email exit in db
            $user = User::where('email', $userMail)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'An error occurred',
                    'message' => 'User Not found'
                ], 404);
            }

            // Check if user has existing tokens
            if ($user->tokens->count() > 0) {
                // Delete existing tokens
                $user->tokens()->delete();
            }
            ;

            $token = $user->createToken('authToken')->plainTextToken;

            $mailData = [
                'title' => 'Mail from Gourmet Haven Restaurant ',
                'body' => 'click on link to reset your password',
                'link' => env('FRONTEND_URL') . '/register/resetPassword?token=' . $token . '&email=' . urlencode($userMail),
            ];

            FacadesMail::to($userMail)->send(new DemoMail($mailData));

            return response()->json([
                'message' => 'Check your Email , Message is sent successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send email',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);

            $email = trim(filter_var($request->email, FILTER_SANITIZE_EMAIL));

            $newUser = User::updateOrCreate(
                ['email' => $email],
                ['password' => Hash::make($request->password)]
            );

            return response()->json([
                'message' => 'Password reset successfully',
                'user' => $newUser,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a contact form email
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function contactMessage(Request $request)
    {
        try {
            // Validate sanitized data
            $validated = Validator::make(ContactMail::$sanitizedData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email:rfc,dns|max:255',
                'phone' => 'required|string|max:20',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);

            if ($validated->fails()) {
                return self::validationFailed($validated->errors()->first());
            }

            // Get validated and sanitized data
            $validatedData = $validated->validated();

            // Send email to admin/restaurant
            $adminEmail = env('MAIL_FROM_ADDRESS', 'gourmethaven@gmail.com');
            FacadesMail::to($adminEmail)
                ->send(new ContactMail($validatedData));


            return response()->json([
                'status' => 'success',
                'message' => 'Your message has been sent successfully! We will get back to you soon.'
            ], 200);

        } catch (\Exception $e) {
            Log::info(
                message: $e->getMessage()
            );
            return self::serverError();
        }
    }


}
