<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    /**
     * Create a new AuthController instance.
     *
     * @param AuthServiceInterface $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|string|in:user,manager,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userData = $validator->validated();

        // Set default role if not provided
        if (!isset($userData['role'])) {
            $userData['role'] = 'user';
        }

        $result = $this->authService->register($userData);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $result['user'],
            'token' => $result['token'],
            'token_type' => $result['token_type']
        ], 201);
    }

    /**
     * Login a user and create a token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->authService->login(
                $request->email,
                $request->password
            );

            return response()->json([
                'message' => 'User logged in successfully',
                'user' => $result['user'],
                'token' => $result['token'],
                'token_type' => $result['token_type']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }
    }

    /**
     * Logout a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);

        return response()->json([
            'message' => 'User logged out successfully'
        ]);
    }

    /**
     * Send password reset email.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->authService->forgotPassword($request->email);

        if ($success) {
            return response()->json([
                'message' => 'Password reset link sent to your email'
            ]);
        }

        return response()->json([
            'message' => 'We could not find a user with that email address'
        ], 404);
    }

    /**
     * Reset user password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->authService->resetPassword($validator->validated());

        if ($success) {
            return response()->json([
                'message' => 'Password has been reset successfully'
            ]);
        }

        return response()->json([
            'message' => 'Invalid or expired password reset token'
        ], 400);
    }

    /**
     * Get authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
