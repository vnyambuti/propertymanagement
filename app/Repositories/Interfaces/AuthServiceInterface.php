<?php
namespace App\Repositories\Interfaces;
use Illuminate\Support\Facades\Request;

interface AuthServiceInterface
{
    /**
     * Register a new user.
     *
     * @param array $userData
     * @return array
     */
    public function register(array $userData): array;

    /**
     * Login a user.
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login(string $email, string $password): array;

    /**
     * Logout a user.
     *
     * @param Request $request
     * @return bool
     */
    public function logout(Request $request): bool;

    /**
     * Send password reset email.
     *
     * @param string $email
     * @return bool
     */
    public function forgotPassword(string $email): bool;

    /**
     * Reset user password.
     *
     * @param array $data
     * @return bool
     */
    public function resetPassword(array $data): bool;
}

