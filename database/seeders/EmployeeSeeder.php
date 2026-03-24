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
            ['email' => 'teamlead@example.com', 'code' => 'EMP001', 'first' => 'Nadia', 'last' => 'Khan', 'manager_code' => null],
            ['email' => 'david.chen@example.com', 'code' => 'EMP002', 'first' => 'David', 'last' => 'Chen', 'manager_code' => null],
            ['email' => 'employee@example.com', 'code' => 'EMP003', 'first' => 'Rakib', 'last' => 'Islam', 'manager_code' => 'EMP001'],
            ['email' => 'amir.khan@example.com', 'code' => 'EMP004', 'first' => 'Amir', 'last' => 'Khan', 'manager_code' => 'EMP001'],
            ['email' => 'linda.okafor@example.com', 'code' => 'EMP005', 'first' => 'Linda', 'last' => 'Okafor', 'manager_code' => 'EMP004'],
            ['email' => 'marco.rossi@example.com', 'code' => 'EMP006', 'first' => 'Marco', 'last' => 'Rossi', 'manager_code' => 'EMP004'],
        ];

        foreach ($coreEmployees as $index => $data) {
            if ($user = User::where('email', $data['email'])->first()) {
                $office = $offices[$index % $officeCount];

                $managerId = null;
                if (!empty($data['manager_code'])) {
                    $manager = Employee::where('employee_code', $data['manager_code'])->first();
                    $managerId = $manager ? $manager->id : null;
                }

                $joiningDate = '2021-05-10';
                $employeeCode = Employee::generateEmployeeCode($joiningDate);

                $employee = Employee::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_code' => $employeeCode,
                        'name' => $data['first'] . ' ' . $data['last'],
                        'date_of_birth' => fake()->date('Y-m-d', '-20 years'),
                        'phone' => fake()->phoneNumber(),
                        'address' => fake()->address(),
                        'joining_date' => $joiningDate,
                        'department_id' => $departments->random()->id ?? null,
                        'designation_id' => $designations->random()->id ?? null,
                        'grade_id' => $grades->random()->id ?? null,
                        'office_id' => $office->id,
                        'office_time_id' => $generalShift->id ?? $officeTimes->random()->id,
                        'reporting_manager_id' => $managerId,
                        'status' => 'active',
                    ]
                );

                // Store code for reference if needed (manager logic)
                $data['generated_code'] = $employee->employee_code;
            }
        }

        // Generate 30 more random employees
        $manager = Employee::where('employee_code', 'EMP002')->first();

        for ($i = 7; $i <= 37; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $office = $offices[$i % $officeCount];
            $joiningDate = fake()->date('Y-m-d', '-2 years');
            $employeeCode = Employee::generateEmployeeCode($joiningDate);

            Employee::create([
                'employee_code' => $employeeCode,
                'name' => $firstName . ' ' . $lastName,
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'date_of_birth' => fake()->date('Y-m-d', '-20 years'),
                'joining_date' => $joiningDate,
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
