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
            ['email' => 'teamlead@example.com', 'code' => 'EMP001', 'first' => 'Nadia', 'last' => 'Khan', 'manager_code' => null, 'department' => 'HR Admin & Legal', 'designation' => 'Manager', 'grade' => 'Management', 'salary' => 85000],
            ['email' => 'david.chen@example.com', 'code' => 'EMP002', 'first' => 'David', 'last' => 'Chen', 'manager_code' => null, 'department' => 'Planning & Engineering', 'designation' => 'Manager', 'grade' => 'Management', 'salary' => 75000],
            ['email' => 'employee@example.com', 'code' => 'EMP003', 'first' => 'Rakib', 'last' => 'Islam', 'manager_code' => 'EMP001', 'department' => 'HR Admin & Legal', 'designation' => 'Executive', 'grade' => 'Management', 'salary' => 45000],
            ['email' => 'amir.khan@example.com', 'code' => 'EMP004', 'first' => 'Amir', 'last' => 'Khan', 'manager_code' => 'EMP001', 'department' => 'HR Admin & Legal', 'designation' => 'Assistant Manager', 'grade' => 'Management', 'salary' => 55000],
            ['email' => 'linda.okafor@example.com', 'code' => 'EMP005', 'first' => 'Linda', 'last' => 'Okafor', 'manager_code' => 'EMP004', 'department' => 'HR Admin & Legal', 'designation' => 'Office Assistant', 'grade' => 'Peon', 'salary' => 15000],
            ['email' => 'marco.rossi@example.com', 'code' => 'EMP006', 'first' => 'Marco', 'last' => 'Rossi', 'manager_code' => 'EMP004', 'department' => 'Restaurant - FOH', 'designation' => 'Restaurant Manager', 'grade' => 'Restaurant', 'salary' => 35000],
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

            $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
            $fatherSuffixes = ['Ahmed', 'Uddin', 'Chowdhury', 'Hossain', 'Khan'];
            $motherSuffixes = ['Begum', 'Akter', 'Khatun', 'Nahar', 'Lata'];

            Employee::updateOrCreate(
                ['employee_code' => $data['code']],
                [
                    'user_id' => $user?->id,
                    'name' => trim($data['first'] . ' ' . $data['last']),
                    'email' => $data['email'],
                    'blood_group' => $bloodGroups[array_rand($bloodGroups)],
                    'father_name' => $data['last'] . ' ' . $fatherSuffixes[array_rand($fatherSuffixes)],
                    'mother_name' => 'Mrs. ' . $motherSuffixes[array_rand($motherSuffixes)],
                    'department_id' => $deptId,
                    'designation_id' => $desigId,
                    'grade_id' => $gradeId,
                    'office_id' => $defaultOffice->id,
                    'office_time_id' => $defaultTime,
                    'reporting_manager_id' => $manager?->id,
                    'status' => 'active',
                    'joining_date' => now()->format('Y-m-d'),
                    'gross_salary' => $data['salary'],
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
            $office = trim((string) ($row['B'] ?? ''));
            $department = trim((string) ($row['C'] ?? ''));
            $designation = trim((string) ($row['J'] ?? ''));
            $grade = trim((string) ($row['K'] ?? ''));
            $name = trim((string) ($row['F'] ?? '')); // Name column
            $empId = trim((string) ($row['D'] ?? '')); // Emp Id column
            $email = trim((string) ($row['L'] ?? '')); // Email column
            $bloodGroup = trim((string) ($row['G'] ?? '')); // Blood Group
            $fatherName = trim((string) ($row['H'] ?? '')); // Father Name
            $motherName = trim((string) ($row['I'] ?? '')); // Mother Name
            $joiningDate = trim((string) ($row['M'] ?? ''));
            $dateOfBirth = trim((string) ($row['O'] ?? ''));
            $status = trim((string) ($row['P'] ?? ''));
            $section = trim((string) ($row['Q'] ?? ''));
            $grossSalary = trim((string) ($row['R'] ?? ''));
            $discontinuationDate = trim((string) ($row['T'] ?? ''));
            $discontinuationReason = trim((string) ($row['U'] ?? ''));
            $spouseName = trim((string) ($row['V'] ?? ''));
            $gender = trim((string) ($row['W'] ?? ''));
            $religion = trim((string) ($row['X'] ?? ''));
            $nationalId = trim((string) ($row['Y'] ?? ''));
            $maritalStatus = trim((string) ($row['Z'] ?? ''));
            $noOfChildren = trim((string) ($row['AA'] ?? ''));
            $contactNo = trim((string) ($row['AB'] ?? ''));
            $emergencyContactName = trim((string) ($row['AC'] ?? ''));
            $emergencyContactAddress = trim((string) ($row['AD'] ?? ''));
            $emergencyContactNo = trim((string) ($row['AE'] ?? ''));
            $emergencyContactRelation = trim((string) ($row['AF'] ?? ''));
            $presentAddress = trim((string) ($row['AG'] ?? ''));
            $permanentAddress = trim((string) ($row['AH'] ?? ''));
            $tin = trim((string) ($row['AI'] ?? '')); // Adding TIN as sequential column
            $nationality = trim((string) ($row['AJ'] ?? '')); // Adding Nationality as sequential column

            if (empty($office) || empty($department) || empty($name) || empty($empId)) {
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
            if ($existing) {
                continue;
            }

            Employee::create([
                'employee_code' => $empId,
                'name' => $name,
                'email' => !empty($email) ? $email : null,
                'blood_group' => !empty($bloodGroup) ? $bloodGroup : null,
                'father_name' => !empty($fatherName) ? $fatherName : null,
                'mother_name' => !empty($motherName) ? $motherName : null,
                'spouse_name' => !empty($spouseName) ? $spouseName : null,
                'gender' => !empty($gender) ? $gender : null,
                'religion' => !empty($religion) ? $religion : null,
                'marital_status' => !empty($maritalStatus) ? $maritalStatus : null,
                'national_id' => !empty($nationalId) ? $nationalId : null,
                'tin' => !empty($tin) ? $tin : null,
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
                'office_time_id' => OfficeTime::where('shift_name', 'General Shift')->value('id') ?? OfficeTime::first()->id ?? null,
                'reporting_manager_id' => null,
                'status' => (strtolower($status) === 'active') ? 'active' : 'inactive',
                'gross_salary' => !empty($grossSalary) ? (float) str_replace(',', '', $grossSalary) : null,
            ]);
        }

        $this->command->info('EmployeeSeeder completed.');
    }
}
