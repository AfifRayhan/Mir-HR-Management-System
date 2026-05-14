<?php

namespace Database\Seeders;

use App\Models\RosterTime;
use Illuminate\Database\Seeder;

class RosterTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            'tech-gulshan' => [
                'Technician A'       => ['label' => 'Shift A', 'start' => '07:00:00', 'end' => '15:00:00', 'badge' => 'badge-a'],
                'Technician B'       => ['label' => 'Shift B', 'start' => '15:00:00', 'end' => '22:00:00', 'badge' => 'badge-b'],
                'Technician C'       => ['label' => 'Shift C', 'start' => '22:00:00', 'end' => '07:00:00', 'badge' => 'badge-c', 'is_overnight' => true],
                'Technician General' => ['label' => 'General', 'start' => '09:00:00', 'end' => '18:00:00', 'badge' => 'badge-g'],
                'Off'                => ['label' => 'Off Day', 'start' => null,       'end' => null,       'badge' => 'badge-off', 'is_off' => true],
            ],
            'tech-borak' => [
                'Technician A'       => ['label' => 'Shift A', 'start' => '07:00:00', 'end' => '15:00:00', 'badge' => 'badge-a'],
                'Technician B'       => ['label' => 'Shift B', 'start' => '15:00:00', 'end' => '22:00:00', 'badge' => 'badge-b'],
                'Technician C'       => ['label' => 'Shift C', 'start' => '22:00:00', 'end' => '07:00:00', 'badge' => 'badge-c', 'is_overnight' => true],
                'Technician General' => ['label' => 'General', 'start' => '09:00:00', 'end' => '18:00:00', 'badge' => 'badge-g'],
                'Off'                => ['label' => 'Off Day', 'start' => null,       'end' => null,       'badge' => 'badge-off', 'is_off' => true],
            ],
            'tech-jessore' => [
                'Technician A'       => ['label' => 'Shift A', 'start' => '07:00:00', 'end' => '14:00:00', 'badge' => 'badge-a'],
                'Technician B'       => ['label' => 'Shift B', 'start' => '14:00:00', 'end' => '22:00:00', 'badge' => 'badge-b'],
                'Technician C'       => ['label' => 'Shift C', 'start' => '22:00:00', 'end' => '07:00:00', 'badge' => 'badge-c', 'is_overnight' => true],
                'Technician General' => ['label' => 'General', 'start' => '10:00:00', 'end' => '16:00:00', 'badge' => 'badge-g'],
                'Off'                => ['label' => 'Off Day', 'start' => null,       'end' => null,       'badge' => 'badge-off', 'is_off' => true],
            ],
            'noc-borak' => [
                'A'   => ['label' => 'Shift A', 'start' => '07:00:00', 'end' => '14:00:00', 'badge' => 'badge-a'],
                'B'   => ['label' => 'Shift B', 'start' => '14:00:00', 'end' => '22:00:00', 'badge' => 'badge-b'],
                'C'   => ['label' => 'Shift C', 'start' => '22:00:00', 'end' => '07:00:00', 'badge' => 'badge-c', 'is_overnight' => true],
                'EA'  => ['label' => 'Eid Shift A', 'start' => '07:00:00', 'end' => '15:00:00', 'badge' => 'badge-a'],
                'EB'  => ['label' => 'Eid Shift B', 'start' => '19:00:00', 'end' => '03:00:00', 'badge' => 'badge-b', 'is_overnight' => true],
                'Off' => ['label' => 'Off Day', 'start' => null,       'end' => null,       'badge' => 'badge-off', 'is_off' => true],
            ],
            'noc-sylhet' => [
                'A'       => ['label' => 'Shift A', 'start' => '09:00:00', 'end' => '13:00:00', 'badge' => 'badge-a'],
                'B'       => ['label' => 'Shift B', 'start' => '15:00:00', 'end' => '22:00:00', 'badge' => 'badge-b'],
                'C'       => ['label' => 'Shift C', 'start' => '22:00:00', 'end' => '09:00:00', 'badge' => 'badge-c', 'is_overnight' => true],
                'EA'      => ['label' => 'Eid Shift A', 'start' => '07:00:00', 'end' => '15:00:00', 'badge' => 'badge-a'],
                'EB'      => ['label' => 'Eid Shift B', 'start' => '19:00:00', 'end' => '03:00:00', 'badge' => 'badge-b', 'is_overnight' => true],
                'General' => ['label' => 'General', 'start' => '09:00:00', 'end' => '17:00:00', 'badge' => 'badge-g'],
                'Off'     => ['label' => 'Off Day', 'start' => null,       'end' => null,       'badge' => 'badge-off', 'is_off' => true],
            ],
            'tech-sylhet' => [
                'X'   => ['label' => 'Shift X', 'start' => '09:00:00', 'end' => '13:00:00', 'badge' => 'badge-a'],
                'Y'   => ['label' => 'Shift Y', 'start' => '10:00:00', 'end' => '18:00:00', 'badge' => 'badge-b'],
                'Z'   => ['label' => 'Shift Z', 'start' => '09:00:00', 'end' => '17:00:00', 'badge' => 'badge-c'],
                'Off' => ['label' => 'Off Day', 'start' => null,       'end' => null,       'badge' => 'badge-off', 'is_off' => true],
            ],
            'drivers' => [
                'DS'  => ['label' => 'Driver Shift', 'start' => '08:00:00', 'end' => '20:00:00', 'badge' => 'badge-a'],
                'DNS' => ['label' => 'Driver Night', 'start' => '20:00:00', 'end' => '06:00:00', 'badge' => 'badge-c', 'is_overnight' => true],
                'Off' => ['label' => 'Off Day',      'start' => null,       'end' => null,       'badge' => 'badge-off', 'is_off' => true],
            ]
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
                        'is_overnight'  => $data['is_overnight'] ?? false,
                    ]
                );
            }
        }
    }
}
