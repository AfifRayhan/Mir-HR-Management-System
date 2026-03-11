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
                'start_time'   => '09:30:00',
                'end_time'     => '17:30:00',
                'late_after'   => '10:15:00',
                'absent_after' => '11:30:00',
                'lunch_start'  => '13:00:00',
                'lunch_end'    => '14:00:00',
            ],
            [
                'shift_name'   => 'Morning Shift',
                'start_time'   => '08:00:00',
                'end_time'     => '17:00:00',
                'late_after'   => '08:15:00',
                'absent_after' => '09:30:00',
                'lunch_start'  => '12:30:00',
                'lunch_end'    => '13:30:00',
            ],
        ];

        foreach ($shifts as $shift) {
            OfficeTime::updateOrCreate(['shift_name' => $shift['shift_name']], $shift);
        }
    }
}
