<?php

namespace Database\Seeders;

use App\Models\RosterTime;
use Illuminate\Database\Seeder;

class DriverRosterTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            'drivers' => [
                'DDS' => ['label' => 'Driver Day Shift',   'start' => '08:00:00', 'end' => '18:00:00', 'badge' => 'badge-a'],
                'DNS' => ['label' => 'Driver Night Shift', 'start' => '20:00:00', 'end' => '06:00:00', 'badge' => 'badge-b'],
                'Off' => ['label' => 'Off Day',            'start' => null,       'end' => null,       'badge' => 'badge-off', 'is_off' => true],
            ],
        ];

        foreach ($configs as $groupSlug => $shifts) {
            foreach ($shifts as $key => $data) {
                RosterTime::updateOrCreate(
                    ['group_slug' => $groupSlug, 'shift_key' => $key],
                    [
                        'display_label' => $data['label'],
                        'start_time'    => $data['start'],
                        'end_time'      => $data['end'],
                        'badge_class'   => $data['badge'],
                        'is_off_day'    => $data['is_off'] ?? false,
                    ]
                );
            }
        }
    }
}
