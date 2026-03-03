<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Seed employees for core seeded users.
     */
    public function run(): void
    {
        // Team Lead
        if ($user = User::where('email', 'teamlead@example.com')->first()) {
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'Nadia',
                    'last_name' => 'Khan',
                    'gender' => 'female',
                    'phone' => '+8801711000002',
                    'address' => 'Gulshan, Dhaka',
                    'date_of_birth' => '1990-07-22',
                    'joining_date' => '2021-05-10',
                    'department_id' => null,
                    'section_id' => null,
                    'designation_id' => null,
                    'grade_id' => null,
                    'office_time_id' => null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        // Additional Team Lead
        if ($user = User::where('email', 'david.chen@example.com')->first()) {
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'David',
                    'last_name' => 'Chen',
                    'gender' => 'male',
                    'phone' => '+8801711000005',
                    'address' => 'Dhanmondi, Dhaka',
                    'date_of_birth' => '1987-04-08',
                    'joining_date' => '2021-09-20',
                    'department_id' => null,
                    'section_id' => null,
                    'designation_id' => null,
                    'grade_id' => null,
                    'office_time_id' => null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        // Employee User
        if ($user = User::where('email', 'employee@example.com')->first()) {
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'Rakib',
                    'last_name' => 'Islam',
                    'gender' => 'male',
                    'phone' => '+8801711000003',
                    'address' => 'Uttara, Dhaka',
                    'date_of_birth' => '1995-11-05',
                    'joining_date' => '2022-02-15',
                    'department_id' => null,
                    'section_id' => null,
                    'designation_id' => null,
                    'grade_id' => null,
                    'office_time_id' => null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        // Other Employees
        if ($user = User::where('email', 'amir.khan@example.com')->first()) {
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'Amir',
                    'last_name' => 'Khan',
                    'gender' => 'male',
                    'phone' => '+8801711000006',
                    'address' => 'Mirpur, Dhaka',
                    'date_of_birth' => '1992-01-19',
                    'joining_date' => '2022-06-10',
                    'department_id' => null,
                    'section_id' => null,
                    'designation_id' => null,
                    'grade_id' => null,
                    'office_time_id' => null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        if ($user = User::where('email', 'linda.okafor@example.com')->first()) {
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'Linda',
                    'last_name' => 'Okafor',
                    'gender' => 'female',
                    'phone' => '+8801711000007',
                    'address' => 'Baridhara, Dhaka',
                    'date_of_birth' => '1993-12-02',
                    'joining_date' => '2023-01-25',
                    'department_id' => null,
                    'section_id' => null,
                    'designation_id' => null,
                    'grade_id' => null,
                    'office_time_id' => null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        if ($user = User::where('email', 'marco.rossi@example.com')->first()) {
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'Marco',
                    'last_name' => 'Rossi',
                    'gender' => 'male',
                    'phone' => '+8801711000008',
                    'address' => 'Tejgaon, Dhaka',
                    'date_of_birth' => '1991-05-27',
                    'joining_date' => '2020-11-30',
                    'department_id' => null,
                    'section_id' => null,
                    'designation_id' => null,
                    'grade_id' => null,
                    'office_time_id' => null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'inactive',
                ]
            );
        }
    }
}
