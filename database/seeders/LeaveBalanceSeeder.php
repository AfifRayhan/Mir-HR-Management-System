<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();
        $currentYear = Carbon::now()->year;

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found to initialize leave balances.');
            return;
        }

        if ($leaveTypes->isEmpty()) {
            $this->command->warn('No leave types found. Please run LeaveTypeSeeder first.');
            return;
        }

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $type) {
                // Initialize leave balance for each type for the current year
                LeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $type->id,
                        'year' => $currentYear,
                    ],
                    [
                        'opening_balance' => $type->total_days_per_year,
                        'used_days' => 0,
                        'remaining_days' => $type->total_days_per_year,
                    ]
                );
            }
        }

        $this->command->info('Leave balances initialized for all employees.');
    }
}
