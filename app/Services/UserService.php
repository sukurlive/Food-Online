<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsers()
    {
        return $this->userRepository->getAll();
    }

    public function getUserById($id)
    {
        return $this->userRepository->findById($id);
    }

    public function getUserByEmail($email)
    {
        return $this->userRepository->findByEmail($email);
    }

    public function getUserByPhone($phone)
    {
        return $this->userRepository->findByPhone($phone);
    }

    public function createUser($data)
    {
        return $this->userRepository->create($data);
    }

    public function updateUser($id, $data)
    {
        return $this->userRepository->update($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->userRepository->delete($id);
    }

    public function getActiveUsers()
    {
        return $this->userRepository->getActiveUsers();
    }

    public function getInactiveUsers()
    {
        return $this->userRepository->getInactiveUsers();
    }

    public function activateUser($id)
    {
        return $this->userRepository->activateUser($id);
    }

    public function deactivateUser($id)
    {
        return $this->userRepository->deactivateUser($id);
    }

    public function countUsersByStatus($isActive = null)
    {
        return $this->userRepository->countByStatus($isActive);
    }

    public function assignRole($userId, $roleName)
    {
        return $this->userRepository->assignRole($userId, $roleName);
    }

    public function removeRole($userId, $roleName)
    {
        return $this->userRepository->removeRole($userId, $roleName);
    }

    public function syncRoles($userId, $roles)
    {
        return $this->userRepository->syncRoles($userId, $roles);
    }

    public function givePermissionTo($userId, $permissionName)
    {
        return $this->userRepository->givePermissionTo($userId, $permissionName);
    }

    public function revokePermissionTo($userId, $permissionName)
    {
        return $this->userRepository->revokePermissionTo($userId, $permissionName);
    }

    public function syncPermissions($userId, $permissions)
    {
        return $this->userRepository->syncPermissions($userId, $permissions);
    }

    public function getUsersByRole($roleName)
    {
        return $this->userRepository->getUsersByRole($roleName);
    }

    public function searchUsers($keyword)
    {
        return $this->userRepository->search($keyword);
    }

    public function registerUser($userData)
    {
        $defaultData = ['is_active' => true,];
        $data        = array_merge($defaultData, $userData);

        $user = $this->userRepository->create($data);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return $user;
    }

    public function loginUser($email, $password)
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->password)) {
            return null;
        }

        if (!$user->is_active) {
            throw new \Exception('Your account is inactive. Please contact administrator.', 403);
        }

        return $user;
    }

    public function logoutUser($user)
    {
        return $this->userRepository->deleteCurrentToken($user);
    }
}
