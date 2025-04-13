<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable ,  MediaAlly,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'avatar',
        'avatar_public_id',
        'address',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        'created_at',
        'avatar_public_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //* Create a new user
    public static function createUser(array $userData, $avatarFile)
    {
        // Upload avatar to Cloudinary
        $uploadResult = Cloudinary::upload($avatarFile->getRealPath(), [
            'folder' => 'laravel-restaurant/users',
            'public_id' => 'user_' . time(),
            'transformation' => [
                'width' => 400,
                'height' => 400,
                'crop' => 'fill',
                'quality' => 'auto'
            ]
        ]);

        $avatarUrl = $uploadResult->getSecurePath();
        $avatarPublicId = $uploadResult->getPublicId();

        // Create user
        $user = self::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'],
            'password' => Hash::make($userData['password']),
            'avatar' => $avatarUrl,
            'avatar_public_id' => $avatarPublicId,
        ]);
        return [
            'user' => $user,
        ];
    }

    public static function login($request) {
            // Validate incoming request first
            $validated = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

         // Then sanitize the validated data
            $sanitizedData = [
            'email' => filter_var(trim($validated['email']), FILTER_SANITIZE_EMAIL),
            'password' => trim($validated['password']),
        ];
 // Check if user exit in Database by email
        $user = self::where('email', $sanitizedData['email'])->first(); // Find user by email
        if (!$user) {
            if (!$user) {
                throw new \Exception('User not found, Create Account', 404);
            }
        }
        // Check if password is correct
        if (!password_verify($sanitizedData['password'], $user->password)) {
            throw new \Exception('Password Not Correct', 401);
        }
        return['user' =>$user];
    }

}
