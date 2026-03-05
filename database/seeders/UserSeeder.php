<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed example users for each core role.
     */
    public function run(): void
    {
        $employeeRole = Role::where('name', 'Employee')->first();
        $teamLeadRole = Role::where('name', 'Team Lead')->first();
        $hrAdminRole = Role::where('name', 'HR Admin')->first();

        if ($hrAdminRole) {
            User::firstOrCreate(
                ['email' => 'hradmin@example.com'],
                [
                    'name' => null,
                    'password' => Hash::make('password'),
                    'employee_id' => null,
                    'role_id' => $hrAdminRole->id,
                    'status' => 'active',
                ]
            );
        }

        if ($teamLeadRole) {
            User::firstOrCreate(
                ['email' => 'teamlead@example.com'],
                [
                    'name' => 'Nadia Khan',
                    'password' => Hash::make('password'),
                    'employee_id' => 'TL-0001',
                    'role_id' => $teamLeadRole->id,
                    'status' => 'active',
                ]
            );

            User::firstOrCreate(
                ['email' => 'david.chen@example.com'],
                [
                    'name' => 'David Chen',
                    'password' => Hash::make('password'),
                    'employee_id' => 'TL-0002',
                    'role_id' => $teamLeadRole->id,
                    'status' => 'active',
                ]
            );
        }

        if ($employeeRole) {
            User::firstOrCreate(
                ['email' => 'employee@example.com'],
                [
                    'name' => 'Rakib Islam',
                    'password' => Hash::make('password'),
                    'employee_id' => 'EMP-0001',
                    'role_id' => $employeeRole->id,
                    'status' => 'active',
                ]
            );

            User::firstOrCreate(
                ['email' => 'amir.khan@example.com'],
                [
                    'name' => 'Amir Khan',
                    'password' => Hash::make('password'),
                    'employee_id' => 'EMP-0002',
                    'role_id' => $employeeRole->id,
                    'status' => 'active',
                ]
            );

            User::firstOrCreate(
                ['email' => 'linda.okafor@example.com'],
                [
                    'name' => 'Linda Okafor',
                    'password' => Hash::make('password'),
                    'employee_id' => 'EMP-0003',
                    'role_id' => $employeeRole->id,
                    'status' => 'active',
                ]
            );

            User::firstOrCreate(
                ['email' => 'marco.rossi@example.com'],
                [
                    'name' => 'Marco Rossi',
                    'password' => Hash::make('password'),
                    'employee_id' => 'EMP-0004',
                    'role_id' => $employeeRole->id,
                    'status' => 'inactive',
                ]
            );
        }
    }
}
