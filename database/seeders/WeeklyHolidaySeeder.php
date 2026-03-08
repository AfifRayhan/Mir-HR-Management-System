<?php

namespace Database\Seeders;

use App\Models\WeeklyHoliday;
use Illuminate\Database\Seeder;

class WeeklyHolidaySeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            'Saturday',
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday'
        ];

        foreach ($days as $day) {
            WeeklyHoliday::firstOrCreate(['day_name' => $day], [
                'is_holiday' => in_array($day, ['Friday', 'Saturday']) // Default Friday as holiday
            ]);
        }
    }
}
