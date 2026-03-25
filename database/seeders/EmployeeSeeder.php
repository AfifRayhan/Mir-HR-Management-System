<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\Office;
use App\Models\OfficeTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Core Users matching existing Employee records (Updating with office_id)
        $coreEmployees = [
            ['email' => 'teamlead@example.com', 'code' => 'EMP001', 'first' => 'Nadia', 'last' => 'Khan', 'manager_code' => null, 'department' => 'HR Admin & Legal', 'designation' => 'Manager', 'grade' => 'Management'],
            ['email' => 'david.chen@example.com', 'code' => 'EMP002', 'first' => 'David', 'last' => 'Chen', 'manager_code' => null, 'department' => 'Planning & Engineering', 'designation' => 'Manager', 'grade' => 'Management'],
            ['email' => 'employee@example.com', 'code' => 'EMP003', 'first' => 'Rakib', 'last' => 'Islam', 'manager_code' => 'EMP001', 'department' => 'HR Admin & Legal', 'designation' => 'Executive', 'grade' => 'Management'],
            ['email' => 'amir.khan@example.com', 'code' => 'EMP004', 'first' => 'Amir', 'last' => 'Khan', 'manager_code' => 'EMP001', 'department' => 'HR Admin & Legal', 'designation' => 'Assistant Manager', 'grade' => 'Management'],
            ['email' => 'linda.okafor@example.com', 'code' => 'EMP005', 'first' => 'Linda', 'last' => 'Okafor', 'manager_code' => 'EMP004', 'department' => 'HR Admin & Legal', 'designation' => 'Office Assistant', 'grade' => 'Peon'],
            ['email' => 'marco.rossi@example.com', 'code' => 'EMP006', 'first' => 'Marco', 'last' => 'Rossi', 'manager_code' => 'EMP004', 'department' => 'Restaurant - FOH', 'designation' => 'Restaurant Manager', 'grade' => 'Restaurant'],
        ];


        $defaultTime = OfficeTime::where('shift_name', 'General Shift')->value('id') ?? OfficeTime::first()->id ?? null;
        $defaultOffice = Office::first();

        foreach ($coreEmployees as $data) {
            $user = \App\Models\User::where('email', $data['email'])->first();

            if ($user && $user->employee_id !== $data['code']) {
                $user->update(['employee_id' => $data['code']]);
            }

            $manager = $data['manager_code'] ? Employee::where('employee_code', $data['manager_code'])->first() : null;

            $deptId = Department::where('name', $data['department'])->value('id') ?? Department::firstOrCreate(['name' => $data['department']])->id;
            $desigId = Designation::where('name', $data['designation'])->value('id') ?? Designation::firstOrCreate(['name' => $data['designation']])->id;
            $gradeId = Grade::where('name', $data['grade'])->value('id') ?? Grade::firstOrCreate(['name' => $data['grade']])->id;

            Employee::updateOrCreate(
                ['employee_code' => $data['code']],
                [
                    'user_id' => $user?->id,
                    'name' => trim($data['first'] . ' ' . $data['last']),
                    'department_id' => $deptId,
                    'designation_id' => $desigId,
                    'grade_id' => $gradeId,
                    'office_id' => $defaultOffice->id,
                    'office_time_id' => $defaultTime,
                    'reporting_manager_id' => $manager?->id,
                    'status' => 'active',
                    'joining_date' => now()->format('Y-m-d'),
                ]
            );
        }
        // composer require phpoffice/phpspreadsheet

        $filePath = base_path('EmployeeSummary_transformed.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Data starts at row 2; row 1 is header in EmployeeSummary_transformed.xlsx
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 1) {
                continue;
            }

            // Columns based on transformed sheet:
            // A=#SL, B=Office, C=Department, D=Emp Id, E=Card No, F=Name, K=Designation, L=Grade,
            // N=Joining Date, P=Date Of Birth
            $office = trim((string) ($row['B'] ?? ''));
            $department = trim((string) ($row['C'] ?? ''));
            $designation = trim((string) ($row['K'] ?? ''));
            $grade = trim((string) ($row['L'] ?? ''));
            $name = trim((string) ($row['F'] ?? '')); // Name column
            $empId = trim((string) ($row['D'] ?? '')); // Emp Id column
            $joiningDate = trim((string) ($row['N'] ?? ''));
            $dateOfBirth = trim((string) ($row['P'] ?? ''));

            if (empty($office) || empty($department) || empty($name) || empty($empId)) {
                continue;
            }

            $officeModel = Office::firstOrCreate(['name' => $office]);
            $departmentModel = Department::firstOrCreate(['name' => $department]);
            $designationModel = Designation::firstOrCreate(['name' => $designation]);
            $gradeModel = Grade::firstOrCreate(['name' => $grade]);

            $existing = Employee::where('employee_code', $empId)->first();
            if ($existing) {
                continue;
            }

            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?? $name;
            $lastName = $nameParts[1] ?? '';

            Employee::create([
                'employee_code' => $empId,
                'name' => $name,
                'date_of_birth' => !empty($dateOfBirth) ? date('Y-m-d', strtotime($dateOfBirth)) : null,
                'phone' => null,
                'address' => null,
                'joining_date' => !empty($joiningDate) ? date('Y-m-d', strtotime($joiningDate)) : null,
                'department_id' => $departmentModel->id,
                'section_id' => null,
                'designation_id' => $designationModel->id,
                'grade_id' => $gradeModel->id,
                'office_id' => $officeModel->id,
                'office_time_id' => OfficeTime::where('shift_name', 'General Shift')->value('id') ?? OfficeTime::first()->id ?? null,
                'reporting_manager_id' => null,
                'status' => 'active',
            ]);
        }

        $this->command->info('EmployeeSummarySeeder completed.');
    }
}
