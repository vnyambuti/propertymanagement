<?php
namespace App\Repositories;


use App\Domain\Property\Models\User;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * Register a new user.
     *
     * @param array $userData
     * @return User
     */
    public function register(array $userData): User
    {
        $userData['password'] = Hash::make($userData['password']);

        return User::create($userData);
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create password reset token.
     *
     * @param string $email
     * @return string
     */
    public function createPasswordResetToken(string $email): string
    {
        $token = Str::random(64);

        // Delete any existing password reset tokens for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Create new password reset token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        return $token;
    }

    /**
     * Reset user password.
     *
     * @param string $token
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function resetPassword(string $token, string $email, string $password): bool
    {
        // Find the token in the database
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$passwordReset) {
            return false;
        }

        // Check if token is expired (tokens are valid for 60 minutes)
        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return false;
        }

        // Update the user's password
        $user = $this->findUserByEmail($email);
        if (!$user) {
            return false;
        }

        $user->password = Hash::make($password);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return true;
    }
}
