# Merge Bonus Earn Leave Into Earn Leave — Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Remove the "Bonus Earn Leave (BEL)" leave type entirely and make eligible employees receive +10 days added directly onto their Earn Leave (EL) opening balance instead.

**Architecture:** BEL never existed as a concept to employees — it was always a raw HR accounting category. We dissolve it by: (1) updating the `getAllocatedDays()` helper in both seeder and controller to fold BEL's 10 days into EL for eligible employees, (2) writing a one-time Artisan command that merges any live BEL balances into EL and deletes BEL rows, and (3) removing BEL from the seeder so it is never re-created.

**Tech Stack:** Laravel 11, PHP, MySQL — Artisan commands, Eloquent ORM, PHPUnit feature tests.

---

## Background Context

### How Leave Balances Work

The `leave_balances` table has one row per `(employee_id, leave_type_id, year)`. The row stores `opening_balance`, `used_days`, and `remaining_days`. No separate "carry forward" column exists — carry forward is implied: `opening_balance` for a new year = new entitlement + previous year's `remaining_days` (only for types where `carry_forward = true`).

### Current BEL Rules (to dissolve)

- BEL is a separate `leave_types` row: name = `"Bonus Earn Leave (BEL)"`, 10 days/year, `carry_forward = false`.
- Only employees with `employee_type != 'Probation'` AND `joining_date` >= 1 year ago qualify.
- In `LeaveBalanceController::store()` and `LeaveBalanceSeeder::run()`, BEL is skipped if `openingBalance == 0`.

### New EL Rules (after this plan)

- EL entitlement for **non-Probation** employees = `floor(days_since_joining / 18)` capped at 30, **plus** +10 if `years_of_service >= 1`.
- Probation employees: EL = 0 (unchanged).
- BEL `leave_type` row and all `leave_balances` rows referencing it are removed.

---

## Task 1: Write Feature Tests (TDD — Red Phase)

**Files:**
- `[ ]` Create `tests/Feature/EarnLeaveBelMergeTest.php`

**Step 1: Write the failing tests**

```php
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
```

**Step 2: Run to confirm all 5 fail**

```bash
php.exe artisan test --filter EarnLeaveBelMergeTest
```

Expected: **5 FAIL**

**Step 3: Commit test file**

```bash
git add tests/Feature/EarnLeaveBelMergeTest.php
git commit -m "test: add failing tests for BEL-into-EL merge"
```

---

## Task 2: Remove BEL From LeaveTypeSeeder

**Files:**
- `[ ]` Modify `database/seeders/LeaveTypeSeeder.php`

**Step 1: Delete the BEL array entry (lines 44–50)**

Remove this entire array block from the `$leaveTypes` array:

```php
// DELETE:
[
    'name' => 'Bonus Earn Leave (BEL)',
    'total_days_per_year' => 10,
    'max_consecutive_days' => null,
    'carry_forward' => false,
    'sort_order' => 5,
],
```

**Step 2: Run test 1 to confirm it passes**

```bash
php.exe artisan test --filter test_bel_leave_type_does_not_exist_after_seeder
```

Expected: **PASS**

**Step 3: Commit**

```bash
git add database/seeders/LeaveTypeSeeder.php
git commit -m "feat: remove Bonus Earn Leave from LeaveTypeSeeder"
```

---

## Task 3: Update `getAllocatedDays()` in LeaveBalanceController

**Files:**
- `[ ]` Modify `app/Http/Controllers/Personnel/LeaveBalanceController.php`

**Step 1: Replace the `earn` branch in the non-Probation `else` block (lines 175–194)**

```php
// BEFORE (lines 174-194):
} else {
    if (str_contains($nameStr, 'earn')) {
        // Determine if this is "Bonus Earn Leave" or regular "Earn Leave"
        if (str_contains($nameStr, 'bonus')) {
            if ($employee->joining_date) {
                $joinDate = Carbon::parse($employee->joining_date);
                $yearsOfService = $joinDate->diffInYears(now());
                return $yearsOfService >= 1 ? 10 : 0;
            }
            return 0;
        }

        if ($employee->joining_date) {
            $joinDate = Carbon::parse($employee->joining_date);
            $daysSinceJoin = $joinDate->diffInDays(now());
            $earnLeave = floor($daysSinceJoin / 18);
            return min(30, max(0, $earnLeave));
        } else {
            return 0;
        }
    }
}
```

