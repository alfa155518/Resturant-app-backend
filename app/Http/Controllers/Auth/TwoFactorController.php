<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\UserId;
use Exception;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    use UserId;


    protected function getUser(Request $request)
    {
        $userId = $this->getUserId($request);

        $user = User::find($userId);

        if (!$user) {
            return self::notFound('User');
        }

        return $user;
    }

    /**
     * Enable 2FA for the user
     * 
     * @param Request $request
     * @return void
     */
    public function enable(Request $request)
    {
        try {
            $user = $this->getUser($request);
            $secret = Google2FA::generateSecretKey();
            $user->google2fa_secret = $secret;
            $user->save();

            $qrCodeUrl = Google2FA::getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );

            $renderer = new ImageRenderer(
                new RendererStyle(300),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrCodeUrl);

            // Create the response with the secret and QR code
            return response()->json([
                'secret' => $secret,
                'qrCode' => base64_encode($qrCodeSvg),
            ], 200);
        } catch (Exception) {
            return self::serverError();
        }

    }

    /**
     * Verify 2FA for the user
     * 
     * @param Request $request
     * @return void
     */
    public function verify(Request $request)
    {
        try {

            $request->validate([
                'one_time_password' => 'required|string',
            ]);

            $user = $this->getUser($request);

            if (!Google2FA::verifyKey($user->google2fa_secret, $request->one_time_password)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid OTP'], 400);
            }

            session(['2fa_verified' => true]);

            // Get the secret from the user's database record
            $secret = $user->google2fa_secret;

            return response()->json([
                'status' => 'success',
                'message' => '2FA verified successfully',
                'google2fa_secret' => $secret
            ], 200);
        } catch (Exception) {
            return self::serverError();
        }

    }

    /**
     * Disable 2FA for the user
     * 
     * @param Request $request
     * @return void
     */
    public function disable(Request $request)
    {
        try {
            $user = $this->getUser($request);

            if (!$user->google2fa_secret) {
                return response()->json(['status' => 'error', 'message' => '2FA is not enabled'], 400);
            }

            $user->google2fa_secret = null;
            $user->save();

            session()->forget('2fa_verified');

            return response()->json(['status' => 'success', 'message' => '2FA disabled successfully'], 200);
        } catch (Exception) {
            return self::serverError();
        }
    }
}
