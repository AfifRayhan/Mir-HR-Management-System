<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class Holiday2026Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            [
                'title' => 'International Labour Day',
                'from_date' => '2026-05-01',
                'to_date' => '2026-05-01',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Eid ul Adha',
                'from_date' => '2026-05-26',
                'to_date' => '2026-05-27',
                'total_days' => 2,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Eid ul Adha',
                'from_date' => '2026-05-28',
                'to_date' => '2026-05-28',
                'total_days' => 1,
                'type' => 'Eid Day',
            ],
            [
                'title' => 'Eid ul Adha',
                'from_date' => '2026-05-29',
                'to_date' => '2026-05-31',
                'total_days' => 3,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Ashura',
                'from_date' => '2026-06-26',
                'to_date' => '2026-06-26',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'July Mass Uprising Day',
                'from_date' => '2026-08-05',
                'to_date' => '2026-08-05',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Eid E Miladunnabi',
                'from_date' => '2026-08-26',
                'to_date' => '2026-08-26',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Janmashtami',
                'from_date' => '2026-09-04',
                'to_date' => '2026-09-04',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Durga Pooja (Bijoya Dashami)',
                'from_date' => '2026-10-20',
                'to_date' => '2026-10-20',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Durga Pooja (Bijoya Dashami)',
                'from_date' => '2026-10-21',
                'to_date' => '2026-10-21',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Victory Day',
                'from_date' => '2026-12-16',
                'to_date' => '2026-12-16',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
            [
                'title' => 'Christmas',
                'from_date' => '2026-12-25',
                'to_date' => '2026-12-25',
                'total_days' => 1,
                'type' => 'National Holiday',
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                [
                    'title' => $holiday['title'],
                    'from_date' => $holiday['from_date'],
                ],
                array_merge($holiday, [
                    'year' => 2026,
                    'all_office' => true,
                    'is_active' => true,
                ])
            );
        }
    }
}
