<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache terlebih dahulu
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================== PERMISSIONS ====================
        $permissions = [
            // Customer permissions
            'view customers',
            'create customers', 
            'edit customers',
            'delete customers',
            
            // Order permissions
            'view orders',
            'create orders',
            'edit orders',
            'delete orders',
            
            // Report permissions
            'view reports',
            
            // User permissions
            'view users',
            'create users',
            'edit users',
            'delete users',
        ];

        // Create permissions dengan guard yang benar
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api'
            ]);
        }

        // ==================== ROLES ====================
        // Admin Role
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api'
        ]);
        
        // User Role
        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'api'
        ]);

        // ==================== ASSIGN PERMISSIONS TO ROLES ====================
        // Admin gets all permissions
        $adminRole->syncPermissions(Permission::all());

        // User gets limited permissions
        $userPermissions = [
            'view customers',
            'create orders',
            'view orders',
            'edit orders'
        ];
        
        $userRole->syncPermissions($userPermissions);

        // ==================== CREATE USERS ====================
        // Admin
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'phone' => '081234560000',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );
        
        // Assign role ke admin
        $adminUser->syncRoles(['admin']);
        $adminUser->syncPermissions(Permission::all()->pluck('name')->toArray());

        // Regular User
        $regularUser = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User',
                'phone' => '081234561111',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]
        );
        
        // Assign role ke user
        $regularUser->syncRoles(['user']);
        $regularUser->syncPermissions($userPermissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}