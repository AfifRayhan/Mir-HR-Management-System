# Overtime Amount Calculation (Tiered Per-Hour / Per-Day) Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Replace the current checkbox-count-based overtime formula with a two-tier rule: hourly rate for ≤ 5 hours worked, one full-shift payment for > 5 hours, with duty-type multipliers applied on top.

**Architecture:**
The new formula has two tiers driven by `total_ot_hours`:
- **≤ 5 hrs** → `hours × perHourRate × multiplier`
- **> 5 hrs** → `fullShiftIncome × 2 × multiplier`  (where `fullShiftIncome = gross × 0.6 / 30`)

`perHourRate` is resolved from the `overtime_rates` table — `designation_id` match first, `grade_id` as fallback. `multiplier` = 3 (Eid), 2 (Holiday/Dayoff), 1 (Workday/default). Both the PHP `OvertimeController::calculateAmount()` (server-side, used on save) and the JavaScript `calculateAmount()` (client-side, real-time display) must implement this same formula. `perHourRate` and `fullShiftIncome` are passed from the controller to the Blade view as JS variables.

**Tech Stack:** Laravel, PHP, Blade, jQuery, Bootstrap 5

---

### Task 1: Fix PHP Server-Side Calculation in OvertimeController

The `calculateAmount()` private method currently uses a flat `fullShiftIncome`-based formula that ignores hours and counts checkbox ticks. Replace it with the two-tier formula.

**Files:**
- `[ ]` Modify `app/Http/Controllers/Personnel/OvertimeController.php`
- `[ ]` Update `tests/Feature/OvertimeTest.php`

**Step 1: Write the failing tests**

The test setup already has: `gross_salary = 30000`, `rate = 35` (per hour), so:
- `fullShiftIncome = (30000 × 0.6) / 30 = 600`

Updated expected amounts under the new formula:
- 4 hrs, no checkbox → `4 × 35 × 1 = 140` ✅ (already correct, no change)
- 6 hrs, workday checkbox → `600 × 2 × 1 = 1200` (> 5 hrs → 2x day rate)
- 5 hrs, holiday checkbox → `5 × 35 × 2 = 350` (≤ 5 hrs → hourly × multiplier)
- 1 hr, eid checkbox → `1 × 35 × 3 = 105` (≤ 5 hrs → hourly × multiplier)

Replace the four test methods in `tests/Feature/OvertimeTest.php`:

```php
public function test_overtime_calculation_below_5_hours()
{
    // Rate 35/hr. 4 hours. No bonus checkbox. ≤5 hrs → 4 × 35 × 1 = 140.
    $data = [
        'employee_id' => $this->employee->id,
        'month' => '04',
        'year' => '2026',
        'ot' => [
            '2026-04-01' => [
                'start' => '17:00',
                'stop'  => '21:00',
            ]
        ]
    ];
    $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);
    $this->assertDatabaseHas('overtimes', [
        'employee_id'    => $this->employee->id,
        'date'           => '2026-04-01 00:00:00',
        'total_ot_hours' => 4.00,
        'amount'         => 140.00,   // 4 × 35 × 1
    ]);
}

    // 6 hours > 5 → 2x day rate. No holiday/eid. 600 × 2 × 1 = 1200.
    $data = [
        'employee_id' => $this->employee->id,
        'month' => '04',
        'year' => '2026',
        'ot' => [
            '2026-04-02' => [
                'start'          => '09:00',
                'stop'           => '15:00',   // 6 hours
                'workday_plus_5' => 'on',
            ]
        ]
    ];
    $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);
    $this->assertDatabaseHas('overtimes', [
        'employee_id'    => $this->employee->id,
        'date'           => '2026-04-02 00:00:00',
        'total_ot_hours' => 6.00,
        'amount'         => 1200.00,   // fullShiftIncome × 2 × 1
    ]);
}

public function test_overtime_calculation_holiday_5_hours_or_less()
{
    // Rate 35/hr. 5 hours. ≤5 hrs → hourly. Holiday multiplier × 2. 5 × 35 × 2 = 350.
    $data = [
        'employee_id' => $this->employee->id,
        'month' => '04',
        'year' => '2026',
        'ot' => [
            '2026-04-03' => [
                'start'          => '09:00',
                'stop'           => '14:00',   // exactly 5 hours
                'holiday_plus_5' => 'on',
            ]
        ]
    ];
    $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);
    $this->assertDatabaseHas('overtimes', [
        'employee_id' => $this->employee->id,
        'date'        => '2026-04-03 00:00:00',
        'amount'      => 350.00,   // 5 × 35 × 2
    ]);
}

public function test_overtime_calculation_holiday_above_5_hours()
{
    // Gross 30000. fullShiftIncome = 600. 6 hours > 5 → 2x day rate. Holiday × 2. 600 × 2 × 2 = 2400.
    $data = [
        'employee_id' => $this->employee->id,
        'month' => '04',
        'year' => '2026',
        'ot' => [
            '2026-04-05' => [
                'start'          => '09:00',
                'stop'           => '15:00',   // 6 hours
                'holiday_plus_5' => 'on',
            ]
        ]
    ];
    $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);
    $this->assertDatabaseHas('overtimes', [
        'employee_id' => $this->employee->id,
        'date'        => '2026-04-05 00:00:00',
        'amount'      => 2400.00,   // fullShiftIncome × 2 × 2
    ]);
}

public function test_overtime_calculation_eid_duty()
{
    // Rate 35/hr. 1 hour. ≤5 hrs → hourly. Eid multiplier × 3. 1 × 35 × 3 = 105.
    $data = [
        'employee_id' => $this->employee->id,
        'month' => '04',
        'year' => '2026',
        'ot' => [
            '2026-04-04' => [
                'start'    => '09:00',
                'stop'     => '10:00',
                'eid_duty' => 'on',
            ]
        ]
    ];
    $response = $this->actingAs($this->admin)->post(route('overtimes.save'), $data);
    $response->assertSessionHasNoErrors();
    $response->assertStatus(302);
    $this->assertDatabaseHas('overtimes', [
        'employee_id' => $this->employee->id,
        'date'        => '2026-04-04 00:00:00',
        'amount'      => 105.00,   // 1 × 35 × 3
    ]);
}
```

