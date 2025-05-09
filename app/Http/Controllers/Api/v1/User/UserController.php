<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\UserServiceInterface;
use App\Domain\Property\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for Users management"
 * )
 *
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
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
     * @OA\Get(
     *     path="/api/v1/users",
     *     operationId="getUsersList",
     *     tags={"Users"},
     *     summary="Get paginated list of users",
     *     description="Retrieves a paginated list of users with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for filtering users",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter users by role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"user", "manager", "admin"})
     *     ),
     *     @OA\Parameter(
     *         name="sort_field",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Direction to sort",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="meta", type="object"),
     *             @OA\Property(property="links", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not enough permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Not enough permissions.")
     *         )
     *     )
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/users/all",
     *     operationId="getAllUsers",
     *     tags={"Users"},
     *     summary="Get all users without pagination",
     *     description="Retrieves all users with optional filtering",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for filtering users",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filter users by role",
     *         required=false,
     *         @OA\Schema(type="string", enum={"user", "manager", "admin"})
     *     ),
     *     @OA\Parameter(
     *         name="sort_field",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Direction to sort",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not enough permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Not enough permissions.")
     *         )
     *     )
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     summary="Get user by ID",
     *     description="Retrieves a specific user by their ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to return",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not enough permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Not enough permissions.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     )
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/users",
     *     operationId="createUser",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     description="Creates a new user (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User creation data",
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"user", "manager", "admin"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User created successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not enough permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Not enough permissions.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to create user"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     *
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
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     summary="Update an existing user",
     *     description="Updates a user's details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to update",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User update data",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="role", type="string", enum={"user", "manager", "admin"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not enough permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Not enough permissions.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to update user"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     *
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
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     description="Deletes a user (admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to delete",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete own account",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You cannot delete your own account.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not enough permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Not enough permissions.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to delete user"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/users/{id}/change-password",
     *     operationId="changeUserPassword",
     *     tags={"Users"},
     *     summary="Change user password",
     *     description="Changes a user's password",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to change password",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Password change data",
     *         @OA\JsonContent(
     *             required={"current_password", "password", "password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="oldpassword"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password changed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not enough permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Not enough permissions.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to change password",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to change password"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/users/role/{role}",
     *     operationId="getUsersByRole",
     *     tags={"Users"},
     *     summary="Get users by role",
     *     description="Retrieves users filtered by role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         description="Role to filter by",
     *         required=true,
     *         @OA\Schema(type="string", enum={"user", "manager", "admin"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid role specified",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid role specified.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Not enough permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. Not enough permissions.")
     *         )
     *     )
     * )
     *
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

/**
 * @OA\Schema(
 *     schema="User",
 *     required={"id", "name", "email", "role"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", enum={"user", "manager", "admin"}, example="user"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
