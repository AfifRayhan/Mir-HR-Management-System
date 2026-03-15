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
        ];

        foreach ($personnelChildren as $child) {
            $child['parent_id'] = $menuModels['personnel']->id;
            $menuModels[$child['slug']] = MenuItem::firstOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define child menu items under Leave
        $leaveChildren = [
            ['name' => 'Leave Types',    'slug' => 'leave-types',        'icon' => 'bi-tag',              'route_name' => 'settings.leave-types.index',   'sort_order' => 1],
            ['name' => 'Leave Accounts', 'slug' => 'leave-accounts',     'icon' => 'bi-wallet2',          'route_name' => 'personnel.leave-balances.index', 'sort_order' => 2],
            ['name' => 'Applications',   'slug' => 'leave-applications', 'icon' => 'bi-file-earmark-text', 'route_name' => 'personnel.leave-applications.index', 'sort_order' => 3],
        ];

        foreach ($leaveChildren as $child) {
            $child['parent_id'] = $menuModels['leave']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define additional leave items (Team Lead / Employee) under the same Leave parent
        $additionalLeaveChildren = [
            ['name' => 'My Applications', 'slug' => 'employee-leave-self',    'icon' => 'bi-journal-text',     'route_name' => 'employee.leave.index',       'sort_order' => 4],
            ['name' => 'Leave Request',   'slug' => 'team-lead-leave-request', 'icon' => 'bi-journal-plus',     'route_name' => 'team-lead.leave.index',      'sort_order' => 5],
            ['name' => 'Team Applications', 'slug' => 'team-lead-leave-apps', 'icon' => 'bi-people-fill',       'route_name' => 'team-lead.leave-applications.index', 'sort_order' => 6],
        ];

        foreach ($additionalLeaveChildren as $child) {
            $child['parent_id'] = $menuModels['leave']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Remove old Office Times from Personnel if it exists
        MenuItem::where('slug', 'personnel-office-times')->delete();

        // Define child menu items under Settings
        $settingsChildren = [
            ['name' => 'Office Type',    'slug' => 'settings-office-types',    'icon' => 'bi-grid-3x3-gap', 'route_name' => 'settings.office-types.index', 'sort_order' => 1],
            ['name' => 'Offices',        'slug' => 'settings-offices',         'icon' => 'bi-building',     'route_name' => 'settings.offices.index',      'sort_order' => 2],
            ['name' => 'Office Times',   'slug' => 'settings-office-times',    'icon' => 'bi-clock',        'route_name' => 'settings.office-times.index', 'sort_order' => 3],
            ['name' => 'Weekly Holiday', 'slug' => 'settings-holidays-weekly', 'icon' => 'bi-calendar-week', 'route_name' => 'settings.holidays.weekly.index', 'sort_order' => 4],
            ['name' => 'Other Holiday',  'slug' => 'settings-holidays-others', 'icon' => 'bi-calendar-plus', 'route_name' => 'settings.holidays.others.index', 'sort_order' => 5],
            ['name' => 'Devices',        'slug' => 'settings-devices',         'icon' => 'bi-cpu',           'route_name' => 'settings.devices.index',      'sort_order' => 6],
            ['name' => 'Notices & Events', 'slug' => 'settings-notices',       'icon' => 'bi-megaphone',     'route_name' => 'settings.notices.index',      'sort_order' => 7],
        ];

        foreach ($settingsChildren as $child) {
            $child['parent_id'] = $menuModels['settings']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define child menu items under Attendances
        $attendanceChildren = [
            ['name' => 'Daily Attendance', 'slug' => 'attendance-daily', 'icon' => 'bi-calendar-check', 'route_name' => 'personnel.attendances.index', 'sort_order' => 1],
            ['name' => 'Adjustment',       'slug' => 'attendance-adjust', 'icon' => 'bi-pencil-square',  'route_name' => 'personnel.attendances.adjust', 'sort_order' => 2],
        ];

        foreach ($attendanceChildren as $child) {
            $child['parent_id'] = $menuModels['attendances']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define child menu items under Employee Dashboard
        $employeeDashboardChildren = [
            ['name' => 'Dashboard',       'slug' => 'employee-dashboard-main', 'icon' => 'bi-speedometer2',   'route_name' => 'employee-dashboard', 'sort_order' => 1],
            ['name' => 'My Profile',      'slug' => 'employee-profile',       'icon' => 'bi-person-vcard',   'route_name' => 'employee-profile',   'sort_order' => 2],
            ['name' => 'Attendances',     'slug' => 'employee-attendance',    'icon' => 'bi-clock',          'route_name' => 'employee.attendance.index', 'sort_order' => 3],
            ['name' => 'Leave Requests',  'slug' => 'employee-leave',         'icon' => 'bi-calendar2-minus', 'route_name' => 'employee.leave.index', 'sort_order' => 4],
            ['name' => 'Payslips',        'slug' => 'employee-payslips',      'icon' => 'bi-envelope-paper', 'route_name' => null,                 'sort_order' => 5],
            ['name' => 'Notifications',   'slug' => 'employee-notifications', 'icon' => 'bi-bell',           'route_name' => null,                 'sort_order' => 6],
        ];

        foreach ($employeeDashboardChildren as $child) {
            $child['parent_id'] = $menuModels['employee-dashboard']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
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

        // HR Admin gets HR Dashboard + all other items EXCEPT Employee Dashboard and specific lead/employee leave items
        $adminMenuIds = MenuItem::whereNotIn('slug', [
            'employee-dashboard',
            'employee-leave-self',
            'team-lead-leave-request',
            'team-lead-leave-apps'
        ])->pluck('id')->all();
        $roleModels['hr_admin']->menuItems()->sync($adminMenuIds);

        // Employee gets Employee Dashboard + all its children
        $employeeMenuIds = MenuItem::where('slug', 'employee-dashboard')
            ->orWhere('parent_id', $menuModels['employee-dashboard']->id)
            ->pluck('id')->all();
        $roleModels['employee']->menuItems()->sync($employeeMenuIds);

        // Cleanup old team-lead-leave parent if it exists
        MenuItem::where('slug', 'team-lead-leave')->delete();

        $teamLeadMenuIds = array_merge(
            $employeeMenuIds, 
            [
                $menuModels['personnel']->id,
                $menuModels['attendances']->id,
                $menuModels['leave']->id,
                $menuModels['team-lead-leave-request']->id,
                $menuModels['team-lead-leave-apps']->id,
            ]
        );

        $roleModels['team_lead']->menuItems()->sync($teamLeadMenuIds);
    }
}
