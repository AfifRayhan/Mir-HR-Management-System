<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\RosterSchedule;
use App\Models\RosterTime;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RosterPlanSeeder extends Seeder
{
    const GROUP_MAP = [
        'noc-borak'     => 'NOC (Borak)',
        'noc-sylhet'    => 'NOC (Sylhet)',
        'tech-gulshan'  => 'Technician (Gulshan)',
        'tech-borak'    => 'Technician (Borak)',
        'tech-jessore'  => 'Technician (Jessore)',
        'tech-sylhet'   => 'Technician (Sylhet)',
    ];

    public function run(): void
    {
        // Start from the current week's Saturday (April 25, 2026)
        $startDate = Carbon::create(2026, 4, 25); 

        $plans = [
            'tech-sylhet' => [
                'employees' => ['Abdul Karim', 'Md Ripon Mia', 'Abdul Hamid', 'Shamim Ahmed'],
                'schedule' => [
                    0 => ['X' => ['Abdul Karim', 'Md Ripon Mia'], 'Y' => ['Abdul Hamid', 'Shamim Ahmed']], // Sat
                    1 => ['X' => ['Shamim Ahmed'], 'Z' => ['Abdul Karim', 'Md Ripon Mia'], 'Off' => ['Abdul Hamid']], // Sun
                    2 => ['X' => ['Abdul Hamid'], 'Y' => ['Shamim Ahmed'], 'Z' => ['Abdul Karim', 'Md Ripon Mia']], // Mon
                    3 => ['Y' => ['Abdul Hamid', 'Shamim Ahmed'], 'Z' => ['Abdul Karim', 'Md Ripon Mia']], // Tue
                    4 => ['Y' => ['Abdul Hamid', 'Shamim Ahmed'], 'Z' => ['Abdul Karim', 'Md Ripon Mia']], // Wed
                    5 => ['Y' => ['Abdul Hamid', 'Shamim Ahmed'], 'Z' => ['Abdul Karim', 'Md Ripon Mia']], // Thu
                    6 => ['Off' => ['Abdul Hamid', 'Abdul Karim', 'Md Ripon Mia', 'Shamim Ahmed']], // Fri
                ]
            ],
            'tech-jessore' => [
                'employees' => ['Md. Shahinur Rahman', 'Sumon Biswas', 'Raisul Islam Raju', 'Md. Saiful Islam'],
                'schedule' => [
                    0 => ['Technician A' => ['Md. Shahinur Rahman'], 'Technician B' => ['Sumon Biswas'], 'Technician C' => ['Raisul Islam Raju'], 'Off' => ['Md. Saiful Islam']],
                    1 => ['Technician B' => ['Raisul Islam Raju'], 'Technician C' => ['Sumon Biswas'], 'Off' => ['Md. Saiful Islam', 'Md. Shahinur Rahman']],
                    2 => ['Technician A' => ['Md. Saiful Islam'], 'Technician B' => ['Sumon Biswas'], 'Technician C' => ['Raisul Islam Raju'], 'Off' => ['Md. Shahinur Rahman']],
                    3 => ['Technician A' => ['Md. Saiful Islam'], 'Technician B' => ['Raisul Islam Raju'], 'Technician C' => ['Md. Shahinur Rahman'], 'Off' => ['Sumon Biswas']],
                    4 => ['Technician A' => ['Raisul Islam Raju'], 'Technician B' => ['Md. Shahinur Rahman'], 'Technician C' => ['Md. Saiful Islam'], 'Off' => ['Sumon Biswas']],
                    5 => ['Technician A' => ['Sumon Biswas'], 'Technician B' => ['Md. Shahinur Rahman'], 'Technician C' => ['Md. Saiful Islam'], 'Off' => ['Raisul Islam Raju']],
                    6 => ['Technician A' => ['Md. Shahinur Rahman'], 'Technician B' => ['Md. Saiful Islam'], 'Technician C' => ['Sumon Biswas'], 'Off' => ['Raisul Islam Raju']],
                ]
            ],
            'tech-borak' => [
                'employees' => ['Md Harun Ar Roshid Khan', 'Md. Morshed Prodhania', 'Md. Abu Rashed', 'Md. Mohan Hossain', 'Hangala Shadat'],
                'schedule' => [
                    0 => ['Technician A' => ['Md Harun Ar Roshid Khan'], 'Technician B' => ['Md. Morshed Prodhania'], 'Technician C' => ['Md. Abu Rashed', 'Md. Mohan Hossain'], 'Off' => ['Hangala Shadat']],
                    1 => ['Technician A' => ['Md Harun Ar Roshid Khan'], 'Technician B' => ['Md. Morshed Prodhania'], 'Technician C' => ['Md. Abu Rashed', 'Md. Mohan Hossain'], 'Off' => ['Hangala Shadat']],
                    2 => ['Technician A' => ['Md Harun Ar Roshid Khan'], 'Technician B' => ['Hangala Shadat'], 'Technician C' => ['Md. Mohan Hossain', 'Md. Morshed Prodhania'], 'Off' => ['Md. Abu Rashed']],
                    3 => ['Technician A' => ['Hangala Shadat'], 'Technician B' => ['Md. Mohan Hossain'], 'Technician C' => ['Md Harun Ar Roshid Khan'], 'Off' => ['Md. Abu Rashed', 'Md. Morshed Prodhania']],
                    4 => ['Technician A' => ['Md. Abu Rashed'], 'Technician B' => ['Hangala Shadat'], 'Technician C' => ['Md Harun Ar Roshid Khan'], 'Off' => ['Md. Mohan Hossain', 'Md. Morshed Prodhania']],
                    5 => ['Technician A' => ['Md. Abu Rashed'], 'Technician B' => ['Md Harun Ar Roshid Khan'], 'Technician C' => ['Hangala Shadat', 'Md. Morshed Prodhania'], 'Off' => ['Md. Mohan Hossain']],
                    6 => ['Technician A' => ['Md. Abu Rashed'], 'Technician B' => ['Md. Morshed Prodhania'], 'Technician C' => ['Hangala Shadat', 'Md. Mohan Hossain'], 'Off' => ['Md Harun Ar Roshid Khan']],
                ]
            ],
            'tech-gulshan' => [
                'employees' => ['Md Arif Khan', 'Md. Alamgir Hossain', 'Md. Emdadul Haque', 'Md. Mosaddek Alam', 'Md. Mahamud Hasan', 'Firoze Hossain', 'Mosarof Hossain', 'Md Billal Hossain'],
                'schedule' => [
                    0 => ['Technician A' => ['Md Arif Khan'], 'Technician B' => ['Md. Alamgir Hossain'], 'Technician C' => ['Md. Emdadul Haque', 'Md. Mosaddek Alam'], 'Technician General' => ['Md. Mahamud Hasan'], 'Off' => ['Firoze Hossain', 'Md Billal Hossain', 'Mosarof Hossain']],
                    1 => ['Technician A' => ['Firoze Hossain', 'Md Arif Khan'], 'Technician B' => ['Md Billal Hossain', 'Md. Mahamud Hasan'], 'Technician C' => ['Md. Emdadul Haque', 'Md. Mosaddek Alam'], 'Off' => ['Md. Alamgir Hossain', 'Mosarof Hossain']],
                    2 => ['Technician A' => ['Firoze Hossain', 'Mosarof Hossain'], 'Technician B' => ['Md Billal Hossain', 'Md. Mosaddek Alam'], 'Technician C' => ['Md Arif Khan', 'Md. Mahamud Hasan'], 'Off' => ['Md. Alamgir Hossain', 'Md. Emdadul Haque']],
                    3 => ['Technician A' => ['Md Billal Hossain', 'Mosarof Hossain'], 'Technician B' => ['Firoze Hossain', 'Md. Alamgir Hossain'], 'Technician C' => ['Md Arif Khan', 'Md. Mahamud Hasan'], 'Off' => ['Md. Emdadul Haque', 'Md. Mosaddek Alam']],
                    4 => ['Technician A' => ['Md Billal Hossain', 'Md. Emdadul Haque'], 'Technician B' => ['Firoze Hossain', 'Md. Mahamud Hasan'], 'Technician C' => ['Md. Alamgir Hossain', 'Mosarof Hossain'], 'Off' => ['Md Arif Khan', 'Md. Mosaddek Alam']],
                    5 => ['Technician A' => ['Firoze Hossain', 'Md. Mosaddek Alam'], 'Technician B' => ['Md Billal Hossain', 'Md. Emdadul Haque'], 'Technician C' => ['Md. Alamgir Hossain', 'Mosarof Hossain'], 'Off' => ['Md Arif Khan', 'Md. Mahamud Hasan']],
                    6 => ['Technician A' => ['Md Arif Khan'], 'Technician B' => ['Mosarof Hossain'], 'Technician C' => ['Md. Alamgir Hossain', 'Md. Emdadul Haque'], 'Technician General' => ['Md. Mosaddek Alam'], 'Off' => ['Firoze Hossain', 'Md Billal Hossain', 'Md. Mahamud Hasan']],
                ]
            ],
            'noc-sylhet' => [
                'employees' => ['Sipon Dey', 'Ruman Miaa', 'Sudip Paul', 'Md. Ikbal Hossain Kazol', 'Partho Ghosh'],
                'schedule' => [
                    0 => ['A' => ['Sipon Dey'], 'B' => ['Ruman Miaa'], 'C' => ['Sudip Paul'], 'General' => ['Md. Ikbal Hossain Kazol', 'Partho Ghosh']],
                    1 => ['B' => ['Md. Ikbal Hossain Kazol'], 'C' => ['Partho Ghosh'], 'General' => ['Ruman Miaa', 'Sipon Dey'], 'Off' => ['Sudip Paul']],
                    2 => ['B' => ['Sudip Paul'], 'C' => ['Partho Ghosh'], 'General' => ['Md. Ikbal Hossain Kazol', 'Ruman Miaa', 'Sipon Dey']],
                    3 => ['B' => ['Sudip Paul'], 'C' => ['Md. Ikbal Hossain Kazol'], 'General' => ['Ruman Miaa', 'Sipon Dey'], 'Off' => ['Partho Ghosh']],
                    4 => ['B' => ['Partho Ghosh'], 'C' => ['Md. Ikbal Hossain Kazol'], 'General' => ['Sipon Dey', 'Sudip Paul'], 'Off' => ['Ruman Miaa']],
                    5 => ['B' => ['Partho Ghosh'], 'General' => ['Md. Ikbal Hossain Kazol', 'Sipon Dey', 'Sudip Paul'], 'Off' => ['Ruman Miaa']],
                    6 => ['B' => ['Md. Ikbal Hossain Kazol'], 'C' => ['Sudip Paul'], 'General' => ['Partho Ghosh'], 'Off' => ['Ruman Miaa', 'Sipon Dey']],
                ]
            ],
            'noc-borak' => [
                'employees' => ['Md Sabid Khan', 'Md. Arif Kabir', 'Mahmudul Hasan', 'Rezaul Islam', 'Md Younus Maruf', 'Tanbir Sikder', 'Md. Azahar Hossain', 'Md. Farhad Uddin Mozumder', 'Mubeen Abdullah'],
                'schedule' => [
                    0 => ['A' => ['Md Sabid Khan', 'Md. Arif Kabir'], 'B' => ['Mahmudul Hasan', 'Rezaul Islam'], 'C' => ['Md Younus Maruf', 'Tanbir Sikder'], 'Off' => ['Md. Azahar Hossain', 'Md. Farhad Uddin Mozumder', 'Mubeen Abdullah']],
                    1 => ['A' => ['Md Sabid Khan', 'Md. Arif Kabir', 'Md. Azahar Hossain'], 'B' => ['Md. Farhad Uddin Mozumder', 'Mubeen Abdullah'], 'C' => ['Mahmudul Hasan', 'Rezaul Islam'], 'Off' => ['Md Younus Maruf', 'Tanbir Sikder']],
                    2 => ['A' => ['Md. Farhad Uddin Mozumder', 'Mubeen Abdullah'], 'B' => ['Md Sabid Khan', 'Md. Azahar Hossain'], 'C' => ['Mahmudul Hasan', 'Md. Arif Kabir'], 'Off' => ['Md Younus Maruf', 'Rezaul Islam', 'Tanbir Sikder']],
                    3 => ['A' => ['Md Younus Maruf', 'Md. Azahar Hossain', 'Tanbir Sikder'], 'B' => ['Md. Farhad Uddin Mozumder', 'Mubeen Abdullah'], 'C' => ['Md Sabid Khan', 'Md. Arif Kabir'], 'Off' => ['Mahmudul Hasan', 'Rezaul Islam']],
                    4 => ['A' => ['Md Younus Maruf', 'Tanbir Sikder'], 'B' => ['Md. Farhad Uddin Mozumder', 'Rezaul Islam'], 'C' => ['Md. Azahar Hossain', 'Mubeen Abdullah'], 'Off' => ['Mahmudul Hasan', 'Md Sabid Khan', 'Md. Arif Kabir']],
                    5 => ['A' => ['Mahmudul Hasan', 'Md. Farhad Uddin Mozumder', 'Tanbir Sikder'], 'B' => ['Md Younus Maruf', 'Rezaul Islam'], 'C' => ['Md. Azahar Hossain', 'Mubeen Abdullah'], 'Off' => ['Md Sabid Khan', 'Md. Arif Kabir']],
                    6 => ['A' => ['Md Sabid Khan', 'Md. Arif Kabir'], 'B' => ['Mahmudul Hasan', 'Md Younus Maruf'], 'C' => ['Rezaul Islam', 'Tanbir Sikder'], 'Off' => ['Md. Azahar Hossain', 'Md. Farhad Uddin Mozumder', 'Mubeen Abdullah']],
                ]
            ],
        ];

        foreach ($plans as $groupSlug => $plan) {
            $groupLabel = self::GROUP_MAP[$groupSlug];
            
            // Update employees to be in the correct roster group
            foreach ($plan['employees'] as $name) {
                Employee::where('name', $name)->update([
                    'roster_group' => $groupLabel,
                    'office_time_id' => 2, // ID 2 is "Roster" shift
                ]);
            }

            // Seed for 6 weeks to cover current month and next month
            for ($week = 0; $week < 6; $week++) {
                foreach ($plan['schedule'] as $dayOffset => $shifts) {
                    $date = $startDate->copy()->addWeeks($week)->addDays($dayOffset)->toDateString();
                    
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
                                    'created_by'  => 1,
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
