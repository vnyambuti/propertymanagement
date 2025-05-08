<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\UserServiceInterface;
use App\Domain\Property\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class UserController extends Controller
{
    protected $userService;

    /**
     * Create a new UserController instance.
     *
     * @param UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
        // $this->middleware('auth:sanctum');
    }

    /**
     * Get a list of users with pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Check permissions - only admin and managers can list users
        if (!$request->user()->isManager()) {
            return response()->json([
                'message' => 'Unauthorized. Not enough permissions.',
            ], 403);
        }

        $perPage = $request->input('per_page', 15);
        $filters = [
            'search' => $request->input('search'),
            'role' => $request->input('role'),
            'sort_field' => $request->input('sort_field'),
            'sort_direction' => $request->input('sort_direction'),
        ];

        $users = $this->userService->getPaginatedUsers($perPage, $filters);

        return response()->json($users);
    }

    /**
     * Get all users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function all(Request $request): JsonResponse
    {
        // Check permissions - only admin and managers can list all users
        if (!$request->user()->isManager()) {
            return response()->json([
                'message' => 'Unauthorized. Not enough permissions.',
            ], 403);
        }

        $filters = [
            'search' => $request->input('search'),
            'role' => $request->input('role'),
            'sort_field' => $request->input('sort_field'),
            'sort_direction' => $request->input('sort_direction'),
        ];

        $users = $this->userService->getAllUsers($filters);

        return response()->json($users);
    }

    /**
     * Get a specific user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // Allow users to view their own profile or admin/managers to view any profile
        if ($request->user()->id !== $id && !$request->user()->isManager()) {
            return response()->json([
                'message' => 'Unauthorized. Not enough permissions.',
            ], 403);
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json($user);
    }

    /**
     * Create a new user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Check permissions - only admin can create users
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Not enough permissions.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => [
                'sometimes',
                'string',
                Rule::in(['user', 'manager', 'admin']),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $this->userService->createUser($validator->validated());

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Allow users to update their own profile or admin to update any profile
        // Managers can only update regular users, not other managers or admins
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $currentUser = $request->user();

        if ($currentUser->id !== $id) {
            if (!$currentUser->isManager()) {
                return response()->json([
                    'message' => 'Unauthorized. Not enough permissions.',
                ], 403);
            }

            // Manager trying to update another manager or admin
            if ($currentUser->role === 'manager' && ($user->role === 'manager' || $user->role === 'admin')) {
                return response()->json([
                    'message' => 'Unauthorized. Managers cannot update other managers or admins.',
                ], 403);
            }
        }

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
        ];

        // Only admins can change roles
        if ($currentUser->isAdmin()) {
            $rules['role'] = [
                'sometimes',
                'string',
                Rule::in(['user', 'manager', 'admin']),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updatedUser = $this->userService->updateUser($id, $validator->validated());

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $updatedUser
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        // Only admin can delete users
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Not enough permissions.',
            ], 403);
        }

        // Prevent self-deletion
        if ($request->user()->id === $id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 400);
        }

        try {
            $deleted = $this->userService->deleteUser($id);

            if ($deleted) {
                return response()->json([
                    'message' => 'User deleted successfully'
                ]);
            }

            return response()->json([
                'message' => 'User not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function changePassword(Request $request, int $id): JsonResponse
    {
        // Allow users to change their own password or admin to change any password
        if ($request->user()->id !== $id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Not enough permissions.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $success = $this->userService->changePassword(
                $id,
                $request->input('current_password'),
                $request->input('password')
            );

            if ($success) {
                return response()->json([
                    'message' => 'Password changed successfully'
                ]);
            }

            return response()->json([
                'message' => 'Failed to change password',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users by role.
     *
     * @param Request $request
     * @param string $role
     * @return JsonResponse
     */
    public function getByRole(Request $request, string $role): JsonResponse
    {
        // Check permissions - only admin and managers can filter users by role
        if (!$request->user()->isManager()) {
            return response()->json([
                'message' => 'Unauthorized. Not enough permissions.',
            ], 403);
        }

        // Validate role
        if (!in_array($role, ['user', 'manager', 'admin'])) {
            return response()->json([
                'message' => 'Invalid role specified.',
            ], 400);
        }

        $users = $this->userService->getUsersByRole($role);

        return response()->json($users);
    }
}
