# Overtime Auto-Fill from Attendance Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Add an "Auto-Fill from Attendance" button to `/overtimes` that calculates and pre-populates OT start/stop times for each day of the selected month by comparing the employee's actual `out_time` against when they should have finished their full scheduled shift.

**Architecture:** A new GET AJAX endpoint `overtimes/auto-fill` on `OvertimeController` fetches `attendance_records` + the employee's shift definition (`office_times` or `roster_times`), computes per-day OT windows, and returns JSON. The frontend button calls this endpoint and populates existing form inputs — then triggers the existing `calculateAmount()` JS function so checkboxes and amounts update automatically. Only days without a saved OT record are auto-filled (non-destructive).

**Tech Stack:** Laravel 11, PHP, Bootstrap 5, Flatpickr (already loaded), jQuery (already loaded), Carbon.

---

## OT Calculation Rule (Critical — Read This First)

| Value | Source |
|---|---|
| `shift_duration_hours` | `office_times.end_time − office_times.start_time` in decimal hours. Roster employees: use `roster_times` equivalents. **Ignore lunch.** |
| `ot_start` | `in_time + shift_duration_hours` — late employees must complete their full shift before OT starts |
| `ot_stop` | `out_time` from `attendance_records` |
| OT exists? | Only when `out_time > ot_start` |

**Example — Late employee:**
- Shift: 09:00–17:00 = 8 hrs | In: 09:30 | Out: 19:45
- `ot_start` = 09:30 + 8h = **17:30**, `ot_stop` = **19:45**, OT = 2.25 hrs ✓

**Example — No OT:**
- Late (in 09:30), left at 17:10 → `ot_start` = 17:30 → `out_time (17:10) < 17:30` → **no OT**

---

## Task 1: Add Route

**Files:**
- `[ ]` Modify `routes/web.php`

### Step 1: Add route

In the existing overtime routes block (around line 165), add **before** the existing `overtimes/save` route:

```php
Route::get('overtimes/auto-fill', [OvertimeController::class, 'autoFill'])->name('overtimes.auto-fill');
```

### Step 2: Verify

```bash
php.exe artisan route:list --name=overtimes
```

Expected: a row with `overtimes/auto-fill` and name `overtimes.auto-fill`.

### Step 3: Commit

```bash
git add routes/web.php
git commit -m "feat: register overtimes auto-fill route"
```

---

## Task 2: Write Feature Tests

**Files:**
- `[ ]` Create `tests/Feature/OvertimeAutoFillTest.php`

