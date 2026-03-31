<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OfficeSeeder::class,
            WeeklyHolidaySeeder::class,
            MenuItemSeeder::class,
            DepartmentSeeder::class,
            SectionSeeder::class,
            DesignationSeeder::class,
            GradeSeeder::class,
            OfficeTimeSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
            LeaveTypeSeeder::class,
            LeaveBalanceSeeder::class,
        ]);
    }
}
