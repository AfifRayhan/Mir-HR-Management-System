<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed the roles and permissions tables.
     */
    public function run(): void
    {
        // Define core roles
        $roles = [
            'employee' => [
                'name' => 'Employee',
                'description' => 'Standard employee with self-service access.',
            ],
            'team_lead' => [
                'name' => 'Team Lead',
                'description' => 'Team lead with access to team-level data.',
            ],
            'hr_admin' => [
                'name' => 'HR Admin',
                'description' => 'Administrator with full HR management access.',
            ],
        ];

        $roleModels = [];
        foreach ($roles as $key => $data) {
            $roleModels[$key] = Role::firstOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );
        }

        // Define a simple permission set by module
        $permissions = [
            ['name' => 'view_self_profile', 'module' => 'employees'],
            ['name' => 'edit_self_profile', 'module' => 'employees'],
            ['name' => 'view_team_employees', 'module' => 'employees'],
            ['name' => 'manage_team_attendance', 'module' => 'attendance'],
            ['name' => 'view_all_employees', 'module' => 'employees'],
            ['name' => 'manage_roles', 'module' => 'security'],
            ['name' => 'manage_permissions', 'module' => 'security'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['name']] = Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['module' => $perm['module']]
            );
        }

        // Attach permissions to roles
        $roleModels['employee']->permissions()->syncWithoutDetaching([
            $permissionModels['view_self_profile']->id,
            $permissionModels['edit_self_profile']->id,
        ]);

        $roleModels['team_lead']->permissions()->syncWithoutDetaching([
            $permissionModels['view_self_profile']->id,
            $permissionModels['edit_self_profile']->id,
            $permissionModels['view_team_employees']->id,
            $permissionModels['manage_team_attendance']->id,
        ]);

        $roleModels['hr_admin']->permissions()->syncWithoutDetaching(
            collect($permissionModels)->pluck('id')->all()
        );
    }
}