**Step 2: Run tests to verify they fail**

```bash
php.exe artisan test --filter OvertimeTest
```

Expected: Several tests FAIL with wrong `amount` values (old formula produces different numbers).

**Step 3: Update the PHP `calculateAmount()` method in OvertimeController**

Replace the private `calculateAmount()` method (lines 358–380) with:

```php
private function calculateAmount($employee, $totalHours, $data): float
{
    // Resolve per-hour rate: designation rate takes priority over grade rate.
    $perHourRate = 0.0;

    if ($employee->designation_id) {
        $designationRate = \App\Models\OvertimeRate::where('designation_id', $employee->designation_id)
            ->whereNull('grade_id')
            ->value('rate');
        if ($designationRate !== null) {
            $perHourRate = (float) $designationRate;
        }
    }

    if ($perHourRate === 0.0 && $employee->grade_id) {
        $gradeRate = \App\Models\OvertimeRate::where('grade_id', $employee->grade_id)
            ->whereNull('designation_id')
            ->value('rate');
        if ($gradeRate !== null) {
            $perHourRate = (float) $gradeRate;
        }
    }

    // Full-shift income (per-day amount) — used when hours > 5
    $fullShiftIncome = ($employee->gross_salary * 0.6) / 30;

    // Determine duty-type multiplier
    $multiplier = 1;
    if (isset($data['eid_duty'])) {
        $multiplier = 3;
    } elseif (isset($data['holiday_plus_5'])) {
        $multiplier = 2;
    }

    // Two-tier formula:
    //   ≤ 5 hours → hourly rate
    //   > 5 hours → one full shift payment
    if ($totalHours <= 5) {
        return round($totalHours * $perHourRate * $multiplier, 2);
    }

    return round($fullShiftIncome * 2 * $multiplier, 2);
}
```

**Step 4: Run tests to verify they pass**

```bash
php.exe artisan test --filter OvertimeTest
```

Expected: All 5 tests PASS.

**Step 5: Commit**

```bash
git add app/Http/Controllers/Personnel/OvertimeController.php tests/Feature/OvertimeTest.php
git commit -m "feat: two-tier OT amount — hourly (≤5h) vs full-shift (>5h) with duty multiplier"
```

---

### Task 2: Pass Rate Variables to Blade View for Real-Time JS Calculation

The JS `calculateAmount()` function must mirror the PHP formula exactly. It needs both `perHourRate` and `fullShiftIncome` exposed as JS variables from the controller.

**Files:**
- `[ ]` Modify `app/Http/Controllers/Personnel/OvertimeController.php` — add `$perHourRate` to view compact
- `[ ]` Modify `resources/views/personnel/overtimes/index.blade.php` — expose variables + rewrite JS formula

**Step 1: Write the failing test**

```php
// Add to tests/Feature/OvertimeTest.php
public function test_overtime_index_exposes_per_hour_rate_to_view()
{
    $response = $this->actingAs($this->admin)
        ->get(route('overtimes.index', [
            'employee_id' => $this->employee->id,
            'month'       => '04',
            'year'        => '2026',
        ]));

    $response->assertStatus(200);
    // The JS variable should be present with the grade rate = 35
    $response->assertSee('perHourRate = 35', false);
}
```

**Step 2: Run test to verify it fails**

```bash
php.exe artisan test --filter test_overtime_index_exposes_per_hour_rate_to_view
```

Expected: FAIL — response does not contain `perHourRate = 35`.

**Step 3: Add `$perHourRate` lookup in `OvertimeController::index()`**

Add this block inside the `if ($employeeId)` block, just before the closing `}` of that block (around line 153), right before `return view(...)`:

