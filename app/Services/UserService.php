<?php

namespace App\Services;



use App\Domain\Property\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\UserServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService implements UserServiceInterface
{
    protected $userRepository;

    /**
     * Create a new user service instance.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get users with pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->getAllPaginated($perPage, $filters);
    }

    /**
     * Get all users.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllUsers(array $filters = []): Collection
    {
        return $this->userRepository->getAll($filters);
    }

    /**
     * Get user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Create a new user.
     *
     * @param array $userData
     * @return User
     */
    public function createUser(array $userData): User
    {
        // Check if email already exists
        if ($this->userRepository->findByEmail($userData['email'])) {
            throw ValidationException::withMessages([
                'email' => ['This email address is already taken.'],
            ]);
        }

        // Set default role if not provided
        if (!isset($userData['role'])) {
            $userData['role'] = 'user';
        }

        return $this->userRepository->create($userData);
    }

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $userData
     * @return User|null
     * @throws ValidationException
     */
    public function updateUser(int $id, array $userData): ?User
    {
        // Check if user exists
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw ValidationException::withMessages([
                'id' => ['User not found.'],
            ]);
        }

        // Check if email is being changed and if it's already taken by another user
        if (isset($userData['email']) && $userData['email'] !== $user->email) {
            $existingUser = $this->userRepository->findByEmail($userData['email']);
            if ($existingUser && $existingUser->id !== $id) {
                throw ValidationException::withMessages([
                    'email' => ['This email address is already taken.'],
                ]);
            }
        }

        return $this->userRepository->update($id, $userData);
    }

    /**
     * Delete a user.
     *
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
    public function deleteUser(int $id): bool
    {
        // Check if user exists
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw ValidationException::withMessages([
                'id' => ['User not found.'],
            ]);
        }

        return $this->userRepository->delete($id);
    }

    /**
     * Get users by role.
     *
     * @param string $role
     * @return Collection
     */
    public function getUsersByRole(string $role): Collection
    {
        return $this->userRepository->getByRole($role);
    }

    /**
     * Change user password.
     *
     * @param int $id
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws ValidationException
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw ValidationException::withMessages([
                'id' => ['User not found.'],
            ]);
        }

        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided current password is incorrect.'],
            ]);
        }

        // Update password
        return (bool) $this->userRepository->update($id, [
            'password' => $newPassword,
        ]);
    }
}
