<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Casual Leave (CL)',
                'total_days_per_year' => 10,
                'max_consecutive_days' => 3,
                'carry_forward' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sick Leave (SL)',
                'total_days_per_year' => 14,
                'max_consecutive_days' => null,
                'carry_forward' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Annual Leave (AL)',
                'total_days_per_year' => 15,
                'max_consecutive_days' => 10,
                'carry_forward' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Parental Leave',
                'total_days_per_year' => 120,
                'max_consecutive_days' => null,
                'carry_forward' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Emergency Leave (EL)',
                'total_days_per_year' => 5,
                'max_consecutive_days' => null,
                'carry_forward' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($leaveTypes as $type) {
            \App\Models\LeaveType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
