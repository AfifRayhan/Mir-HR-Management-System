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
            ['name' => 'Dashboard',       'slug' => 'employee-dashboard-main', 'icon' => 'bi-speedometer2',   'route_name' => 'employee-dashboard', 'sort_order' => 2],
            ['name' => 'My Profile',      'slug' => 'employee-profile',       'icon' => 'bi-person-vcard',   'route_name' => 'employee-profile',   'sort_order' => 3],
            ['name' => 'My Attendances',     'slug' => 'employee-attendance',    'icon' => 'bi-clock',          'route_name' => 'employee.attendance.index', 'sort_order' => 4],
            ['name' => 'Security',    'slug' => 'security',           'icon' => 'bi-shield-lock',    'route_name' => null,                 'sort_order' => 5],
            ['name' => 'Settings',    'slug' => 'settings',           'icon' => 'bi-gear',           'route_name' => null,                 'sort_order' => 6],
            ['name' => 'Leave',       'slug' => 'leave',              'icon' => 'bi-journal-check',  'route_name' => null,                 'sort_order' => 7],
            ['name' => 'Team Leave',  'slug' => 'team-leave',         'icon' => 'bi-people-fill',    'route_name' => null,                 'sort_order' => 9],
            ['name' => 'Personnel',   'slug' => 'personnel',          'icon' => 'bi-people',         'route_name' => null,                 'sort_order' => 10],
            ['name' => 'Supervisor Remarks', 'slug' => 'team-lead-remarks', 'icon' => 'bi-chat-left-text', 'route_name' => 'team-lead.remarks.index', 'sort_order' => 11],
            ['name' => 'Attendance Approvals', 'slug' => 'team-lead-attendance-approvals', 'icon' => 'bi-check2-all', 'route_name' => 'team-lead.attendances.approvals', 'sort_order' => 12],
            ['name' => 'Overtime',    'slug' => 'overtime',           'icon' => 'bi-clock-history',  'route_name' => 'overtimes.index', 'sort_order' => 13],
            ['name' => 'Roster',      'slug' => 'roster',             'icon' => 'bi-calendar3',      'route_name' => 'roster.index',       'sort_order' => 14],
            ['name' => 'Driver Roster', 'slug' => 'driver-roster',    'icon' => 'bi-car-front',      'route_name' => 'driver-roster.index', 'sort_order' => 15],
            ['name' => 'Reports',     'slug' => 'reports',            'icon' => 'bi-file-earmark-pdf', 'route_name' => null,                'sort_order' => 16],
        ];

        $menuModels = [];
        foreach ($items as $data) {
            $menuModels[$data['slug']] = MenuItem::updateOrCreate(
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
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
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
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define child menu items under Leave (HR Admin version)
        $leaveChildren = [
            ['name' => 'Leave Types',    'slug' => 'leave-types',        'icon' => 'bi-tag',              'route_name' => 'settings.leave-types.index',   'sort_order' => 1],
            ['name' => 'Leave Accounts', 'slug' => 'leave-accounts',     'icon' => 'bi-wallet2',          'route_name' => 'personnel.leave-balances.index', 'sort_order' => 2],
            ['name' => 'Applications',   'slug' => 'leave-applications', 'icon' => 'bi-file-earmark-text', 'route_name' => 'personnel.leave-applications.index', 'sort_order' => 3],
            ['name' => 'History',        'slug' => 'leave-history',      'icon' => 'bi-clock-history',    'route_name' => 'personnel.leave-applications.history', 'sort_order' => 4],
            ['name' => 'Manual Leave',   'slug' => 'leave-manual',       'icon' => 'bi-pencil-square',    'route_name' => 'personnel.leave.manual',             'sort_order' => 5],
        ];

        foreach ($leaveChildren as $child) {
            $child['parent_id'] = $menuModels['leave']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Removed Requests child items to make Leave Request a direct link

        // Define child menu items under Team Leave
        $teamLeaveChildren = [
            ['name' => 'Applications', 'slug' => 'team-lead-leave-apps', 'icon' => 'bi-file-earmark-text', 'route_name' => 'team-lead.leave-applications.index', 'sort_order' => 1],
            ['name' => 'History',      'slug' => 'team-lead-leave-history', 'icon' => 'bi-clock-history', 'route_name' => 'team-lead.leave-applications.history', 'sort_order' => 2],
        ];

        foreach ($teamLeaveChildren as $child) {
            $child['parent_id'] = $menuModels['team-leave']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Remove old Office Times from Personnel if it exists
        MenuItem::where('slug', 'personnel-office-times')->delete();
        MenuItem::where('slug', 'attendances')->delete(); // Remove old ambiguous slug

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

        // Define child menu items under Attendances (HR Admin version)
        $attendanceChildren = [
            ['name' => 'Daily Attendance', 'slug' => 'attendance-daily', 'icon' => 'bi-calendar-check', 'route_name' => 'personnel.attendances.index', 'sort_order' => 1],
            ['name' => 'Adjustment',       'slug' => 'attendance-adjust', 'icon' => 'bi-pencil-square',  'route_name' => 'personnel.attendances.adjust', 'sort_order' => 2],
            ['name' => 'Approvals',        'slug' => 'attendance-approvals', 'icon' => 'bi-check2-all',  'route_name' => 'personnel.attendances.approvals', 'sort_order' => 3],
        ];

        // Create a parent for Admin Attendances
        $adminAttendanceParent = MenuItem::updateOrCreate(
            ['slug' => 'admin-attendances'],
            ['name' => 'Attendances', 'icon' => 'bi-clock', 'route_name' => null, 'sort_order' => 11.5]
        );

        // Define child menu items under Roster
        $rosterChildren = [
            ['name' => 'Manage Roster', 'slug' => 'roster-index', 'icon' => 'bi-calendar3', 'route_name' => 'roster.index', 'sort_order' => 1],
            ['name' => 'Roster Times',  'slug' => 'roster-times', 'icon' => 'bi-clock',     'route_name' => 'roster.times.index', 'sort_order' => 2],
        ];

        foreach ($rosterChildren as $child) {
            $child['parent_id'] = $menuModels['roster']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define child menu items under Driver Roster
        $driverRosterChildren = [
            ['name' => 'Manage Roster', 'slug' => 'driver-roster-index', 'icon' => 'bi-car-front', 'route_name' => 'driver-roster.index', 'sort_order' => 1],
            ['name' => 'Roster Times',  'slug' => 'driver-roster-times', 'icon' => 'bi-clock',     'route_name' => 'driver-roster.times.index', 'sort_order' => 2],
        ];

        foreach ($driverRosterChildren as $child) {
            $child['parent_id'] = $menuModels['driver-roster']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define child menu items under Overtime
        $overtimeChildren = [
            ['name' => 'Monthly Config', 'slug' => 'overtime-monthly', 'icon' => 'bi-calendar-month', 'route_name' => 'overtimes.index', 'sort_order' => 1],
            ['name' => 'Settings',       'slug' => 'overtime-settings', 'icon' => 'bi-gear',           'route_name' => 'overtimes.settings', 'sort_order' => 2],
        ];

        foreach ($overtimeChildren as $child) {
            $child['parent_id'] = $menuModels['overtime']->id;
            $menuModels[$child['slug']] = MenuItem::updateOrCreate(
                ['slug' => $child['slug']],
                $child
            );
        }

        // Define child menu items under Reports
        $reportsChildren = [
            ['name' => 'Generate Letter', 'slug' => 'reports-generate', 'icon' => 'bi-file-earmark-plus', 'route_name' => 'personnel.reports.generate', 'sort_order' => 1],
            ['name' => 'Letter Template', 'slug' => 'personnel-report-templates', 'icon' => 'bi-file-earmark-richtext', 'route_name' => 'personnel.report-templates.index', 'sort_order' => 2],
            ['name' => 'Employee Export', 'slug' => 'reports-employee-export', 'icon' => 'bi-file-earmark-spreadsheet', 'route_name' => 'personnel.reports.employees.export.preview', 'sort_order' => 3],
            ['name' => 'Attendance Export (Daily)', 'slug' => 'reports-attendance-export', 'icon' => 'bi-calendar-check', 'route_name' => 'personnel.reports.attendances.export.preview', 'sort_order' => 4],
            ['name' => 'Attendance Export (Monthly)', 'slug' => 'reports-attendance-export-monthly', 'icon' => 'bi-calendar-month', 'route_name' => 'personnel.reports.attendances.monthly.export.preview', 'sort_order' => 5],
            ['name' => 'Attendance Export (Yearly)', 'slug' => 'reports-attendance-export-yearly', 'icon' => 'bi-calendar-range', 'route_name' => 'personnel.reports.attendances.yearly.export.preview', 'sort_order' => 6],
            ['name' => 'Employee Log', 'slug' => 'reports-attendance-log', 'icon' => 'bi-person-badge', 'route_name' => 'personnel.reports.attendances.log.preview', 'sort_order' => 7],
            ['name' => 'Leave Balance', 'slug' => 'reports-leave-balance', 'icon' => 'bi-wallet2', 'route_name' => 'personnel.reports.leave-balance.preview', 'sort_order' => 8],
        ];

        foreach ($reportsChildren as $child) {
            $child['parent_id'] = $menuModels['reports']->id;
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

        // HR Admin gets HR Dashboard + all other items EXCEPT the self-service employee items and Team Lead specifics
        $adminMenuIds = MenuItem::whereNotIn('slug', [
            'employee-dashboard-main',
            'employee-profile',
            'employee-attendance',
            'employee-leave-request',
            'team-lead-leave-request',
            'team-leave',
            'team-lead-leave-apps',
            'team-lead-leave-history',
            'team-lead-remarks',
            'team-lead-attendance-approvals',
        ])->pluck('id')->all();
        $roleModels['hr_admin']->menuItems()->sync($adminMenuIds);

        // Employee gets Self-Service items + Overtime + Leave Request (specific)
        $employeeMenuIds = MenuItem::whereIn('slug', [
            'employee-dashboard-main',
            'employee-profile',
            'employee-attendance',
            'overtime', 
            'overtime-monthly',
            'employee-leave-request'
        ])->pluck('id')->all();
        $roleModels['employee']->menuItems()->sync($employeeMenuIds);

        // Team Lead gets Self-Service + Attendance Approvals + Leave Request (specific) + Team Leave + Remarks
        $teamLeadMenuIds = MenuItem::whereIn('slug', [
            'employee-dashboard-main',
            'employee-profile',
            'employee-attendance',
            'team-lead-remarks',
            'team-lead-attendance-approvals',
            'team-lead-leave-request',
            'team-leave',
            'team-lead-leave-apps',
            'team-lead-leave-history',
        ])->pluck('id')->all();

        $roleModels['team_lead']->menuItems()->sync($teamLeadMenuIds);
    }
}
