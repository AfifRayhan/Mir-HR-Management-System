<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            ['name' => 'Project Manager', 'short_name' => 'PM', 'priority' => 10],
            ['name' => 'Senior Software Engineer', 'short_name' => 'Sr. SE', 'priority' => 8],
            ['name' => 'Software Engineer', 'short_name' => 'SE', 'priority' => 5],
            ['name' => 'HR Manager', 'short_name' => 'HRM', 'priority' => 7],
            ['name' => 'Finance Officer', 'short_name' => 'FO', 'priority' => 4],
        ];

        foreach ($designations as $desig) {
            Designation::firstOrCreate(['name' => $desig['name']], $desig);
        }
    }
}