### Step 1: Write the failing tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\AttendanceRecord;
use App\Models\OfficeTime;
use App\Models\Designation;
use App\Models\Overtime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OvertimeAutoFillTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsHrAdmin(): self
    {
        $role = Role::factory()->create(['name' => 'hr_admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        return $this->actingAs($user);
    }

    public function test_auto_fill_returns_late_arrival_ot_start(): void
    {
        $officeTime = OfficeTime::create([
            'shift_name' => 'General',
            'short_name' => 'GEN',
            'start_time' => '09:00:00',
            'end_time'   => '17:00:00',  // 8-hour shift
        ]);

        $designation = Designation::create(['name' => 'Engineer', 'is_ot_eligible' => true]);

        $employee = Employee::factory()->create([
            'status'         => 'active',
            'office_time_id' => $officeTime->id,
            'designation_id' => $designation->id,
            'gross_salary'   => 30000,
        ]);

        // Late arrival: in 09:30, out 20:00 → ot_start must be 17:30, not 17:00
        AttendanceRecord::create([
            'employee_id'   => $employee->id,
            'date'          => '2026-04-15',
            'in_time'       => '2026-04-15 09:30:00',
            'out_time'      => '2026-04-15 20:00:00',
            'working_hours' => 10.5,
            'status'        => 'late',
        ]);

        $response = $this->actingAsHrAdmin()
            ->getJson(route('overtimes.auto-fill', [
                'employee_id' => $employee->id,
                'month'       => '04',
                'year'        => '2026',
            ]));

        $response->assertOk()->assertJsonStructure(['suggestions']);

        $day = $response->json('suggestions.2026-04-15');
        $this->assertNotNull($day);
        $this->assertEquals('17:30', $day['ot_start']); // 09:30 + 8h
        $this->assertEquals('20:00', $day['ot_stop']);
        $this->assertEquals(2.5, $day['total_ot_hours']);
    }

    public function test_auto_fill_skips_days_without_overtime(): void
    {
        $officeTime = OfficeTime::create([
            'shift_name' => 'General', 'short_name' => 'GEN',
            'start_time' => '09:00:00', 'end_time'   => '17:00:00',
        ]);
        $designation = Designation::create(['name' => 'Engineer', 'is_ot_eligible' => true]);
        $employee = Employee::factory()->create([
            'status' => 'active', 'office_time_id' => $officeTime->id, 'designation_id' => $designation->id,
        ]);

        // In 09:30, out 17:10 → ot_start would be 17:30 → no OT (left early)
        AttendanceRecord::create([
            'employee_id' => $employee->id, 'date' => '2026-04-10',
            'in_time' => '2026-04-10 09:30:00', 'out_time' => '2026-04-10 17:10:00',
            'working_hours' => 7.67, 'status' => 'late',
        ]);

        $response = $this->actingAsHrAdmin()
            ->getJson(route('overtimes.auto-fill', [
                'employee_id' => $employee->id, 'month' => '04', 'year' => '2026',
            ]));

        $response->assertOk();
        $this->assertArrayNotHasKey('2026-04-10', $response->json('suggestions'));
    }

    public function test_auto_fill_skips_days_with_existing_ot_record(): void
    {
        $officeTime = OfficeTime::create([
            'shift_name' => 'General', 'short_name' => 'GEN',
            'start_time' => '09:00:00', 'end_time'   => '17:00:00',
        ]);
        $designation = Designation::create(['name' => 'Engineer', 'is_ot_eligible' => true]);
        $employee = Employee::factory()->create([
            'status' => 'active', 'office_time_id' => $officeTime->id, 'designation_id' => $designation->id,
        ]);

        AttendanceRecord::create([
            'employee_id' => $employee->id, 'date' => '2026-04-05',
            'in_time' => '2026-04-05 09:00:00', 'out_time' => '2026-04-05 20:00:00',
            'working_hours' => 11, 'status' => 'present',
        ]);

        Overtime::create([
            'employee_id' => $employee->id, 'date' => '2026-04-05',
            'ot_start' => '18:00', 'ot_stop' => '20:00',
            'total_ot_hours' => 2, 'created_by' => 1,
        ]);

        $response = $this->actingAsHrAdmin()
            ->getJson(route('overtimes.auto-fill', [
                'employee_id' => $employee->id, 'month' => '04', 'year' => '2026',
            ]));

        $response->assertOk();
        $this->assertArrayNotHasKey('2026-04-05', $response->json('suggestions'));
    }
}
```

### Step 2: Run to verify they fail

```bash
php.exe artisan test --filter OvertimeAutoFillTest
```

Expected: All 3 **FAIL** (404 or "Route not defined").

### Step 3: Commit

```bash
git add tests/Feature/OvertimeAutoFillTest.php
git commit -m "test: add failing tests for overtime auto-fill endpoint"
```

---

## Task 3: Implement `autoFill()` in OvertimeController

**Files:**
- `[ ]` Modify `app/Http/Controllers/Personnel/OvertimeController.php`

### Step 1: Add two methods after `save()` and before `calculateTotalHours()`

```php
/**
 * Return attendance-based OT suggestions for a given employee/month.
 *
 * Only returns days that:
 *   - Have an attendance record with both in_time and out_time
 *   - Do NOT already have a saved Overtime record (non-destructive)
 *   - Have out_time > (in_time + shift_duration) — genuine OT
 *
 * OT rule (late employees complete their full shift first):
 *   ot_start = in_time + shift_duration_hours
 *   ot_stop  = out_time
 */
public function autoFill(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'month'       => 'required|string',
        'year'        => 'required|integer',
    ]);

    $employeeId = $request->employee_id;
    $month      = $request->month;
    $year       = $request->year;

    $employee = Employee::with(['officeTime'])->findOrFail($employeeId);

    // Fetch attendance records for this month (need both in and out times)
    $attendanceRecords = \App\Models\AttendanceRecord::where('employee_id', $employeeId)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->whereNotNull('in_time')
        ->whereNotNull('out_time')
        ->get()
        ->keyBy(fn($r) => $r->date->format('Y-m-d'));

    // Dates that already have saved OT records — skip these
    $existingOt = Overtime::where('employee_id', $employeeId)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->pluck('date')
        ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
        ->flip(); // use as a lookup set

    $suggestions = [];

    foreach ($attendanceRecords as $dateStr => $record) {
        if (isset($existingOt[$dateStr])) {
            continue;
        }

        $shiftHours = $this->resolveShiftDuration($employee, $dateStr);
        if ($shiftHours === null || $shiftHours <= 0) {
            continue;
        }

        $inTime  = Carbon::parse($record->in_time);
        $outTime = Carbon::parse($record->out_time);

        // OT only begins once the employee's full shift duration is completed
        $otStart = $inTime->copy()->addHours($shiftHours);

        if (!$outTime->greaterThan($otStart)) {
            continue;
        }

        $suggestions[$dateStr] = [
            'ot_start'       => $otStart->format('H:i'),
            'ot_stop'        => $outTime->format('H:i'),
            'total_ot_hours' => round($otStart->diffInMinutes($outTime) / 60, 2),
        ];
    }

    return response()->json(['suggestions' => $suggestions]);
}

