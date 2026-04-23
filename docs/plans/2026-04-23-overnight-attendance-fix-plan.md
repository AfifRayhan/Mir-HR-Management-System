# Overnight Shift Attendance Fix Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Prevent overnight shift checkout logs from being incorrectly reused as check-in logs for the next shift by implementing a 4-hour proximity threshold for `in_time`.

**Architecture:** Modify `AttendanceService::processEmployeeAttendance` to filter logs based on their proximity to the scheduled shift start time. Use a 4-hour window for both early and late arrivals.

**Tech Stack:** Laravel, PHP, Carbon, PHPUnit.

---

### Task 1: Create failing test case

**Files:**
- `[ ]` Modify `tests/Feature/OvernightLogFetchTest.php` to add a test case for the threshold logic.

**Step 1: Add the failing test**

```php
    public function test_checkout_of_previous_shift_is_not_reused_as_checkin_of_next_day()
    {
        $user = \App\Models\User::create([
            'name' => 'Admin 4',
            'email' => 'admin4@example.com',
            'password' => bcrypt('password'),
        ]);

        $officeTime = \App\Models\OfficeTime::create([
            'shift_name' => 'Roster',
            'start_time' => '10:00:00',
            'end_time'   => '18:00:00',
        ]);

        $employee = \App\Models\Employee::create([
            'name' => 'Test Employee 4',
            'employee_code' => 'T004',
            'email' => 'test4@example.com',
            'roster_group'  => 'NOC (Borak)',
            'office_time_id' => $officeTime->id,
            'status' => 'active'
        ]);

        RosterTime::create([
            'group_slug'    => 'noc-borak',
            'shift_key'     => 'N4',
            'display_label' => 'Night 4',
            'start_time'    => '22:00:00',
            'end_time'      => '06:00:00',
            'is_overnight'  => true,
        ]);

        // Day 1 (Monday) and Day 2 (Tuesday) both have Night Shift (10 PM - 6 AM)
        RosterSchedule::create(['employee_id' => $employee->id, 'date' => '2026-04-20', 'shift_type' => 'N4']);
        RosterSchedule::create(['employee_id' => $employee->id, 'date' => '2026-04-21', 'shift_type' => 'N4']);

        // Logs:
        // Day 1: 10:05 PM (Check-in Day 1)
        // Day 2: 06:10 AM (Check-out Day 1)
        // Day 2: 10:05 PM (Check-in Day 2)
        // Day 3: 06:10 AM (Check-out Day 2)
        Attendance::create(['user_id' => 'T004', 'punch_time' => '2026-04-20 22:05:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T004', 'punch_time' => '2026-04-21 06:10:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T004', 'punch_time' => '2026-04-21 22:05:00', 'machine_id' => 1]);
        Attendance::create(['user_id' => 'T004', 'punch_time' => '2026-04-22 06:10:00', 'machine_id' => 1]);

        $service = new AttendanceService();

        // Process Day 2
        $service->processEmployeeAttendance($employee, '2026-04-21');

        $record = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', '2026-04-21')->first();

        $this->assertNotNull($record);
        // CURRENT BUG: $record->in_time would be 2026-04-21 06:10:00
        // DESIRED: $record->in_time should be 2026-04-21 22:05:00
        $this->assertEquals('2026-04-21 22:05:00', $record->in_time->toDateTimeString(), 'Check-in for Day 2 should be the 10:05 PM log, not the 6:10 AM log');
    }
```

**Step 2: Run test to verify it fails**

Run: `php.exe artisan test --filter test_checkout_of_previous_shift_is_not_reused_as_checkin_of_next_day`
Expected: FAIL (it will pick 06:10:00 as in_time)

**Step 3: Commit**

```bash
git add tests/Feature/OvernightLogFetchTest.php
git commit -m "test: add failing test for overnight log reuse"
```

---

### Task 2: Implement threshold logic in AttendanceService

**Files:**
- `[ ]` Modify `app/Services/AttendanceService.php`

**Step 1: Modify processEmployeeAttendance**

Replace the log selection logic to find the first log within the 4-hour window.

```php
// app/Services/AttendanceService.php around line 105

            if ($logs->isEmpty()) {
                $inTime = null;
                $outTime = null;
            } else {
                // Find first valid inTime within 4 hours of scheduled start if it's a roster shift
                if ($rosterShift && $rosterShift->start_time) {
                    $scheduledStart = Carbon::parse($date . ' ' . $rosterShift->start_time);
                    
                    $validInLog = $logs->first(function($log) use ($scheduledStart) {
                        return abs(Carbon::parse($log->punch_time)->diffInHours($scheduledStart, false)) <= 4;
                    });
                    
                    if ($validInLog) {
                        $inTime = $validInLog->punch_time;
                        // Out time is the last log after this inTime
                        $outTime = $logs->where('punch_time', '>', $inTime)->last()?->punch_time;
                    } else {
                        $inTime = null;
                        $outTime = null;
                    }
                } else {
                    // Fallback for non-roster or missing start_time
                    $inTime = $logs->first()->punch_time;
                    $outTime = $logs->count() > 1 ? $logs->last()->punch_time : null;
                }
                $machineId = $logs->first()->machine_id;
            }
```

**Step 2: Run test to verify it passes**

Run: `php.exe artisan test --filter test_checkout_of_previous_shift_is_not_reused_as_checkin_of_next_day`
Expected: PASS

**Step 3: Run all overnight tests**

Run: `php.exe artisan test --filter OvernightLogFetchTest`
Expected: ALL PASS

**Step 4: Commit**

```bash
git add app/Services/AttendanceService.php
git commit -m "fix: implement 4-hour threshold for roster check-in to prevent log reuse"
```

---

### Task 3: Cleanup and Final Verification

**Step 1: Final manual check**
Verify the code doesn't break normal roster shifts.

**Step 2: Commit and Finalize**
```bash
git commit -m "docs: finalize overnight shift fix"
```
