<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\OfficeTime;
use Illuminate\Database\Seeder;

class RosterGroupSeeder extends Seeder
{
    public function run(): void
    {
        $rosterShiftId = OfficeTime::where('shift_name', 'Roster')->value('id');

        if (!$rosterShiftId) {
            $this->command->error('Roster shift not found in OfficeTime table.');
            return;
        }

        $nocBorakEmployeeCodes = [
            '24102086', '1304127', '25072088', '25019351', '24079347', 
            '25039352', '1304131', '1304128', '25079353'
        ];

        $nocSylhetEmployeeCodes = [
            '13041007', '1210304', '21111115', '23081141', '26011171'
        ];

        $techGulshanEmployeeCodes = [
            '17122050', '26019356', '23039337', '26019357', '1708321', 
            '1901327', '23039336', '23119343'
        ];

        $techBorakEmployeeCodes = [
            '1211309', '1702319', '1710322', '1211308', '23129345'
        ];

        $techSylhetEmployeeCodes = [
            '13041012', '18071095', '16091073', '24071155'
        ];

        $techJessoreEmployeeCodes = [
            '13042002', '1704320', '1605317', '23119342'
        ];

        // Update NOC (Borak) Group
        Employee::whereIn('employee_code', $nocBorakEmployeeCodes)
            ->update([
                'roster_group' => 'NOC (Borak)',
                'office_time_id' => $rosterShiftId
            ]);

        // Update NOC (Sylhet) Group
        Employee::whereIn('employee_code', $nocSylhetEmployeeCodes)
            ->update([
                'roster_group' => 'NOC (Sylhet)',
                'office_time_id' => $rosterShiftId
            ]);

        // Update Technician (Gulshan) Group
        Employee::whereIn('employee_code', $techGulshanEmployeeCodes)
            ->update([
                'roster_group' => 'Technician (Gulshan)',
                'office_time_id' => $rosterShiftId
            ]);

        // Update Technician (Borak) Group
        Employee::whereIn('employee_code', $techBorakEmployeeCodes)
            ->update([
                'roster_group' => 'Technician (Borak)',
                'office_time_id' => $rosterShiftId
            ]);

        // Update Technician (Sylhet) Group
        Employee::whereIn('employee_code', $techSylhetEmployeeCodes)
            ->update([
                'roster_group' => 'Technician (Sylhet)',
                'office_time_id' => $rosterShiftId
            ]);

        // Update Technician (Jessore) Group
        Employee::whereIn('employee_code', $techJessoreEmployeeCodes)
            ->update([
                'roster_group' => 'Technician (Jessore)',
                'office_time_id' => $rosterShiftId
            ]);

        $this->command->info('Updated ' . count($nocBorakEmployeeCodes) . ' employees to NOC (Borak) group.');
        $this->command->info('Updated ' . count($nocSylhetEmployeeCodes) . ' employees to NOC (Sylhet) group.');
        $this->command->info('Updated ' . count($techGulshanEmployeeCodes) . ' employees to Technician (Gulshan) group.');
        $this->command->info('Updated ' . count($techBorakEmployeeCodes) . ' employees to Technician (Borak) group.');
        $this->command->info('Updated ' . count($techSylhetEmployeeCodes) . ' employees to Technician (Sylhet) group.');
        $this->command->info('Updated ' . count($techJessoreEmployeeCodes) . ' employees to Technician (Jessore) group.');
    }
}
