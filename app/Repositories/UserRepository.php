<?php

namespace App\Repositories;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRepository
{
    public function getAll()
    {
        return User::with('roles')->get();
    }

    public function findById($id)
    {
        return User::with('roles', 'permissions')->findOrFail($id);
    }

    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function findByPhone($phone)
    {
        return User::where('phone', $phone)->first();
    }

    public function create($data)
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user = User::create($data);

        // Assign role
        $roleName = $data['role'] ?? 'user';
        $user->assignRole($roleName);
        
        $this->assignRoleBasedPermissions($user, $roleName);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return $user->load('roles', 'permissions');
    }

    protected function assignRoleBasedPermissions($user, $roleName)
    {
        $permissions = [];
        
        switch ($roleName) {
            case 'admin':
                $permissions = Permission::all()->pluck('name')->toArray();
                break;
                
            case 'user':
                $permissions = [
                    'view customers',
                    'create orders',
                    'view orders',
                    'edit orders'
                ];
                break;
        }
        
        if (!empty($permissions)) {
            $user->givePermissionTo($permissions);
            \Log::info("Direct permissions assigned to user: " . implode(', ', $permissions));
        }
    }

    public function update($id, $request)
    {
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email', 'is_active']));

        if ($request->has('is_active') && $request->is_active === false) {
            $user->tokens()->delete();
        }

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        if ($request->has('permissions')) {
            $user->syncPermissions([$request->permissions]);
        }

        return $user->load('roles', 'permissions');
    }

    public function getActiveUsers()
    {
        return User::where('is_active', true)
            ->with('roles')
            ->get();
    }

    public function getInactiveUsers()
    {
        return User::where('is_active', false)
            ->with('roles')
            ->get();
    }

    public function search($keyword)
    {
        return User::where('name', 'like', "%{$keyword}%")
            ->orWhere('email', 'like', "%{$keyword}%")
            ->orWhere('phone', 'like', "%{$keyword}%")
            ->with('roles')
            ->get();
    }

    public function assignRole($userId, $roleName)
    {
        $user = User::findOrFail($userId);
        $role = Role::where('name', $roleName)->firstOrFail();

        $user->assignRole($role);

        return $user->load('roles');
    }

    public function removeRole($userId, $roleName)
    {
        $user = User::findOrFail($userId);
        $user->removeRole($roleName);

        return $user->load('roles');
    }

    public function syncRoles($userId, $roles)
    {
        $user = User::findOrFail($userId);
        $user->syncRoles($roles);

        return $user->load('roles');
    }

    public function givePermissionTo($userId, $permissionName)
    {
        $user = User::findOrFail($userId);
        $user->givePermissionTo($permissionName);

        return $user->load('permissions');
    }

    public function revokePermissionTo($userId, $permissionName)
    {
        $user = User::findOrFail($userId);
        $user->revokePermissionTo($permissionName);

        return $user->load('permissions');
    }

    public function syncPermissions($userId, array $permissions)
    {
        $user = User::findOrFail($userId);
        $user->syncPermissions($permissions);

        return $user->load('permissions');
    }

    public function activateUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => true]);

        return $user;
    }

    public function deactivateUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return $user;
    }

    public function getUsersByRole($roleName)
    {
        return User::whereHas('roles', function($query) use ($roleName) {
            $query->where('name', $roleName);
        })
        ->with('roles')
        ->get();
    }

    public function changePassword($id, $newPassword)
    {
        $user = User::findOrFail($id);

        $user->update([
            'password' => bcrypt($newPassword)
        ]);

        return $user;
    }

    public function countByStatus($isActive = null)
    {
        $query = User::query();

        if (!is_null($isActive)) {
            $query->where('is_active', $isActive);
        }

        return $query->count();
    }

    public function deleteCurrentToken($user)
    {
        if ($user && $user->currentAccessToken()) {
            return $user->currentAccessToken()->delete();
        }

        return $user->tokens()->delete();
    }

    public function deleteAllTokens($user)
    {
        return $user->tokens()->delete();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->roles()->detach();
        $user->permissions()->detach();

        return $user->delete();
    }
}
