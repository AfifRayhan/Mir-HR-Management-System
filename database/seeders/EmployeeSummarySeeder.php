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

class EmployeeSummarySeeder extends Seeder
{
    public function run(): void
    {
        // Make sure to install phpoffice/phpspreadsheet first:
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
