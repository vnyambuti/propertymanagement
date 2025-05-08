<?php

namespace App\Repositories\Interfaces;

use App\Domain\Property\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Get all users with pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get all users.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Find user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     *
     * @param array $userData
     * @return User
     */
    public function create(array $userData): User;

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $userData
     * @return User|null
     */
    public function update(int $id, array $userData): ?User;

    /**
     * Delete a user.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Get users by role.
     *
     * @param string $role
     * @return Collection
     */
    public function getByRole(string $role): Collection;
}

interface UserServiceInterface
{
    /**
     * Get users with pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get all users.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllUsers(array $filters = []): Collection;

    /**
     * Get user by ID.
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User;

    /**
     * Create a new user.
     *
     * @param array $userData
     * @return User
     */
    public function createUser(array $userData): User;

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $userData
     * @return User|null
     */
    public function updateUser(int $id, array $userData): ?User;

    /**
     * Delete a user.
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool;

    /**
     * Get users by role.
     *
     * @param string $role
     * @return Collection
     */
    public function getUsersByRole(string $role): Collection;

    /**
     * Change user password.
     *
     * @param int $id
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $id, string $currentPassword, string $newPassword): bool;
}
