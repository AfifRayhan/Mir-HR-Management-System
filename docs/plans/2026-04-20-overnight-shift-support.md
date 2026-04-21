# Overnight Shift Support — Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Fully support overnight shifts (e.g. 10 PM – 6 AM) in `AttendanceService` so attendance processing, late calculation, and working-day detection are all correct for roster employees whose shift spans midnight.

**Architecture:** Add an `is_overnight` boolean flag to `roster_times` so the system knows a shift intentionally crosses midnight. The attendance log fetch is widened to capture punch records on the *next* calendar day for overnight workers. Late calculation and working-day detection are verified/corrected with tests.

**Tech Stack:** Laravel 11, PHP 8.x, MySQL, Carbon, PHPUnit.

---

## Background & Key Files

| File | Role |
|---|---|
| `app/Services/AttendanceService.php` | Core: log fetch → status/late calc → DB write |
| `app/Models/RosterTime.php` | Shift config: `start_time`, `end_time`, `is_off_day` |
| `app/Models/RosterSchedule.php` | Per-employee per-date shift assignment |
| `app/Models/AttendanceRecord.php` | Result row written per employee per date |
| `app/Models/Attendance.php` | Raw punch log from biometric machine |
| `database/migrations/2026_04_20_045620_create_roster_times_table.php` | `roster_times` schema |

---

## Task 1 — Add `is_overnight` Flag to `roster_times`

**Files:**
- `[ ]` Create `database/migrations/2026_04_20_add_is_overnight_to_roster_times_table.php`
- `[ ]` Update `app/Models/RosterTime.php`

**Step 1: Write failing test**

Create `tests/Unit/RosterTimeOvernightFlagTest.php`:

```php
<?php
namespace Tests\Unit;

use App\Models\RosterTime;
use PHPUnit\Framework\TestCase;

class RosterTimeOvernightFlagTest extends TestCase
{
    public function test_roster_time_has_is_overnight_in_fillable()
    {
        $model = new RosterTime();
        $this->assertContains('is_overnight', $model->getFillable());
    }

    public function test_is_overnight_casts_to_boolean()
    {
        $model = new RosterTime();
        $this->assertArrayHasKey('is_overnight', $model->getCasts());
    }
}
```

**Step 2: Run to verify it fails**

```bash
php.exe artisan test --filter RosterTimeOvernightFlagTest
```
Expected: FAIL — `is_overnight` not in `$fillable`

**Step 3: Create migration**

```php
// database/migrations/2026_04_20_add_is_overnight_to_roster_times_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('roster_times', function (Blueprint $table) {
            $table->boolean('is_overnight')->default(false)->after('is_off_day');
        });
    }
    public function down(): void {
        Schema::table('roster_times', function (Blueprint $table) {
            $table->dropColumn('is_overnight');
        });
    }
};
```

Run:
```bash
php.exe artisan migrate
```

**Step 4: Update `RosterTime` model**

```php
// app/Models/RosterTime.php
protected $fillable = [
    'group_slug', 'shift_key', 'display_label',
    'start_time', 'end_time', 'badge_class',
    'is_off_day',
    'is_overnight',   // ADD THIS
];

protected $casts = [
    'is_off_day'   => 'boolean',
    'is_overnight' => 'boolean',  // ADD THIS
];
```

**Step 5: Run to verify it passes**

```bash
php.exe artisan test --filter RosterTimeOvernightFlagTest
```
Expected: PASS

**Step 6: Commit**

```bash
git add database/migrations/2026_04_20_add_is_overnight_to_roster_times_table.php \
        app/Models/RosterTime.php \
        tests/Unit/RosterTimeOvernightFlagTest.php
git commit -m "feat: add is_overnight flag to roster_times"
```

---

## Task 2 — Fix Log Fetching to Span Midnight for Overnight Shifts

