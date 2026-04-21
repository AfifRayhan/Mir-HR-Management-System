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
                $openingBalance = $this->getAllocatedDays($employee, $type);

                // Approach 3: Bonus Earn Leave won't initialize if opening balance is 0 (due to < 1 year service)
                if (str_contains(strtolower($type->name), 'bonus') && $openingBalance == 0) {
                    continue;
                }

                // Initialize leave balance for each type for the current year
                LeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $type->id,
                        'year' => $currentYear,
                    ],
                    [
                        'opening_balance' => $openingBalance,
                        'used_days' => 0,
                        'remaining_days' => $openingBalance,
                    ]
                );
            }
        }

        $this->command->info('Leave balances initialized for all employees.');
    }

    private function getAllocatedDays($employee, $leaveType)
    {
        $nameStr = strtolower($leaveType->name);

        if ($employee->employee_type === 'Probation') {
            if (str_contains($nameStr, 'casual')) {
                return 4;
            } elseif (str_contains($nameStr, 'sick')) {
                return 4;
            } elseif (str_contains($nameStr, 'emergency')) {
                return 2;
            } elseif (str_contains($nameStr, 'earn')) {
                return 0;
            }
        } else {
            if (str_contains($nameStr, 'earn')) {
                // Determine if this is "Bonus Earn Leave" or regular "Earn Leave"
                if (str_contains($nameStr, 'bonus')) {
                    if ($employee->joining_date) {
                        $joinDate = \Carbon\Carbon::parse($employee->joining_date);
                        $yearsOfService = $joinDate->diffInYears(now());
                        return $yearsOfService >= 1 ? 10 : 0;
                    }
                    return 0;
                }

                if ($employee->joining_date) {
                    $joinDate = \Carbon\Carbon::parse($employee->joining_date);
                    $daysSinceJoin = $joinDate->diffInDays(now());
                    $earnLeave = floor($daysSinceJoin / 18);
                    return min(30, max(0, $earnLeave));
                } else {
                    return 0;
                }
            }
        }
        
        return $leaveType->total_days_per_year;
    }
}
