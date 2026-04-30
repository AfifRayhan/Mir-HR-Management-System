<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\OfficeTime;
use Illuminate\Database\Seeder;

class DriverRosterGroupSeeder extends Seeder
{
    public function run(): void
    {
        $rosterShiftId = OfficeTime::where('shift_name', 'Roster')->value('id');

        if (!$rosterShiftId) {
            $this->command->error('Roster shift not found in OfficeTime table.');
            return;
        }

        $driverEmployeeCodes = [
            '15012032', '24079348', '25122090'
        ];

        // Update Drivers Group
        Employee::whereIn('employee_code', $driverEmployeeCodes)
            ->update([
                'roster_group' => 'Drivers',
                'office_time_id' => $rosterShiftId
            ]);

        $this->command->info('Updated ' . count($driverEmployeeCodes) . ' employees to Drivers group.');
    }
}
