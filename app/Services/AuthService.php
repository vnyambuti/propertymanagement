<?php

namespace App\Services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\ValidationException;
use App\Mail\PasswordResetMail;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Repositories\Interfaces\AuthServiceInterface;

class AuthService
{
    protected $authRepository;

    /**
     * Create a new auth service instance.
     *
     * @param AuthRepositoryInterface $authRepository
     */
    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Register a new user.
     *
     * @param array $userData
     * @return array
     */
    public function register(array $userData): array
    {
        $user = $this->authRepository->register($userData);

        // Trigger registered event
        event(new Registered($user));

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Login a user.
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        $user = $this->authRepository->findUserByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke existing tokens and create new one
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Logout a user.
     *
     * @param Request $request
     * @return bool
     */
    public function logout(Request $request): bool
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return true;
    }

    /**
     * Send password reset email.
     *
     * @param string $email
     * @return bool
     */
    public function forgotPassword(string $email): bool
    {
        $user = $this->authRepository->findUserByEmail($email);

        if (!$user) {
            return false;
        }

        $token = $this->authRepository->createPasswordResetToken($email);

        // Send email with reset link
        $resetUrl = config('app.url') . '/reset-password/' . $token . '?email=' . urlencode($email);

        Mail::to($email)->send(new PasswordResetMail($resetUrl));

        return true;
    }

    /**
     * Reset user password.
     *
     * @param array $data
     * @return bool
     */
    public function resetPassword(array $data): bool
    {
        return $this->authRepository->resetPassword(
            $data['token'],
            $data['email'],
            $data['password']
        );
    }
}
