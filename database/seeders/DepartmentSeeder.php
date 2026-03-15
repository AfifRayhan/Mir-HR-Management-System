<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Information Technology', 'short_name' => 'IT',  'description' => 'Software and infrastructure management', 'order_sequence' => 1],
            ['name' => 'Human Resources',        'short_name' => 'HR',  'description' => 'Employee relations and recruitment',    'order_sequence' => 2],
            ['name' => 'Finance',                'short_name' => 'FIN', 'description' => 'Accounts and payroll',                'order_sequence' => 3],
            ['name' => 'Operations',             'short_name' => 'OPS', 'description' => 'Daily business activities',           'order_sequence' => 4],
            ['name' => 'Marketing',              'short_name' => 'MKT', 'description' => 'Brand and advertising',               'order_sequence' => 5],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['name' => $dept['name']], $dept);
        }
    }
}
