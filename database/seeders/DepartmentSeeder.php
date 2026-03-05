<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Information Technology', 'short_name' => 'IT', 'description' => 'Software and infrastructure management'],
            ['name' => 'Human Resources', 'short_name' => 'HR', 'description' => 'Employee relations and recruitment'],
            ['name' => 'Finance', 'short_name' => 'FIN', 'description' => 'Accounts and payroll'],
            ['name' => 'Operations', 'short_name' => 'OPS', 'description' => 'Daily business activities'],
            ['name' => 'Marketing', 'short_name' => 'MKT', 'description' => 'Brand and advertising'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['name' => $dept['name']], $dept);
        }
    }
}
