<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        // Departments
        $bit = Department::where('short_name', 'BIT')->first();
        $exec = Department::where('short_name', 'EXEC')->first();
        $fld = Department::where('short_name', 'FLD')->first();
        $fin = Department::where('short_name', 'FIN')->first();
        $hr = Department::where('short_name', 'HR')->first();
        $ins = Department::where('short_name', 'INS')->first();
        $om = Department::where('short_name', 'O&M')->first();
        $sm = Department::where('short_name', 'S&M')->first();

        $sections = [
            // Infrastructure & Network Support
            ['department_id' => $ins->id, 'name' => 'Core Network'],
            ['department_id' => $ins->id, 'name' => 'Data Communication'],
            ['department_id' => $ins->id, 'name' => 'Transmission'],
            ['department_id' => $ins->id, 'name' => 'NOC'],
            ['department_id' => $ins->id, 'name' => 'Power'],
            ['department_id' => $ins->id, 'name' => 'IPTSP'],
            ['department_id' => $ins->id, 'name' => 'Cloud'],

            // Billing & IT
            ['department_id' => $bit->id, 'name' => 'IT'],
            ['department_id' => $bit->id, 'name' => 'Billing'],
            ['department_id' => $bit->id, 'name' => 'Software Development'],
            ['department_id' => $bit->id, 'name' => 'R&D and Security'],

            // HR Admin & Legal
            ['department_id' => $hr->id, 'name' => 'HR & Admin'],
            ['department_id' => $hr->id, 'name' => 'Legal & Compliance'],
            ['department_id' => $hr->id, 'name' => 'Regulatory Affairs'],
            ['department_id' => $hr->id, 'name' => 'Transport'],
            ['department_id' => $hr->id, 'name' => 'Others'],

            // Finance & Accounts
            ['department_id' => $fin->id, 'name' => 'Accounts'],
            ['department_id' => $fin->id, 'name' => 'Procurement'],

            // Executive Office
            ['department_id' => $exec->id, 'name' => "MD's Office"],
            ['department_id' => $exec->id, 'name' => "CEO's Office"],
            ['department_id' => $exec->id, 'name' => "COO's Office"],

            // Sales & Marketing
            ['department_id' => $sm->id, 'name' => 'Marketing'],
            ['department_id' => $sm->id, 'name' => 'Sales'],
            ['department_id' => $sm->id, 'name' => 'Business Development'],
            ['department_id' => $sm->id, 'name' => 'International Carrier Service'],

            // Operation & Maintenance
            ['department_id' => $om->id, 'name' => 'Maintenance'],
            ['department_id' => $om->id, 'name' => 'Commercial'],

            // Field Operation
            ['department_id' => $fld->id, 'name' => 'Field Operation'],
        ];

        foreach ($sections as $section) {
            \App\Models\Section::updateOrCreate(
                ['name' => $section['name']],
                $section
            );
        }
    }
}
