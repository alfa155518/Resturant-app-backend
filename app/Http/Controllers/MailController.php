<?PHP

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\DemoMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail as FacadesMail;

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
            };

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

    public function resetPassword(Request $request) {
        try {
            $request->validate([
                'email' =>'required|email',
                'password' =>'required|min:8',
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
}
