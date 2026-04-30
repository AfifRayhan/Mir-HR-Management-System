<?php

namespace Database\Seeders;

use App\Models\OfficeTime;
use Illuminate\Database\Seeder;

class OfficeTimeSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'shift_name'   => 'General Shift',
                'short_name'   => 'GS',
                'start_time'   => '09:30:00',
                'end_time'     => '17:00:00',
                'late_after'   => '10:30:00',
                'absent_after' => '11:30:00',
                'lunch_start'  => '13:00:00',
                'lunch_end'    => '14:30:00',
            ],
            [
                'shift_name'   => 'Roster',
                'short_name'   => 'RT',
                'start_time'   => '00:00:00',
                'end_time'     => '00:00:00',
                'late_after'   => null,
                'absent_after' => null,
            ],
            [
                'shift_name'   => 'Half Day Shift',
                'short_name'   => 'HDS',
                'start_time'   => '07:00:00',
                'end_time'     => '19:00:00',
                'late_after'   => '08:00:00',
                'absent_after' => '09:00:00',
                'lunch_start'  => '13:00:00',
                'lunch_end'    => '14:00:00',
            ],

            [
                'shift_name'   => 'Kitchen Staff Duty',
                'short_name'   => 'KDS',
                'start_time'   => '08:00:00',
                'end_time'     => '16:00:00',
                'late_after'   => '09:00:00',
                'absent_after' => '10:00:00',
                'lunch_start'  => '13:00:00',
                'lunch_end'    => '14:00:00',
            ],
            [
                'shift_name'   => 'Peon General Shift',
                'short_name'   => 'PGS',
                'start_time'   => '08:00:00',
                'end_time'     => '18:00:00',
                'late_after'   => '09:00:00',
                'absent_after' => '10:00:00',
                'lunch_start'  => '13:00:00',
                'lunch_end'    => '14:00:00',
            ],
        ];

        foreach ($shifts as $shift) {
            OfficeTime::updateOrCreate(['shift_name' => $shift['shift_name']], $shift);
        }
    }
}
