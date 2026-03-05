<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Section;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\OfficeTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EmployeeSeeder extends Seeder
{
    /**
     * Seed employees for core seeded users.
     */
    public function run(): void
    {
        // Fetch available IDs
        $departments = Department::all();
        $designations = Designation::all();
        $grades = Grade::all();
        $officeTimes = OfficeTime::all();

        // Team Lead - Nadia Khan (HR Admin)
        if ($user = User::where('email', 'teamlead@example.com')->first()) {
            $dept = $departments->where('short_name', 'HR')->first();
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => 'EMP001',
                    'first_name' => 'Nadia',
                    'last_name' => 'Khan',
                    'phone' => '+8801711000002',
                    'address' => 'Gulshan, Dhaka',
                    'date_of_birth' => '1990-07-22',
                    'joining_date' => '2021-05-10',
                    'department_id' => $dept->id ?? null,
                    'section_id' => Section::where('department_id', $dept->id ?? 0)->first()->id ?? null,
                    'designation_id' => $designations->where('short_name', 'HRM')->first()->id ?? null,
                    'grade_id' => $grades->where('name', 'Grade A')->first()->id ?? null,
                    'office_time_id' => $officeTimes->first()->id ?? null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        // Additional Team Lead - David Chen (Team Lead)
        if ($user = User::where('email', 'david.chen@example.com')->first()) {
            $dept = $departments->where('short_name', 'IT')->first();
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => 'EMP002',
                    'first_name' => 'David',
                    'last_name' => 'Chen',
                    'phone' => '+8801711000005',
                    'address' => 'Dhanmondi, Dhaka',
                    'date_of_birth' => '1987-04-08',
                    'joining_date' => '2021-09-20',
                    'department_id' => $dept->id ?? null,
                    'section_id' => Section::where('department_id', $dept->id ?? 0)->where('name', 'Software Development')->first()->id ?? null,
                    'designation_id' => $designations->where('short_name', 'PM')->first()->id ?? null,
                    'grade_id' => $grades->where('name', 'Grade B')->first()->id ?? null,
                    'office_time_id' => $officeTimes->first()->id ?? null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        $manager = Employee::where('employee_code', 'EMP002')->first();

        // Employee User - Rakib Islam (Employee)
        if ($user = User::where('email', 'employee@example.com')->first()) {
            $dept = $departments->where('short_name', 'IT')->first();
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => 'EMP003',
                    'first_name' => 'Rakib',
                    'last_name' => 'Islam',
                    'phone' => '+8801711000003',
                    'address' => 'Uttara, Dhaka',
                    'date_of_birth' => '1995-11-05',
                    'joining_date' => '2022-02-15',
                    'department_id' => $dept->id ?? null,
                    'section_id' => Section::where('department_id', $dept->id ?? 0)->where('name', 'Software Development')->first()->id ?? null,
                    'designation_id' => $designations->where('short_name', 'SE')->first()->id ?? null,
                    'grade_id' => $grades->where('name', 'Grade C')->first()->id ?? null,
                    'office_time_id' => $officeTimes->first()->id ?? null,
                    'reporting_manager_id' => $manager->id ?? null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        // Other Employees
        if ($user = User::where('email', 'amir.khan@example.com')->first()) {
            $dept = $departments->where('short_name', 'IT')->first();
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => 'EMP004',
                    'first_name' => 'Amir',
                    'last_name' => 'Khan',
                    'phone' => '+8801711000006',
                    'address' => 'Mirpur, Dhaka',
                    'date_of_birth' => '1992-01-19',
                    'joining_date' => '2022-06-10',
                    'department_id' => $dept->id ?? null,
                    'section_id' => Section::where('department_id', $dept->id ?? 0)->where('name', 'Quality Assurance')->first()->id ?? null,
                    'designation_id' => $designations->where('short_name', 'Sr. SE')->first()->id ?? null,
                    'grade_id' => $grades->where('name', 'Grade B')->first()->id ?? null,
                    'office_time_id' => $officeTimes->first()->id ?? null,
                    'reporting_manager_id' => $manager->id ?? null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        if ($user = User::where('email', 'linda.okafor@example.com')->first()) {
            $dept = $departments->where('short_name', 'FIN')->first();
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => 'EMP005',
                    'first_name' => 'Linda',
                    'last_name' => 'Okafor',
                    'phone' => '+8801711000007',
                    'address' => 'Baridhara, Dhaka',
                    'date_of_birth' => '1993-12-02',
                    'joining_date' => '2023-01-25',
                    'department_id' => $dept->id ?? null,
                    'section_id' => Section::where('department_id', $dept->id ?? 0)->where('name', 'Payroll')->first()->id ?? null,
                    'designation_id' => $designations->where('short_name', 'FO')->first()->id ?? null,
                    'grade_id' => $grades->where('name', 'Grade C')->first()->id ?? null,
                    'office_time_id' => $officeTimes->last()->id ?? null,
                    'reporting_manager_id' => null,
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        if ($user = User::where('email', 'marco.rossi@example.com')->first()) {
            $dept = $departments->where('short_name', 'OPS')->first();
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => 'EMP006',
                    'first_name' => 'Marco',
                    'last_name' => 'Rossi',
                    'phone' => '+8801711000008',
                    'address' => 'Tejgaon, Dhaka',
                    'date_of_birth' => '1991-05-27',
                    'joining_date' => '2020-11-30',
                    'department_id' => $dept->id ?? null,
                    'section_id' => null,
                    'designation_id' => null,
                    'grade_id' => $grades->where('name', 'Grade D')->first()->id ?? null,
                    'office_time_id' => $officeTimes->last()->id ?? null,
                    'reporting_manager_id' => null,
                    'status' => $user->status == 'active' ? 'active' : 'resigned',
                ]
            );
        }
    }
}
