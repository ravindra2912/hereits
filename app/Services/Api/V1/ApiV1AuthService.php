<?php

namespace App\Services\Api\V1;

use App\Mail\ResetPasswordEmail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ApiV1AuthService
{
    /**
     * Authenticate a user and issue a Passport access token.
     *
     * @param array{email: string, password: string} $credentials
     * @return array{success: bool, status_code: int, message: string, data?: array}
     */
    public function login(array $credentials): array
    {
        $identifier = trim($credentials['email']);

        $user = User::query()
            ->where(function ($query) use ($identifier) {
                $query->where('email', $identifier);
                if (is_numeric($identifier) && (int)$identifier > 0) {
                    $query->orWhere('contact', $identifier);
                }
            })
            ->first();

        if (!$user) {
            return [
                'success' => false,
                'status_code' => 404,
                'message' => 'User does not exist with the provided credentials.',
            ];
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return [
                'success' => false,
                'status_code' => 401,
                'message' => 'Password does not match our records.',
            ];
        }

        $token = $user->createToken('hereits API')->accessToken;

        return [
            'success' => true,
            'status_code' => 200,
            'message' => 'Login successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ];
    }

    /**
     * Register a new user and generate a Passport access token.
     *
     * @param array $data
     * @return array{success: bool, status_code: int, message: string, data?: array}
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = new User();
            $user->first_name = trim($data['first_name']);
            $user->last_name = trim($data['last_name']);
            $user->email = trim($data['email']);
            $user->contact = !empty($data['contact']) && $data['contact'] != 0 ? $data['contact'] : null;
            $user->dob = $data['dob'] ?? null;
            $user->role = 3; // Default Customer/User role
            $user->password = Hash::make($data['password']);
            $user->save();

            $token = $user->createToken('hereits API')->accessToken;

            return [
                'success' => true,
                'status_code' => 201,
                'message' => 'User registered successfully.',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ];
        });
    }

    /**
     * Send password reset token to user email.
     *
     * @param string $email
     * @return array{success: bool, status_code: int, message: string}
     */
    public function forgotPassword(string $email): array
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            $token = Str::random(64);
            $hashedToken = hash('sha256', $token);
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => $hashedToken,
                'created_at' => Carbon::now(),
            ]);

            try {
                $mailData = [
                    'username' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email,
                    'token' => $token,
                    'url' => route('password.reset', [$token, $email]),
                ];

                Mail::to($email)->send(new ResetPasswordEmail($mailData));
            } catch (\Throwable $e) {
                Log::error('API ForgotPassword mail dispatch failure: ' . $e->getMessage(), [
                    'email' => $email,
                ]);
            }
        }

        return [
            'success' => true,
            'status_code' => 200,
            'message' => 'If that email address is in our database, we will send you a password reset link shortly.',
        ];
    }

    /**
     * Reset password using verification token.
     *
     * @param array{email: string, token: string, password: string} $data
     * @return array{success: bool, status_code: int, message: string}
     */
    public function resetPassword(array $data): array
    {
        $hashedToken = hash('sha256', $data['token']);
        $record = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->where(function ($q) use ($data, $hashedToken) {
                $q->where('token', $hashedToken)
                  ->orWhere('token', $data['token']);
            })
            ->first();

        if (!$record) {
            return [
                'success' => false,
                'status_code' => 400,
                'message' => 'Invalid or expired password reset token.',
            ];
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return [
                'success' => false,
                'status_code' => 400,
                'message' => 'Password reset token has expired. Please request a new one.',
            ];
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return [
                'success' => false,
                'status_code' => 404,
                'message' => 'User does not exist.',
            ];
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return [
            'success' => true,
            'status_code' => 200,
            'message' => 'Password has been reset successfully.',
        ];
    }

    /**
     * Revoke Passport tokens for the authenticated user.
     *
     * @param User $user
     * @return array{success: bool, status_code: int, message: string}
     */
    public function logout(User $user): array
    {
        $token = $user->token();
        if ($token) {
            $token->revoke();
        } else {
            $user->tokens()->delete();
        }

        return [
            'success' => true,
            'status_code' => 200,
            'message' => 'Successfully logged out.',
        ];
    }

    /**
     * Update user profile information.
     *
     * @param User $user
     * @param array $data
     * @param mixed|null $profileFile
     * @return array{success: bool, status_code: int, message: string, user: User}
     */
    public function updateProfile(User $user, array $data, $profileFile = null): array
    {
        $user->first_name = trim($data['first_name']);
        $user->last_name = trim($data['last_name']);

        if (array_key_exists('contact', $data)) {
            $user->contact = !empty($data['contact']) && $data['contact'] != 0 ? $data['contact'] : null;
        }

        if (array_key_exists('dob', $data)) {
            $user->dob = $data['dob'];
        }

        if (array_key_exists('gender', $data)) {
            $user->gender = $data['gender'];
        }

        if ($profileFile) {
            if (function_exists('fileUploadStorage')) {
                if (!empty($user->profile) && function_exists('fileRemoveStorage')) {
                    fileRemoveStorage($user->profile);
                }
                $user->profile = fileUploadStorage($profileFile, 'users', 500, 500);
            } else {
                $user->profile = $profileFile->store('users', 'public');
            }
        }

        $user->save();

        return [
            'success' => true,
            'status_code' => 200,
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ];
    }

    /**
     * Update user account password.
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return array{success: bool, status_code: int, message: string}
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): array
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return [
                'success' => false,
                'status_code' => 422,
                'message' => 'Current password does not match our records.',
            ];
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return [
            'success' => true,
            'status_code' => 200,
            'message' => 'Password updated successfully.',
        ];
    }
}