**Context:** `processEmployeeAttendance()` fetches punches only for `$date`. An overnight worker who punches in at 10 PM on April 20 and out at 6 AM on April 21 will have their punch-out missed (it's logged on April 21 in the `attendances` table). This task widens the fetch when the shift is overnight.

**Files:**
- `[ ]` `app/Services/AttendanceService.php`

**Step 1: Write failing test**

Create `tests/Feature/OvernightLogFetchTest.php`:

```php
<?php
namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\RosterSchedule;
use App\Models\RosterTime;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvernightLogFetchTest extends TestCase
{
    use RefreshDatabase;

    public function test_overnight_shift_fetches_out_time_from_next_day()
    {
        $employee = Employee::factory()->create([
            'employee_code' => 'T001',
            'roster_group'  => 'NOC (Borak)',
        ]);

        RosterTime::create([
            'group_slug'    => 'noc-borak',
            'shift_key'     => 'N',
            'display_label' => 'Night',
            'start_time'    => '22:00:00',
            'end_time'      => '06:00:00',
            'badge_class'   => 'badge-dark',
            'is_off_day'    => false,
            'is_overnight'  => true,
        ]);

        RosterSchedule::create([
            'employee_id' => $employee->id,
            'date'        => '2026-04-20',
            'shift_type'  => 'N',
            'created_by'  => 1,
        ]);

        // Punch in day-1, punch out day-2
        Attendance::create(['user_id' => 'T001', 'punch_time' => '2026-04-20 22:05:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T001', 'punch_time' => '2026-04-21 06:10:00', 'machine_id' => 1]);

        (new AttendanceService())->processEmployeeAttendance($employee, '2026-04-20');

        $record = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', '2026-04-20')->first();

        $this->assertNotNull($record);
        $this->assertEquals('present', $record->status);
        $this->assertNotNull($record->out_time);
        $this->assertEquals('2026-04-21', $record->out_time->toDateString());
        $this->assertEqualsWithDelta(8.08, (float)$record->working_hours, 0.1);
    }
}
```

**Step 2: Run to verify it fails**

```bash
php.exe artisan test --filter OvernightLogFetchTest
```
Expected: FAIL — `out_time` is null

**Step 3: Update log-fetch block in `processEmployeeAttendance()`**

In `AttendanceService.php`, find the block that fetches `$logs` (around line 88) and replace:

```php
// BEFORE
$logs = Attendance::where('user_id', $employee->employee_code)
    ->whereDate('punch_time', $date)
    ->orderBy('punch_time', 'asc')
    ->get();

// AFTER
$isOvernightShift = $rosterShift && $rosterShift->is_overnight;

$logs = Attendance::where('user_id', $employee->employee_code)
    ->where(function ($q) use ($date, $isOvernightShift) {
        $q->whereDate('punch_time', $date);
        if ($isOvernightShift) {
            $nextDay = Carbon::parse($date)->addDay()->toDateString();
            $q->orWhereDate('punch_time', $nextDay);
        }
    })
    ->orderBy('punch_time', 'asc')
    ->get();
```

**Step 4: Run to verify it passes**

```bash
php.exe artisan test --filter OvernightLogFetchTest
```
Expected: PASS

**Step 5: Commit**

```bash
git add app/Services/AttendanceService.php tests/Feature/OvernightLogFetchTest.php
git commit -m "feat: fetch next-day punch logs for overnight roster shifts"
```

---

## Task 3 — Verify Late Calculation for Overnight Shifts

**Context:** Late threshold is `start_time + 1 hour`. For an overnight shift starting at `22:00`, threshold is `23:00`. A punch-in at `23:10` = 10 minutes late (600 seconds). The current code anchors `shiftStart` to `$date`, which is already correct for overnight. This task adds a regression test to prevent future breakage.

**Files:**
- `[ ]` Add test to `tests/Feature/OvernightLogFetchTest.php`

**Step 1: Add test**

```php
public function test_overnight_shift_marks_late_when_punch_in_after_threshold()
{
    $employee = Employee::factory()->create([
        'employee_code' => 'T002',
        'roster_group'  => 'NOC (Borak)',
    ]);

    RosterTime::create([
        'group_slug'    => 'noc-borak',
        'shift_key'     => 'N2',
        'display_label' => 'Night 2',
        'start_time'    => '22:00:00',
        'end_time'      => '06:00:00',
        'badge_class'   => 'badge-dark',
        'is_off_day'    => false,
        'is_overnight'  => true,
    ]);

    RosterSchedule::create([
        'employee_id' => $employee->id,
        'date'        => '2026-04-20',
        'shift_type'  => 'N2',
        'created_by'  => 1,
    ]);

    // 70 minutes late (threshold = 23:00, actual = 23:10)
    Attendance::create(['user_id' => 'T002', 'punch_time' => '2026-04-20 23:10:00', 'machine_id' => 1]);
    Attendance::create(['user_id' => 'T002', 'punch_time' => '2026-04-21 06:00:00', 'machine_id' => 1]);

    (new AttendanceService())->processEmployeeAttendance($employee, '2026-04-20');

    $record = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
        ->where('date', '2026-04-20')->first();

    $this->assertEquals('late', $record->status);
    $this->assertEquals(600, $record->late_seconds); // 10 min = 600 sec
}
```

**Step 2: Run**

```bash
php.exe artisan test --filter OvernightLogFetchTest
```
Expected: All PASS (including the new late test)

**Step 3: Commit**

```bash
git add tests/Feature/OvernightLogFetchTest.php
git commit -m "test: verify late calculation for overnight roster shifts"
```

---

## Task 4 — Verify `isWorkingDay()` for Second Calendar Day of Overnight Shifts

**Context:** `isWorkingDay($employee, '2026-04-21')` must return `false` for a worker whose only shift entry is for `2026-04-20` — even if they physically finish their shift on `2026-04-21`. The absence sweep must not create a spurious "absent" record for April 21.

**Files:**
- `[ ]` Create `tests/Feature/OvernightWorkingDayTest.php`

**Step 1: Write test**

```php
<?php
namespace Tests\Feature;

use App\Models\Employee;
use App\Models\RosterSchedule;
use App\Models\RosterTime;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvernightWorkingDayTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_calendar_day_of_overnight_shift_is_not_an_independent_working_day()
    {
        $employee = Employee::factory()->create([
            'employee_code' => 'T003',
            'roster_group'  => 'NOC (Borak)',
        ]);

        RosterTime::create([
            'group_slug'    => 'noc-borak',
            'shift_key'     => 'N3',
            'display_label' => 'Night 3',
            'start_time'    => '22:00:00',
            'end_time'      => '06:00:00',
            'badge_class'   => 'badge-dark',
            'is_off_day'    => false,
            'is_overnight'  => true,
        ]);

        // Only scheduled for April 20
        RosterSchedule::create([
            'employee_id' => $employee->id,
            'date'        => '2026-04-20',
            'shift_type'  => 'N3',
            'created_by'  => 1,
        ]);

        $service = new AttendanceService();

        $this->assertTrue($service->isWorkingDay($employee, '2026-04-20'));
        $this->assertFalse($service->isWorkingDay($employee, '2026-04-21'));
    }
}
```

**Step 2: Run**

```bash
php.exe artisan test --filter OvernightWorkingDayTest
```
Expected: PASS (existing logic already returns false for no roster entry; this is a regression guard)

If it FAILS: In `isWorkingDay()`, the roster check block already returns `false` when `!$rosterShift`. Verify that `getRosterShiftForDate()` returns `null` when no `RosterSchedule` entry exists for that date. If `null` is returned, no code change is needed.

**Step 3: Commit**

```bash
git add tests/Feature/OvernightWorkingDayTest.php
git commit -m "test: verify overnight shift second-day is not an independent working day"
```

---

## Final Check

```bash
php.exe artisan test --filter Overnight
```
Expected: All PASS

```bash
php.exe artisan test
```
Expected: Full suite PASS

---

## Out of Scope (YAGNI)

- Admin UI toggle for `is_overnight` on shifts (set via seeder/tinker for now)
- Absence sweep for overnight workers (no batch command currently exists)
- General (non-roster) overnight shifts