/**
 * Resolve the scheduled shift duration (decimal hours) for an employee on a given date.
 * Returns null if no shift definition can be found.
 * Lunch breaks are intentionally ignored per business rule.
 */
private function resolveShiftDuration(Employee $employee, string $date): ?float
{
    $officeTime = $employee->officeTime;

    if (!$officeTime) {
        return null;
    }

    // Roster employee: get shift from RosterTime via AttendanceService
    if ($officeTime->shift_name === 'Roster') {
        $attendanceService = app(\App\Services\AttendanceService::class);
        $rosterShift = $attendanceService->getRosterShiftForDate($employee, $date);

        if (!$rosterShift || $rosterShift->is_off_day || !$rosterShift->start_time || !$rosterShift->end_time) {
            return null;
        }

        $start = Carbon::parse($date . ' ' . $rosterShift->start_time);
        $end   = Carbon::parse($date . ' ' . $rosterShift->end_time);
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay(); // overnight shift
        }

        return round($start->diffInMinutes($end) / 60, 2);
    }

    // Standard employee: use office_times start/end
    if (!$officeTime->start_time || !$officeTime->end_time) {
        return null;
    }

    $start = Carbon::parse($date . ' ' . $officeTime->start_time);
    $end   = Carbon::parse($date . ' ' . $officeTime->end_time);
    if ($end->lessThanOrEqualTo($start)) {
        $end->addDay();
    }

    return round($start->diffInMinutes($end) / 60, 2);
}
```

### Step 2: Run the tests

```bash
php.exe artisan test --filter OvertimeAutoFillTest
```

Expected: All 3 **PASS**.

### Step 3: Commit

```bash
git add app/Http/Controllers/Personnel/OvertimeController.php
git commit -m "feat: implement overtime auto-fill endpoint with late-arrival shift-completion logic"
```

---

## Task 4: Add Auto-Fill Button & AJAX to Blade View

**Files:**
- `[ ]` Modify `resources/views/personnel/overtimes/index.blade.php`

### Step 1: Add the button

Find the employee info header `div` (the `d-flex justify-content-between align-items-center mb-4` div, around line 72). Add the Auto-Fill button as a **middle child** between the employee info block and the "Total Payable" block:

```html
{{-- Auto-Fill button --}}
<div class="text-center">
    <button type="button" id="btn-auto-fill" class="btn btn-outline-primary btn-sm px-3"
            data-url="{{ route('overtimes.auto-fill') }}"
            data-employee="{{ $selectedEmployee->id }}"
            data-month="{{ $month }}"
            data-year="{{ $year }}">
        <i class="bi bi-magic me-1"></i> Auto-Fill from Attendance
    </button>
    <div class="text-xs text-muted mt-1">Fills empty rows only · won't overwrite saved data</div>