```php
// Resolve per-hour rate for JS real-time display
$perHourRate = 0.0;
if ($selectedEmployee) {
    if ($selectedEmployee->designation_id) {
        $designationRate = \App\Models\OvertimeRate::where('designation_id', $selectedEmployee->designation_id)
            ->whereNull('grade_id')
            ->value('rate');
        if ($designationRate !== null) {
            $perHourRate = (float) $designationRate;
        }
    }
    if ($perHourRate === 0.0 && $selectedEmployee->grade_id) {
        $gradeRate = \App\Models\OvertimeRate::where('grade_id', $selectedEmployee->grade_id)
            ->whereNull('designation_id')
            ->value('rate');
        if ($gradeRate !== null) {
            $perHourRate = (float) $gradeRate;
        }
    }
}
```

Update the `return view()` call to include `$perHourRate`:

```php
return view('personnel.overtimes.index', compact(
    'employees',
    'employeeId',
    'month',
    'year',
    'selectedEmployee',
    'daysInMonth',
    'overtimeRecords',
    'rosterSchedules',
    'weeklyHolidays',
    'holidays',
    'canEdit',
    'isTeamLeadLayout',
    'perHourRate'        // <-- new
));
```

**Step 4: Update the Blade JS to use the two-tier formula**

In `resources/views/personnel/overtimes/index.blade.php`, inside the `@push('scripts')` `<script>` block:

**Replace** the three variable declarations (lines ~244–246):

```javascript
// OLD — remove:
const gross = parseFloat(otForm.dataset.gross) || 0;
const grade = otForm.dataset.grade || '';
const fullShiftIncome = (gross * 0.6) / 30;
```

**With:**

```javascript
const perHourRate    = {{ $perHourRate ?? 0 }};
const gross          = parseFloat(otForm.dataset.gross) || 0;
const fullShiftIncome = (gross * 0.6) / 30;
```

> `fullShiftIncome` remains because the JS still needs it for the > 5-hour tier. `gross` is already on the form's `data-gross` attribute so no server change needed for it.

**Replace** the amount-calculation block (from `let multiplier = 1;` through the `.text()` call):

```javascript
// OLD block — remove everything from 'let multiplier' to the .text() call:
let multiplier = 1;
if (eidDuty) multiplier = 3;
else if (holidayPlus5) multiplier = 2;

const baseValue = fullShiftIncome * multiplier;

let count = 0;
if (eidDuty) count++;
if (holidayPlus5) count++;
if (workdayPlus5) count++;

const amount = baseValue * count;

$(`#amount_${date}`).text(amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
```

**With:**

```javascript
// NEW two-tier formula — mirrors PHP calculateAmount() exactly:
let multiplier = 1;
if (eidDuty)           multiplier = 3;
else if (holidayPlus5) multiplier = 2;

const amount = (hours <= 5)
    ? hours * perHourRate * multiplier          // ≤ 5 hrs: per-hour rate
    : fullShiftIncome * 2 * multiplier;             // > 5 hrs: double full shift

$(`#amount_${date}`).text(amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
```

**Step 5: Run test to verify it passes**

```bash
php.exe artisan test --filter test_overtime_index_exposes_per_hour_rate_to_view
```

Expected: PASS.

**Step 6: Run full test suite to confirm no regressions**

```bash
php.exe artisan test --filter OvertimeTest
```

Expected: All 6 tests PASS.

**Step 7: Commit**

```bash
git add app/Http/Controllers/Personnel/OvertimeController.php resources/views/personnel/overtimes/index.blade.php tests/Feature/OvertimeTest.php
git commit -m "feat: real-time JS mirrors two-tier OT formula (hourly ≤5h, full-shift >5h)"
```

---

### Task 3: Manual Verification in Browser

**Step 1: Start the dev server (if not already running)**

```bash
php.exe artisan serve
```

Visit `http://127.0.0.1:8081/overtimes`.

**Step 2: Verify real-time calculation with a Management employee (rate = 115/hr, gross ~25,000)**

`fullShiftIncome` for gross 25,000 = `(25000 × 0.6) / 30 = 500`

| Start | Stop | Hours | Checkbox | Expected Amount |
|---|---|---|---|---|
| 17:00 | 18:00 | 1.00 | none | 115.00 (1 × 115 × 1) |
| 17:00 | 19:00 | 2.00 | none | 230.00 (2 × 115 × 1) |
| 17:00 | 22:00 | 5.00 | none | 575.00 (5 × 115 × 1) |
| 17:00 | 23:00 | 6.00 | none | 1000.00 (fullShift × 2 × 1) |
| 17:00 | 19:00 | 2.00 | Holiday ✓ | 460.00 (2 × 115 × 2) |
| 17:00 | 23:00 | 6.00 | Holiday ✓ | 2000.00 (fullShift × 2 × 2) |
| 17:00 | 18:00 | 1.00 | Eid ✓ | 345.00 (1 × 115 × 3) |
| 17:00 | 23:00 | 6.00 | Eid ✓ | 3000.00 (fullShift × 2 × 3) |

Confirm the Amount column updates instantly without saving.

**Step 3: Save and confirm DB values match**

Click "Save Overtime Records" and confirm the saved `amount` in DB matches what the UI showed.