```php
// AFTER:
} else {
    if (str_contains($nameStr, 'earn')) {
        if (!$employee->joining_date) {
            return 0;
        }
        $joinDate       = Carbon::parse($employee->joining_date);
        $daysSinceJoin  = $joinDate->diffInDays(now());
        $yearsOfService = $joinDate->diffInYears(now());

        // Accrued EL: 1 day per 18 working days, capped at 30
        $earnLeave = (int) min(30, max(0, floor($daysSinceJoin / 18)));

        // BEL bonus folded in: +10 for employees with >= 1 year service
        if ($yearsOfService >= 1) {
            $earnLeave += 10;
        }

        return $earnLeave;
    }
}
```

**Step 2: Remove the BEL guard in `store()` (lines 112–116)**

```php
// DELETE these 4 lines from store():
// Approach 3: Bonus Earn Leave won't initialize if opening balance is 0 (due to < 1 year service)
if (str_contains(strtolower($type->name), 'bonus') && $openingBalance == 0) {
    $skippedCount++;
    continue;
}
```

**Step 3: Run tests 2, 3, 4**

```bash
php.exe artisan test --filter "test_el_allocation_adds_10|test_el_allocation_no_bonus|test_el_allocation_is_zero"
```

Expected: **3 PASS**

**Step 4: Commit**

```bash
git add app/Http/Controllers/Personnel/LeaveBalanceController.php
git commit -m "feat: fold BEL +10 into EL in LeaveBalanceController::getAllocatedDays"
```

---

## Task 4: Update `getAllocatedDays()` in LeaveBalanceSeeder

**Files:**
- `[ ]` Modify `database/seeders/LeaveBalanceSeeder.php`

**Step 1: Replace the `earn` branch in the non-Probation block (lines 75–94)**

```php
// BEFORE (lines 74-94):
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
```

```php
// AFTER:
} else {
    if (str_contains($nameStr, 'earn')) {
        if (!$employee->joining_date) {
            return 0;
        }
        $joinDate       = \Carbon\Carbon::parse($employee->joining_date);
        $daysSinceJoin  = $joinDate->diffInDays(now());
        $yearsOfService = $joinDate->diffInYears(now());

        // Accrued EL: 1 day per 18 working days, capped at 30
        $earnLeave = (int) min(30, max(0, floor($daysSinceJoin / 18)));

        // BEL bonus folded in: +10 for employees with >= 1 year service
        if ($yearsOfService >= 1) {
            $earnLeave += 10;
        }

        return $earnLeave;
    }
}
```

**Step 2: Remove the BEL guard in `run()` (lines 36–39)**

```php
// DELETE these 3 lines from run():
// Approach 3: Bonus Earn Leave won't initialize if opening balance is 0 (due to < 1 year service)
if (str_contains(strtolower($type->name), 'bonus') && $openingBalance == 0) {
    continue;
}
```

**Step 3: Run full test suite**

```bash
php.exe artisan test --filter EarnLeaveBelMergeTest
```

Expected: **4 of 5 pass** (test 5 still needs the Artisan command from Task 5).

**Step 4: Commit**

```bash
git add database/seeders/LeaveBalanceSeeder.php
git commit -m "feat: fold BEL +10 into EL in LeaveBalanceSeeder::getAllocatedDays"
```

---

## Task 5: Create the One-Time Migration Artisan Command

**Files:**
- `[ ]` Create `app/Console/Commands/MergeBelIntoEl.php`

**Step 1: Generate the command file**

```bash
php.exe artisan make:command MergeBelIntoEl
```

**Step 2: Implement the command**

Replace the generated stub content with:

