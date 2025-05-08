<?php
namespace App\Repositories\Interfaces;

use App\Domain\Property\Models\User;
use Illuminate\Http\Request;

interface AuthRepositoryInterface
{
    /**
     * Register a new user.
     *
     * @param array $userData
     * @return User
     */
    public function register(array $userData): User;

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User;

    /**
     * Create password reset token.
     *
     * @param string $email
     * @return string
     */
    public function createPasswordResetToken(string $email): string;

    /**
     * Reset user password.
     *
     * @param string $token
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function resetPassword(string $token, string $email, string $password): bool;
}
