<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QualificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('EmployeeQualification.csv');

        if (!file_exists($filePath)) {
            $this->command->error("File not found: {$filePath}");
            return;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);

        // Id,HrmEmployeeId,Qualification,Level,Institution,BoardOrUniversity,,HrmPassingYear,GroupName,Result
        
        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < count($header)) continue;
            
            $data = array_combine($header, $row);

            $hrmEmployeeId = trim($data['HrmEmployeeId']);
            if (empty($hrmEmployeeId) || $hrmEmployeeId === 'NULL') continue;
            
            // Find employee by hrm_employee_id
            $employee = \App\Models\Employee::where('hrm_employee_id', $hrmEmployeeId)->first();

            if (!$employee) {
                // If not found by hrm_employee_id, fallback to employee_code
                $employee = \App\Models\Employee::where('employee_code', $hrmEmployeeId)->first();
            }

            if ($employee) {
                \App\Models\EmployeeQualification::create([
                    'employee_id' => $employee->id,
                    'qualification' => $data['Qualification'] !== 'NULL' ? $data['Qualification'] : null,
                    'level' => $data['Level'] !== 'NULL' ? $data['Level'] : null,
                    'institution' => $data['Institution'] !== 'NULL' ? $data['Institution'] : null,
                    'board_university' => $data['BoardOrUniversity'] !== 'NULL' ? $data['BoardOrUniversity'] : null,
                    'passing_year' => $data['HrmPassingYear'] !== '0' && $data['HrmPassingYear'] !== 'NULL' ? $data['HrmPassingYear'] : null,
                    'group_major' => $data['GroupName'] !== 'NULL' ? $data['GroupName'] : null,
                    'result' => $data['Result'] !== 'NULL' ? $data['Result'] : null,
                ]);
                $count++;
            }
        }

        fclose($file);
        $this->command->info("QualificationSeeder: Imported {$count} records.");
    }
}
