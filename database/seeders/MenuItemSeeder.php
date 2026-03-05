<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Role;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Seed the menu_items table and assign them to roles.
     */
    public function run(): void
    {
        // Define top-level menu items
        $items = [
            ['name' => 'HR Dashboard', 'slug' => 'hr-dashboard',       'icon' => 'bi-speedometer2',   'route_name' => 'hr-dashboard',       'sort_order' => 1],
            ['name' => 'Employee Dashboard', 'slug' => 'employee-dashboard', 'icon' => 'bi-speedometer2',   'route_name' => 'employee-dashboard', 'sort_order' => 1],
            ['name' => 'Security',    'slug' => 'security',           'icon' => 'bi-shield-lock',    'route_name' => null,                 'sort_order' => 2],
            ['name' => 'Settings',    'slug' => 'settings',           'icon' => 'bi-gear',           'route_name' => null,                 'sort_order' => 3],
            ['name' => 'Leave',       'slug' => 'leave',              'icon' => 'bi-journal-check',  'route_name' => null,                 'sort_order' => 4],
            ['name' => 'Personnel',   'slug' => 'personnel',          'icon' => 'bi-people',         'route_name' => null,                 'sort_order' => 5],
            ['name' => 'Attendances', 'slug' => 'attendances',        'icon' => 'bi-clock-history',  'route_name' => null,                 'sort_order' => 6],
            ['name' => 'Payroll',     'slug' => 'payroll',            'icon' => 'bi-cash-stack',     'route_name' => null,                 'sort_order' => 7],
        ];

        $menuModels = [];
        foreach ($items as $data) {
            $menuModels[$data['slug']] = MenuItem::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        // Define child menu items under Security
        $securityChildren = [
            ['name' => 'Users',            'slug' => 'security-users',            'icon' => 'bi-person',       'route_name' => 'security.users.index',            'sort_order' => 1],
            ['name' => 'Roles',            'slug' => 'security-roles',            'icon' => 'bi-person-badge', 'route_name' => 'security.roles.index',            'sort_order' => 2],
            ['name' => 'Role Permissions', 'slug' => 'security-role-permissions', 'icon' => 'bi-key',          'route_name' => 'security.role-permissions.index', 'sort_order' => 3],
        ];

        foreach ($securityChildren as $child) {
            $child['parent_id'] = $menuModels['security']->id;
            $menuModels[$child['slug']] = MenuItem::firstOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define child menu items under Personnel
        $personnelChildren = [
            ['name' => 'Employees',    'slug' => 'personnel-employees',    'icon' => 'bi-people',       'route_name' => 'personnel.employees.index',    'sort_order' => 1],
            ['name' => 'Departments',  'slug' => 'personnel-departments',  'icon' => 'bi-building',     'route_name' => 'personnel.departments.index',  'sort_order' => 2],
            ['name' => 'Sections',     'slug' => 'personnel-sections',     'icon' => 'bi-diagram-2',    'route_name' => 'personnel.sections.index',     'sort_order' => 3],
            ['name' => 'Designations', 'slug' => 'personnel-designations', 'icon' => 'bi-award',        'route_name' => 'personnel.designations.index', 'sort_order' => 4],
            ['name' => 'Grades',       'slug' => 'personnel-grades',       'icon' => 'bi-layers',       'route_name' => 'personnel.grades.index',       'sort_order' => 5],
            ['name' => 'Office Times', 'slug' => 'personnel-office-times', 'icon' => 'bi-clock',        'route_name' => 'personnel.office-times.index', 'sort_order' => 6],
        ];

        foreach ($personnelChildren as $child) {
            $child['parent_id'] = $menuModels['personnel']->id;
            $menuModels[$child['slug']] = MenuItem::firstOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define core roles
        $roles = [
            'employee' => [
                'name'        => 'Employee',
                'description' => 'Standard employee with self-service access.',
            ],
            'team_lead' => [
                'name'        => 'Team Lead',
                'description' => 'Team lead with access to team-level data.',
            ],
            'hr_admin' => [
                'name'        => 'HR Admin',
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

        // HR Admin gets HR Dashboard + all other items EXCEPT Employee Dashboard
        $adminMenuIds = MenuItem::where('slug', '!=', 'employee-dashboard')->pluck('id')->all();
        $roleModels['hr_admin']->menuItems()->sync($adminMenuIds);

        // Employee gets Employee Dashboard only
        $roleModels['employee']->menuItems()->sync([
            $menuModels['employee-dashboard']->id,
        ]);

        // Team Lead gets Employee Dashboard, Personnel, Attendances
        $roleModels['team_lead']->menuItems()->sync([
            $menuModels['employee-dashboard']->id,
            $menuModels['personnel']->id,
            $menuModels['attendances']->id,
        ]);
    }
}
