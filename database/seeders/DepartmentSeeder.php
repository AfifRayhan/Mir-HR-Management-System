<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public static function getDepartments(): array
    {
        return [
            ['name' => 'Billing & IT', 'short_name' => 'BIT', 'description' => 'Billing and Information Technology', 'order_sequence' => 7, 'incharge_code' => '80882'],
            ['name' => 'Executive Office', 'short_name' => 'EXEC', 'description' => 'Executive Office', 'order_sequence' => 1, 'incharge_code' => '1302195'],
            ['name' => 'Field Operation', 'short_name' => 'FLD', 'description' => 'Field Operation', 'order_sequence' => 8, 'incharge_code' => null],
            ['name' => 'Finance & Accounts', 'short_name' => 'FIN', 'description' => 'Finance and Accounts', 'order_sequence' => 4, 'incharge_code' => '1302195'],
            ['name' => 'HR Admin & Legal', 'short_name' => 'HR', 'description' => 'Human Resources Admin and Legal', 'order_sequence' => 2, 'incharge_code' => '1302195'],
            ['name' => 'Infrastructure & Network Support', 'short_name' => 'INS', 'description' => 'Infrastructure and Network Support', 'order_sequence' => 11, 'incharge_code' => '80882'],
            ['name' => 'Operation & Maintenance', 'short_name' => 'O&M', 'description' => 'Operation and Maintenance', 'order_sequence' => 6, 'incharge_code' => '80882'],
            ['name' => 'Planning & Engineering', 'short_name' => 'P&E', 'description' => 'Planning and Engineering', 'order_sequence' => 5, 'incharge_code' => '80882'],
            ['name' => 'Restaurant - BOH', 'short_name' => 'BOH', 'description' => 'Restaurant - Back of House', 'order_sequence' => 10, 'incharge_code' => null],
            ['name' => 'Restaurant - FOH', 'short_name' => 'FOH', 'description' => 'Restaurant - Front of House', 'order_sequence' => 9, 'incharge_code' => null],
            ['name' => 'Sales & Marketing', 'short_name' => 'S&M', 'description' => 'Sales and Marketing', 'order_sequence' => 3, 'incharge_code' => '1302210'],
        ];
    }

    public function run(): void
    {
        foreach (self::getDepartments() as $dept) {
            Department::updateOrCreate(
                ['name' => $dept['name']],
                [
                    'short_name' => $dept['short_name'],
                    'description' => $dept['description'],
                    'order_sequence' => $dept['order_sequence'],
                    // incharge_id will be handled as late-binding in UserSeeder
                ]
            );
        }
    }
}
