<?php
// tests/Feature/EarnLeaveBelMergeTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EarnLeaveBelMergeTest extends TestCase
{
    use RefreshDatabase;

    private function makeEmployee(string $type, ?string $joiningDate): Employee
    {
        $role = Role::firstOrCreate(['name' => 'Employee'], ['description' => 'Employee']);
        $user = User::factory()->create(['role_id' => $role->id]);
        return Employee::factory()->create([
            'employee_type' => $type,
            'joining_date'  => $joiningDate,
            'user_id'       => $user->id,
            'status'        => 'active',
        ]);
    }

    // Test 1: BEL must NOT exist after seeder runs
    public function test_bel_leave_type_does_not_exist_after_seeder()
    {
        $this->artisan('db:seed', ['--class' => 'LeaveTypeSeeder']);
        $bel = LeaveType::where('name', 'LIKE', '%Bonus%')->first();
        $this->assertNull($bel, 'Bonus Earn Leave type should not exist after seeder.');
    }

    // Test 2: Non-probation, >=1 yr service gets EL = accrued (capped 30) + 10
    public function test_el_allocation_adds_10_for_eligible_employee()
    {
        $this->artisan('db:seed', ['--class' => 'LeaveTypeSeeder']);
        $el = LeaveType::where('name', 'LIKE', '%Earn Leave%')
                       ->where('name', 'NOT LIKE', '%Bonus%')
                       ->firstOrFail();

        // Employee joined 2 years ago: floor(730/18) = 40 -> capped 30, +10 = 40
        $employee = $this->makeEmployee('Regular', Carbon::now()->subYears(2)->toDateString());

        $controller = app(\App\Http\Controllers\Personnel\LeaveBalanceController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getAllocatedDays');
        $method->setAccessible(true);

        $allocated = $method->invoke($controller, $employee, $el);
        $this->assertEquals(40, $allocated);
    }

    // Test 3: Non-probation, < 1 yr service does NOT get the +10
    public function test_el_allocation_no_bonus_for_under_one_year()
    {
        $this->artisan('db:seed', ['--class' => 'LeaveTypeSeeder']);
        $el = LeaveType::where('name', 'LIKE', '%Earn Leave%')
                       ->where('name', 'NOT LIKE', '%Bonus%')
                       ->firstOrFail();

        // 6 months ago: floor(180/18) = 10, no +10
        $employee = $this->makeEmployee('Regular', Carbon::now()->subMonths(6)->toDateString());

        $controller = app(\App\Http\Controllers\Personnel\LeaveBalanceController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getAllocatedDays');
        $method->setAccessible(true);

        $allocated = $method->invoke($controller, $employee, $el);
        $this->assertEquals(10, $allocated);
    }

    // Test 4: Probation always gets 0 EL
    public function test_el_allocation_is_zero_for_probation()
    {
        $this->artisan('db:seed', ['--class' => 'LeaveTypeSeeder']);
        $el = LeaveType::where('name', 'LIKE', '%Earn Leave%')
                       ->where('name', 'NOT LIKE', '%Bonus%')
                       ->firstOrFail();

        $employee = $this->makeEmployee('Probation', Carbon::now()->subYears(3)->toDateString());

        $controller = app(\App\Http\Controllers\Personnel\LeaveBalanceController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getAllocatedDays');
        $method->setAccessible(true);

        $allocated = $method->invoke($controller, $employee, $el);
        $this->assertEquals(0, $allocated);
    }

    // Test 5: leave:merge-bel-into-el merges BEL rows into EL and removes BEL
    public function test_bel_balance_is_merged_into_el_by_command()
    {
        $this->artisan('db:seed', ['--class' => 'LeaveTypeSeeder']);

        // Manually create a legacy BEL type (simulating pre-migration data)
        $bel = LeaveType::create([
            'name' => 'Bonus Earn Leave (BEL)',
            'total_days_per_year' => 10,
            'carry_forward' => false,
            'sort_order' => 5,
        ]);
        $el = LeaveType::where('name', 'Earn Leave (EL)')->firstOrFail();

        $employee = $this->makeEmployee('Regular', Carbon::now()->subYears(2)->toDateString());

        LeaveBalance::create([
            'employee_id' => $employee->id, 'leave_type_id' => $el->id,
            'year' => 2026, 'opening_balance' => 20, 'used_days' => 3, 'remaining_days' => 17,
        ]);
        LeaveBalance::create([
            'employee_id' => $employee->id, 'leave_type_id' => $bel->id,
            'year' => 2026, 'opening_balance' => 10, 'used_days' => 2, 'remaining_days' => 8,
        ]);

        $this->artisan('leave:merge-bel-into-el')->assertSuccessful();

        $elBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $el->id)->where('year', 2026)->first();

        $this->assertNotNull($elBalance);
        $this->assertEquals(30, $elBalance->opening_balance); // 20 + 10
        $this->assertEquals(5,  $elBalance->used_days);       // 3 + 2
        $this->assertEquals(25, $elBalance->remaining_days);  // 17 + 8

        $this->assertDatabaseMissing('leave_balances', ['employee_id' => $employee->id, 'leave_type_id' => $bel->id]);
        $this->assertDatabaseMissing('leave_types', ['name' => 'Bonus Earn Leave (BEL)']);
    }
}
