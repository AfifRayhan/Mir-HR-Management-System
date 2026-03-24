<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $bit = Department::where('short_name', 'BIT')->first();
        $hr = Department::where('short_name', 'HR')->first();
        $fin = Department::where('short_name', 'FIN')->first();

        $sections = [
            ['department_id' => $bit->id, 'name' => 'Software Development', 'description' => 'Web and Mobile development'],
            ['department_id' => $bit->id, 'name' => 'Quality Assurance', 'description' => 'Testing and QC'],
            ['department_id' => $hr->id, 'name' => 'Recruitment', 'description' => 'Hiring and onboarding'],
            ['department_id' => $fin->id, 'name' => 'Payroll', 'description' => 'Salary processing'],
        ];

        foreach ($sections as $sec) {
            Section::firstOrCreate(['name' => $sec['name'], 'department_id' => $sec['department_id']], $sec);
        }
    }
}
