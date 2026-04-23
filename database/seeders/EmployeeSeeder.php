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
        $defaultTime = OfficeTime::where('shift_name', 'General Shift')->value('id') ?? OfficeTime::first()->id ?? null;

        // composer require phpoffice/phpspreadsheet

        $filePath = base_path('EmployeeSummary_transformed.xlsx');

        if (!file_exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        // Map of employee_code => manager_hrmId, resolved after all rows are inserted
        $managerMap = [];

        // Data starts at row 2; row 1 is header in EmployeeSummary_transformed.xlsx
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 1) {
                continue;
            }

            // Columns based on transformed sheet:
            // A=#SL, B=Office, C=Department, D=Emp Id, E=Card No, F=Name
            // G=Blood Group, H=Father Name, I=Mother Name
            // J=Designation, K=Grade, L=Email
            // M=Joining Date, O=Date Of Birth, P=Status
            // Q=Section, R=Gross
            // S=HrmEmployeeId, T=DateOfDiscontinuation,U=ReasonOfDiscontinuation
            // V=SpouseName, W=Gender, X=Religion, Y=NationalId, Z=MaritalStatus
            // AA=NoOfChildren, AB=ContactNo
            // AC=EmergencyContactName, AD=EmergencyContactAddress
            // AE=EmergencyContactNo, AF=EmergencyContactPersonRelation
            // AG=PresentAddress, AH=PermanentAddress
            // AI=Manager_id, AJ = Personal_Email
            $office = trim((string) ($row['B'] ?? ''));
            $department = trim((string) ($row['C'] ?? ''));
            $designation = trim((string) ($row['J'] ?? ''));
            $grade = trim((string) ($row['K'] ?? ''));
            $name = trim((string) ($row['F'] ?? ''));
            $empId = trim((string) ($row['D'] ?? ''));
            $email = trim((string) ($row['L'] ?? ''));
            $personalEmail = trim((string) ($row['AJ'] ?? ''));
            $bloodGroup = trim((string) ($row['G'] ?? ''));
            $fatherName = trim((string) ($row['H'] ?? ''));
            $motherName = trim((string) ($row['I'] ?? ''));
            $joiningDate = trim((string) ($row['M'] ?? ''));
            $dateOfBirth = trim((string) ($row['O'] ?? ''));
            $status = trim((string) ($row['P'] ?? ''));
            $section = trim((string) ($row['Q'] ?? ''));
            $grossSalary = trim((string) ($row['R'] ?? ''));
            $hrmEmployeeId = trim((string) ($row['S'] ?? ''));
            $discontinuationDate = trim((string) ($row['T'] ?? ''));
            $discontinuationReason = trim((string) ($row['U'] ?? ''));
            $spouseName = trim((string) ($row['V'] ?? ''));
            $gender = trim((string) ($row['W'] ?? ''));
            $religion = trim((string) ($row['X'] ?? ''));
            $formatNumber = function($val) {
                $val = trim((string)$val);
                if (is_numeric($val) && stripos($val, 'E') !== false) {
                    return number_format((float)$val, 0, '', '');
                }
                return $val;
            };

            $nationalId = $formatNumber($row['Y'] ?? '');
            $maritalStatus = trim((string) ($row['Z'] ?? ''));
            $noOfChildren = trim((string) ($row['AA'] ?? ''));
            $contactNo = $formatNumber($row['AB'] ?? '');
            $emergencyContactName = trim((string) ($row['AC'] ?? ''));
            $emergencyContactAddress = trim((string) ($row['AD'] ?? ''));
            $emergencyContactNo = $formatNumber($row['AE'] ?? '');
            $emergencyContactRelation = trim((string) ($row['AF'] ?? ''));
            $presentAddress = trim((string) ($row['AG'] ?? ''));
            $permanentAddress = trim((string) ($row['AH'] ?? ''));
            $managerCode = trim((string) ($row['AI'] ?? '')); 
            $tin = trim((string) ($row['AK'] ?? ''));
            $nationality = trim((string) ($row['AL'] ?? ''));

            if (empty($office) || empty($department) || empty($name) || empty($empId) || $empId === '0123456') {
                continue;
            }

            $officeModel = Office::firstOrCreate(['name' => $office]);
            $departmentModel = Department::firstOrCreate(['name' => $department]);
            $designationModel = Designation::firstOrCreate(['name' => $designation]);
            $gradeModel = Grade::firstOrCreate(['name' => $grade]);
            
            $sectionModel = null;
            if (!empty($section)) {
                $sectionModel = \App\Models\Section::firstOrCreate(
                    ['name' => $section, 'department_id' => $departmentModel->id]
                );
            }

            $existing = Employee::where('employee_code', $empId)->first();

            $employeeData = [
                'hrm_employee_id' => !empty($hrmEmployeeId) ? $hrmEmployeeId : null,
                'name' => $name,
                'email' => !empty($email) ? $email : null,
                'personal_email' => !empty($personalEmail) ? $personalEmail : null,
                'blood_group' => !empty($bloodGroup) ? $bloodGroup : null,
                'father_name' => !empty($fatherName) ? $fatherName : null,
                'mother_name' => !empty($motherName) ? $motherName : null,
                'spouse_name' => !empty($spouseName) ? $spouseName : null,
                'gender' => !empty($gender) ? $gender : null,
                'religion' => !empty($religion) ? $religion : null,
                'marital_status' => !empty($maritalStatus) ? $maritalStatus : null,
                'national_id' => !empty($nationalId) ? $nationalId : null,
                'tin' => null, // TIN previously mapped to AJ, but AJ is now personal_email
                'nationality' => !empty($nationality) ? $nationality : 'Bangladeshi',
                'no_of_children' => !empty($noOfChildren) ? (int)$noOfChildren : null,
                'contact_no' => !empty($contactNo) ? $contactNo : null,
                'emergency_contact_name' => !empty($emergencyContactName) ? $emergencyContactName : null,
                'emergency_contact_address' => !empty($emergencyContactAddress) ? $emergencyContactAddress : null,
                'emergency_contact_no' => !empty($emergencyContactNo) ? $emergencyContactNo : null,
                'emergency_contact_relation' => !empty($emergencyContactRelation) ? $emergencyContactRelation : null,
                'date_of_birth' => !empty($dateOfBirth) ? date('Y-m-d', strtotime($dateOfBirth)) : null,
                'phone' => null,
                'present_address' => !empty($presentAddress) ? $presentAddress : null,
                'permanent_address' => !empty($permanentAddress) ? $permanentAddress : null,
                'joining_date' => !empty($joiningDate) ? date('Y-m-d', strtotime($joiningDate)) : null,
                'discontinuation_date' => !empty($discontinuationDate) ? date('Y-m-d', strtotime($discontinuationDate)) : null,
                'discontinuation_reason' => !empty($discontinuationReason) ? $discontinuationReason : null,
                'department_id' => $departmentModel->id,
                'section_id' => $sectionModel?->id,
                'designation_id' => $designationModel->id,
                'grade_id' => $gradeModel->id,
                'office_id' => $officeModel->id,
                'office_time_id' => $defaultTime,
                'status' => (strtolower($status) === 'active') ? 'active' : 'inactive',
                'gross_salary' => !empty($grossSalary) ? (float) str_replace(',', '', $grossSalary) : null,
            ];

            if ($existing) {
                $existing->update($employeeData);
            } else {
                $employeeData['employee_code'] = $empId;
                $employeeData['reporting_manager_id'] = null;
                Employee::create($employeeData);
            }

            // Store manager mapping (empCode => managerHrmId) for second pass
            if (!empty($managerCode)) {
                $managerMap[$empId] = $managerCode;
            }
        }

        // Second pass: resolve reporting_manager_id using hrm_employee_id directly from the database
        foreach ($managerMap as $empCode => $managerHrmId) {
            $employee = Employee::where('employee_code', $empCode)->first();
            $manager  = Employee::where('hrm_employee_id', $managerHrmId)->first();
            
            if ($employee && $manager) {
                $employee->update(['reporting_manager_id' => $manager->id]);
            }
        }

        $this->command->info('EmployeeSeeder completed.');
    }
}
