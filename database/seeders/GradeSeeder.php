<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = ['Management', 'Technician', 'Lineman'];

        foreach ($grades as $grade) {
            Grade::firstOrCreate(['name' => $grade]);
        }
    }
}
