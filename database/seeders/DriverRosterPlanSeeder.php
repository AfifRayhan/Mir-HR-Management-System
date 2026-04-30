<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\RosterSchedule;
use Illuminate\Database\Seeder;

class DriverRosterPlanSeeder extends Seeder
{
    public function run(): void
    {
        $schedule = [
            '2026-05-01' => ['DNS' => ['Md. Shabuz Mia'], 'Off' => ['Md Jahidul Islam', 'Md. Milon Mridha']],
            '2026-05-02' => ['DDS' => ['Md. Milon Mridha'], 'Off' => ['Md. Shabuz Mia', 'Md Jahidul Islam']],
            '2026-05-03' => ['DDS' => ['Md. Milon Mridha'], 'Off' => ['Md. Shabuz Mia', 'Md Jahidul Islam']],
            '2026-05-04' => ['DDS' => ['Md. Milon Mridha'], 'Off' => ['Md. Shabuz Mia', 'Md Jahidul Islam']],
            '2026-05-05' => ['DDS' => ['Md. Milon Mridha'], 'Off' => ['Md. Shabuz Mia', 'Md Jahidul Islam']],
            '2026-05-06' => ['DDS' => ['Md. Milon Mridha'], 'Off' => ['Md. Shabuz Mia', 'Md Jahidul Islam']],
            '2026-05-07' => ['DDS' => ['Md. Milon Mridha'], 'Off' => ['Md. Shabuz Mia', 'Md Jahidul Islam']],
            '2026-05-08' => ['DDS' => ['Md. Milon Mridha'], 'DNS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia']],
            '2026-05-09' => ['DDS' => ['Md. Milon Mridha'], 'Off' => ['Md. Shabuz Mia', 'Md Jahidul Islam']],
            '2026-05-10' => ['DDS' => ['Md. Milon Mridha'], 'Off' => ['Md. Shabuz Mia', 'Md Jahidul Islam']],
            '2026-05-11' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-12' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-13' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-14' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-15' => ['DDS' => ['Md Jahidul Islam', 'Md. Milon Mridha'], 'DNS' => ['Md. Shabuz Mia']],
            '2026-05-16' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-17' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-18' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-19' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-20' => ['DDS' => ['Md Jahidul Islam'], 'Off' => ['Md. Shabuz Mia', 'Md. Milon Mridha']],
            '2026-05-21' => ['DDS' => ['Md. Shabuz Mia'], 'Off' => ['Md Jahidul Islam', 'Md. Milon Mridha']],
            '2026-05-22' => ['DDS' => ['Md. Shabuz Mia'], 'DNS' => ['Md. Milon Mridha'], 'Off' => ['Md Jahidul Islam']],
            '2026-05-23' => ['DDS' => ['Md. Shabuz Mia'], 'Off' => ['Md Jahidul Islam', 'Md. Milon Mridha']],
            '2026-05-24' => ['DDS' => ['Md. Shabuz Mia'], 'Off' => ['Md Jahidul Islam', 'Md. Milon Mridha']],
            '2026-05-25' => ['DDS' => ['Md. Shabuz Mia'], 'Off' => ['Md Jahidul Islam', 'Md. Milon Mridha']],
            '2026-05-26' => ['DDS' => ['Md. Shabuz Mia', 'Md. Milon Mridha'], 'Off' => ['Md Jahidul Islam']],
            '2026-05-27' => ['DDS' => ['Md. Shabuz Mia', 'Md. Milon Mridha'], 'Off' => ['Md Jahidul Islam']],
            '2026-05-28' => ['DDS' => ['Md. Shabuz Mia', 'Md. Milon Mridha'], 'Off' => ['Md Jahidul Islam']],
            '2026-05-29' => ['DDS' => ['Md. Shabuz Mia'], 'DNS' => ['Md. Milon Mridha'], 'Off' => ['Md Jahidul Islam']],
            '2026-05-30' => ['DDS' => ['Md. Shabuz Mia'], 'Off' => ['Md Jahidul Islam', 'Md. Milon Mridha']],
            '2026-05-31' => ['DDS' => ['Md. Shabuz Mia'], 'Off' => ['Md Jahidul Islam', 'Md. Milon Mridha']],
        ];

        foreach ($schedule as $date => $shifts) {
            foreach ($shifts as $shiftKey => $employeeNames) {
                foreach ($employeeNames as $name) {
                    $employee = Employee::where('name', $name)->first();
                    if (!$employee) continue;

                    RosterSchedule::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'date' => $date,
                        ],
                        [
                            'shift_type'  => $shiftKey,
                            'created_by'  => 1, // System or Admin ID
                        ]
                    );
                }
            }
        }
        
        $this->command->info('Driver Roster Plan seeded for May 2026.');
    }
}
