<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = ['Cleaner', 'Cook', 'Driver', 'Lineman', 'Management', 'Peon', 'Restaurant', 'Technician'];

        foreach ($grades as $grade) {
            Grade::firstOrCreate(['name' => $grade]);
        }
    }
}
