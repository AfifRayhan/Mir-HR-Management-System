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

        // Determine row where data begins; assume header row is first.
        $header = null;

        foreach ($rows as $rowIndex => $row) {
            if (trim($row['A']) === 'Office' && trim($row['B']) === 'Floor') {
                // start from next row
                $header = $rows[$rowIndex];
                continue;
            }

            if (!$header) {
                continue;
            }

            // Skip blank and metadata rows
            $office = trim((string) ($row['A'] ?? ''));
            $department = trim((string) ($row['C'] ?? ''));
            $designation = trim((string) ($row['N'] ?? ''));
            $grade = trim((string) ($row['P'] ?? ''));
            $name = trim((string) ($row['I'] ?? '')); // Name column
            $empId = trim((string) ($row['D'] ?? '')); // Emp Id column
            $joiningDate = trim((string) ($row['R'] ?? ''));
            $dateOfBirth = trim((string) ($row['V'] ?? ''));

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
