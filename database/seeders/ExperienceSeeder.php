<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeExperience;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExperienceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('EmployeeExperience.csv');

        if (!file_exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);

        // Id,HrmEmployeeId,Organization,Designation,Department,DateFrom,DateTo,Responsibilities
        
        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            $hrmEmployeeId = trim($data['HrmEmployeeId']);
            
            // Find employee by hrm_employee_id
            $employee = Employee::where('hrm_employee_id', $hrmEmployeeId)->first();

            if (!$employee) {
                // If not found by hrm_employee_id, maybe it matches employee_code?
                $employee = Employee::where('employee_code', $hrmEmployeeId)->first();
            }

            if ($employee) {
                EmployeeExperience::create([
                    'employee_id' => $employee->id,
                    'organization' => $data['Organization'] !== 'NULL' ? $data['Organization'] : null,
                    'designation' => $data['Designation'] !== 'NULL' ? $data['Designation'] : null,
                    'department' => $data['Department'] !== 'NULL' ? $data['Department'] : null,
                    'date_from' => $data['DateFrom'] !== '00:00.0' ? $data['DateFrom'] : null,
                    'date_to' => $data['DateTo'] !== '00:00.0' ? $data['DateTo'] : null,
                    'responsibilities' => $data['Responsibilities'] !== '.' ? $data['Responsibilities'] : null,
                ]);
                $count++;
            }
        }

        fclose($file);
        $this->command->info("ExperienceSeeder: Imported {$count} records.");
    }
}
