# Overtime Floor and Additive Multiplier Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Implement whole-hour rounding for short overtime (Tier 1) and an additive multiplier system for long overtime (Tier 2) to ensure Saturday (Holiday, 8h) results in 1,000 BDT while Friday (Holiday, 14h) remains 2,000 BDT.

**Architecture:** 
- Tier 1 calculation will use `floor($totalHours)` in PHP and `Math.floor(hours)` in JS.
- Tier 2 calculation will use an additive multiplier sum: `Workday=1`, `Holiday=1`, `Eid=2`.
- Total Tier 2 Multiplier = Sum of checked boxes. (e.g., Holiday + Workday = 2).
- UI will automatically check "Workday Duty" for any shift $\ge$ 12 hours to trigger the extra multiplier.

**Tech Stack:** PHP (Laravel), JavaScript (jQuery), Bootstrap 5

---

### Task 1: Update PHP Calculation Logic

**Files:**
- `[ ]` Modify `app/Http/Controllers/Personnel/OvertimeController.php`

**Step 1: Implement floor and additive multiplier in `calculateAmount`**

```php
    private function calculateAmount($employee, $totalHours, $data): float
    {
        // ... (rate resolution remains same) ...

        // Full-shift income (per-day amount) — used when hours > 5
        $fullShiftIncome = ($employee->gross_salary * 0.6) / 30;

        // Determine additive multiplier
        $multiplier = 0;
        if (isset($data['workday_plus_5'])) $multiplier += 1;
        if (isset($data['holiday_plus_5'])) $multiplier += 1;
        if (isset($data['eid_duty']))       $multiplier += 2;

        // Fallback for Tier 1 if no checkboxes are checked (e.g. workday < 5h)
        $tier1Multiplier = max(1, $multiplier);

        // Two-tier formula:
        //   ≤ 5 hours → hourly rate × floor(hours) × tier1Multiplier
        //   > 5 hours → (fullShiftIncome * 2) × multiplier
        if ($totalHours <= 5) {
            return round(floor($totalHours) * $perHourRate * $tier1Multiplier, 2);
        }

        return round(($fullShiftIncome * 2) * $multiplier, 2);
    }
```

**Step 2: Run tests to verify logic (expect some failures due to old test expectations)**

Run: `php.exe artisan test --filter OvertimeTest`

---

### Task 2: Update JavaScript Calculation and Real-Time Behavior

**Files:**
- `[ ]` Modify `resources/views/personnel/overtimes/index.blade.php`

**Step 1: Update JS `calculateAmount` and auto-toggle logic**

```javascript
            function calculateAmount(date) {
                // ... (hour calculation remains same) ...

                const workdayCheck = $(`input[name="ot[${date}][workday_plus_5]"]`);
                const holidayCheck = $(`input[name="ot[${date}][holiday_plus_5]"]`);
                const eidCheck     = $(`input[name="ot[${date}][eid_duty]"]`);

                // Real-time auto-toggle based on hours
                if (hours > 0) {
                    if (isEid) {
                        eidCheck.prop('checked', true);
                        holidayCheck.prop('checked', false);
                    } else if (isOff) {
                        holidayCheck.prop('checked', true);
                        eidCheck.prop('checked', false);
                    }
                    
                    // Toggle Workday Duty (+5 hrs)
                    if (hours >= 12) {
                        workdayCheck.prop('checked', true);
                    } else if (!isOff && !isEid && hours >= 5) {
                        workdayCheck.prop('checked', true);
                    } else if (hours < 5 || (isOff || isEid)) {
                        // If < 5h total, or if it's an off day and < 12h, uncheck workday
                        if (hours < 12) workdayCheck.prop('checked', false);
                    }
                } else {
                    workdayCheck.prop('checked', false);
                    holidayCheck.prop('checked', false);
                    eidCheck.prop('checked', false);
                }

                // ... (multiplier sum logic) ...
                let m = 0;
                if (workdayCheck.is(':checked')) m += 1;
                if (holidayCheck.is(':checked')) m += 1;
                if (eidCheck.is(':checked'))     m += 2;

                const tier1M = Math.max(1, m);
                const amount = (hours <= 5)
                    ? Math.floor(hours) * perHourRate * tier1M
                    : (fullShiftIncome * 2) * m;

                $(`#amount_${date}`).text(amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                updateGrandTotal();
            }
```

---

### Task 3: Update Feature Tests to Match New Logic

**Files:**
- `[ ]` Modify `tests/Feature/OvertimeTest.php`

**Step 1: Update expectations for floor and additive multipliers**

- `test_overtime_calculation_below_5_hours`: Test with 4.5 hours $\rightarrow$ Floor(4) $\rightarrow$ $4 \times 35 \times 1 = 140$.
- `test_overtime_calculation_holiday_above_5_hours`: Update amount from 2400 to 1200 (Multiplier 1).
- Add new test: `test_overtime_calculation_holiday_very_long_shift` (14h) $\rightarrow$ Multiplier 2 $\rightarrow$ 2400.

**Step 2: Run all tests**

Run: `php.exe artisan test --filter OvertimeTest`
Expected: ALL PASS

---

### Task 4: Final Verification

**Step 1: Manual verification in browser**
- Select an employee.
- Enter 1.5 hours on a workday $\rightarrow$ verify amount is 115 (if rate is 115).
- Enter 0.5 hours on a workday $\rightarrow$ verify amount is 0.
- Enter 8.0 hours on a Saturday $\rightarrow$ verify amount is 1000.
- Enter 14.0 hours on a Friday $\rightarrow$ verify amount is 2000.
- Verify checkboxes toggle correctly when switching between 11h and 12h on an off-day.