</div>
```

### Step 2: Add the AJAX handler

Append inside the existing `@push('scripts')` block, immediately **after** the closing `});` of `$(document).ready(...)`:

```javascript
// ── Auto-Fill from Attendance ──────────────────────────────────────────────
$('#btn-auto-fill').on('click', function () {
    const btn = $(this);
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Loading...');

    $.getJSON(btn.data('url'), {
        employee_id: btn.data('employee'),
        month:       btn.data('month'),
        year:        btn.data('year'),
    })
    .done(function (data) {
        const suggestions = data.suggestions || {};
        let filled = 0;

        $.each(suggestions, function (date, info) {
            const startInput = $(`input[name="ot[${date}][start]"]`);
            const stopInput  = $(`input[name="ot[${date}][stop]"]`);

            // Non-destructive: only fill if both inputs are currently empty
            if (startInput.length && !startInput.val() && !stopInput.val()) {
                if (startInput[0]._flatpickr) {
                    startInput[0]._flatpickr.setDate(info.ot_start, true, 'H:i');
                } else {
                    startInput.val(info.ot_start);
                }
                if (stopInput[0]._flatpickr) {
                    stopInput[0]._flatpickr.setDate(info.ot_stop, true, 'H:i');
                } else {
                    stopInput.val(info.ot_stop);
                }

                // Trigger existing calculateAmount() for this date via the change event
                startInput.trigger('change');
                filled++;
            }
        });

        if (filled === 0) {
            alert('No new OT entries found. All rows are already filled or attendance shows no overtime.');
        } else {
            alert(`Auto-filled ${filled} day(s). Review then click "Save Overtime Records" to confirm.`);
        }
    })
    .fail(function () {
        alert('Failed to load attendance data. Please try again.');
    })
    .always(function () {
        btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Auto-Fill from Attendance');
    });
});
```

### Step 3: Run all OT tests

```bash
php.exe artisan test --filter OvertimeAutoFillTest
```

Expected: All 3 **PASS**.

### Step 4: Manual smoke test

1. `php.exe artisan serve`
2. Go to `http://127.0.0.1:8000/overtimes`
3. Select employee + month/year → **Load Form**
4. Click **Auto-Fill from Attendance**
5. Verify:
   - Late-arrival days: `ot_start` = `in_time + shift_duration` (not the fixed `end_time`)
   - Days with no overtime in attendance: remain empty
   - Days with existing saved OT: untouched
   - Checkboxes and Amount column auto-update
6. **Save Overtime Records** → reload → confirm data persisted

### Step 5: Commit

```bash
git add resources/views/personnel/overtimes/index.blade.php
git commit -m "feat: add auto-fill from attendance button and AJAX handler to overtime page"
```

---

## Task 5: Verify `is_ot_eligible` Column on `designations`

> The controller's employee query uses `whereHas('designation', fn($q) => $q->where('is_ot_eligible', true))`.
> This column must exist for tests and the page to work.

### Step 1: Check if the column exists

```bash
php.exe artisan tinker --execute="echo Schema::hasColumn('designations', 'is_ot_eligible') ? 'EXISTS' : 'MISSING';"
```

If output is `MISSING`, create a migration:

```bash
php.exe artisan make:migration add_is_ot_eligible_to_designations_table --table=designations
```

```php
public function up(): void
{
    Schema::table('designations', function (Blueprint $table) {
        $table->boolean('is_ot_eligible')->default(false)->after('priority');
    });
}

public function down(): void
{
    Schema::table('designations', function (Blueprint $table) {
        $table->dropColumn('is_ot_eligible');
    });
}
```

```bash
php.exe artisan migrate
```

### Step 2: Run full test suite

```bash
php.exe artisan test
```

Expected: All pass.

### Step 3: Commit (only if migration was needed)

```bash
git add database/migrations/
git commit -m "feat: add is_ot_eligible to designations"
```

---

## Files Changed Summary

| File | Change |
|---|---|
| `routes/web.php` | +1 GET route `overtimes/auto-fill` |
| `app/Http/Controllers/Personnel/OvertimeController.php` | +2 methods: `autoFill()`, `resolveShiftDuration()` |
| `resources/views/personnel/overtimes/index.blade.php` | +1 button + AJAX block in `@push('scripts')` |
| `tests/Feature/OvertimeAutoFillTest.php` | New — 3 feature tests |
| `database/migrations/*` | Only if `is_ot_eligible` column missing |
