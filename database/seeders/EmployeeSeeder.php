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
        $generalShift = OfficeTime::where('shift_name', 'General Shift')->first();
        $offices = \App\Models\Office::all();
        $officeCount = $offices->count();

        // Core Users matching existing Employee records (Updating with office_id)
        $coreEmployees = [
            ['email' => 'teamlead@example.com', 'code' => 'EMP001', 'first' => 'Nadia', 'last' => 'Khan'],
            ['email' => 'david.chen@example.com', 'code' => 'EMP002', 'first' => 'David', 'last' => 'Chen'],
            ['email' => 'employee@example.com', 'code' => 'EMP003', 'first' => 'Rakib', 'last' => 'Islam'],
            ['email' => 'amir.khan@example.com', 'code' => 'EMP004', 'first' => 'Amir', 'last' => 'Khan'],
            ['email' => 'linda.okafor@example.com', 'code' => 'EMP005', 'first' => 'Linda', 'last' => 'Okafor'],
            ['email' => 'marco.rossi@example.com', 'code' => 'EMP006', 'first' => 'Marco', 'last' => 'Rossi'],
        ];

        foreach ($coreEmployees as $index => $data) {
            if ($user = User::where('email', $data['email'])->first()) {
                $office = $offices[$index % $officeCount];

                Employee::updateOrCreate(
                    ['employee_code' => $data['code']],
                    [
                        'user_id' => $user->id,
                        'first_name' => $data['first'],
                        'last_name' => $data['last'],
                        'joining_date' => '2021-05-10',
                        'department_id' => $departments->random()->id ?? null,
                        'designation_id' => $designations->random()->id ?? null,
                        'grade_id' => $grades->random()->id ?? null,
                        'office_id' => $office->id,
                        'office_time_id' => $generalShift->id ?? $officeTimes->random()->id,
                        'status' => 'active',
                    ]
                );
            }
        }

        // Generate 30 more random employees
        $manager = Employee::where('employee_code', 'EMP002')->first();

        for ($i = 7; $i <= 37; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $office = $offices[$i % $officeCount];

            Employee::create([
                'employee_code' => 'EMP' . str_pad($i, 003, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'date_of_birth' => fake()->date('Y-m-d', '-20 years'),
                'joining_date' => fake()->date('Y-m-d', '-2 years'),
                'department_id' => $departments->random()->id ?? null,
                'section_id' => null,
                'designation_id' => $designations->random()->id ?? null,
                'grade_id' => $grades->random()->id ?? null,
                'office_id' => $office->id,
                'office_time_id' => $officeTimes->random()->id ?? null,
                'reporting_manager_id' => $manager->id ?? null,
                'status' => 'active',
            ]);
        }
    }
}