```php
<?php
// app/Console/Commands/MergeBelIntoEl.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\DB;

class MergeBelIntoEl extends Command
{
    protected $signature   = 'leave:merge-bel-into-el';
    protected $description = 'One-time: merge all Bonus Earn Leave (BEL) balances into Earn Leave (EL) and remove BEL type.';

    public function handle(): int
    {
        $bel = LeaveType::where('name', 'LIKE', '%Bonus%')
                        ->where('name', 'LIKE', '%Earn%')
                        ->first();

        if (!$bel) {
            $this->info('No Bonus Earn Leave type found — nothing to migrate.');
            return self::SUCCESS;
        }

        $el = LeaveType::where('name', 'LIKE', '%Earn Leave%')
                       ->where('name', 'NOT LIKE', '%Bonus%')
                       ->first();

        if (!$el) {
            $this->error('Earn Leave (EL) type not found. Aborting.');
            return self::FAILURE;
        }

        $belBalances = LeaveBalance::where('leave_type_id', $bel->id)->get();
        $merged = 0;

        DB::transaction(function () use ($belBalances, $el, $bel, &$merged) {
            foreach ($belBalances as $belBalance) {
                $elBalance = LeaveBalance::where('employee_id', $belBalance->employee_id)
                    ->where('leave_type_id', $el->id)
                    ->where('year', $belBalance->year)
                    ->first();

                if ($elBalance) {
                    // Merge BEL into existing EL row
                    $elBalance->opening_balance += $belBalance->opening_balance;
                    $elBalance->used_days       += $belBalance->used_days;
                    $elBalance->remaining_days  += $belBalance->remaining_days;
                    $elBalance->save();
                } else {
                    // No EL row exists — create one from BEL's values under EL type
                    LeaveBalance::create([
                        'employee_id'    => $belBalance->employee_id,
                        'leave_type_id'  => $el->id,
                        'year'           => $belBalance->year,
                        'opening_balance'=> $belBalance->opening_balance,
                        'used_days'      => $belBalance->used_days,
                        'remaining_days' => $belBalance->remaining_days,
                    ]);
                }

                $belBalance->delete();
                $merged++;
            }

            // Delete the BEL leave_type record itself
            $bel->delete();
        });

        $this->info("Done. {$merged} BEL balance row(s) merged into EL. BEL leave type deleted.");
        return self::SUCCESS;
    }
}
```

**Step 3: Run all 5 tests**

```bash
php.exe artisan test --filter EarnLeaveBelMergeTest
```

Expected: **5 PASS**

**Step 4: Commit**

```bash
git add app/Console/Commands/MergeBelIntoEl.php
git commit -m "feat: add leave:merge-bel-into-el artisan command for one-time BEL data migration"
```

---

## Task 6: Run Migration on Live Data & Final Verification

**Step 1: Run full test suite (no regressions)**

```bash
php.exe artisan test
```

Expected: All existing tests pass.

**Step 2: Run the command on the real database**

```bash
php.exe artisan leave:merge-bel-into-el
```

Expected output:
```
Done. N BEL balance row(s) merged into EL. BEL leave type deleted.
```

**Step 3: Verify BEL is gone from database**

```bash
php.exe artisan tinker --execute="echo 'BEL types remaining: ' . App\Models\LeaveType::where('name','LIKE','%Bonus%')->count();"
```

Expected: `BEL types remaining: 0`

**Step 4: Final commit**

```bash
git add -A
git commit -m "feat: complete BEL->EL merge — BEL dissolved, eligible employees get +10 EL"
```

---

## Verification Plan

### Automated Tests
- `php.exe artisan test --filter EarnLeaveBelMergeTest` → 5 PASS
- `php.exe artisan test` → full suite with no regressions

### Manual Spot-Check
1. **HR Admin → Leave Balances** → pick an employee previously with BEL → confirm no "Bonus Earn Leave" row, and EL balance reflects the merged total.
2. **Employee/Team Lead → Leave page** → "Bonus Earn Leave" must not appear in the leave type dropdown or balance cards.
3. **Settings → Leave Types** → "Bonus Earn Leave (BEL)" must not be in the list.
4. **Leave Balance Report (PDF/Excel)** → only EL row appears with the correct merged balance.
