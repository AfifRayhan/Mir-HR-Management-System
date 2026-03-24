<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Billing & IT', 'short_name' => 'BIT', 'description' => 'Billing and Information Technology', 'order_sequence' => 1],
            ['name' => 'Executive Office', 'short_name' => 'EXEC', 'description' => 'Executive Office', 'order_sequence' => 2],
            ['name' => 'Field Operation', 'short_name' => 'FLD', 'description' => 'Field Operation', 'order_sequence' => 3],
            ['name' => 'Finance & Accounts', 'short_name' => 'FIN', 'description' => 'Finance and Accounts', 'order_sequence' => 4],
            ['name' => 'HR Admin & Legal', 'short_name' => 'HR', 'description' => 'Human Resources Admin and Legal', 'order_sequence' => 5],
            ['name' => 'Infrastructure & Network Support', 'short_name' => 'INS', 'description' => 'Infrastructure and Network Support', 'order_sequence' => 6],
            ['name' => 'Operation & Maintenance', 'short_name' => 'O&M', 'description' => 'Operation and Maintenance', 'order_sequence' => 7],
            ['name' => 'Planning & Engineering', 'short_name' => 'P&E', 'description' => 'Planning and Engineering', 'order_sequence' => 8],
            ['name' => 'Restaurant - BOH', 'short_name' => 'BOH', 'description' => 'Restaurant - Back of House', 'order_sequence' => 9],
            ['name' => 'Restaurant - FOH', 'short_name' => 'FOH', 'description' => 'Restaurant - Front of House', 'order_sequence' => 10],
            ['name' => 'Sales & Marketing', 'short_name' => 'S&M', 'description' => 'Sales and Marketing', 'order_sequence' => 11],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['name' => $dept['name']], $dept);
        }
    }
}
