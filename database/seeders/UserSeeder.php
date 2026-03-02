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
                    'name' => 'HR Admin',
                    'password' => Hash::make('password'),
                    'employee_id' => 'HR-0001',
                    'role_id' => $hrAdminRole->id,
                    'status' => 'active',
                ]
            );
        }

        if ($teamLeadRole) {
            User::firstOrCreate(
                ['email' => 'teamlead@example.com'],
                [
                    'name' => 'Team Lead',
                    'password' => Hash::make('password'),
                    'employee_id' => 'TL-0001',
                    'role_id' => $teamLeadRole->id,
                    'status' => 'active',
                ]
            );
        }

        if ($employeeRole) {
            User::firstOrCreate(
                ['email' => 'employee@example.com'],
                [
                    'name' => 'Employee User',
                    'password' => Hash::make('password'),
                    'employee_id' => 'EMP-0001',
                    'role_id' => $employeeRole->id,
                    'status' => 'active',
                ]
            );
        }
    }
}

