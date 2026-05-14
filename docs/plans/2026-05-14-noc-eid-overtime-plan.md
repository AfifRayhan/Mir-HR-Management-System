# NOC Eid Hybrid Overtime Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Implement a hybrid overtime calculation for NOC employees where the first 8 hours on Eid earn fixed units, and hours beyond that are paid hourly.

**Architecture:** Update the calculation engine in both PHP (for saving) and JS (for UI) to check for NOC groups and apply a combined formula. Enhance the Auto-Fill logic to treat Eid shifts as 100% overtime for NOC.

**Tech Stack:** Laravel, PHP, jQuery, Blade

---

### Task 1: Update Backend Calculation Logic

**Files:**
- `[ ]` Modify `app/Http/Controllers/Personnel/OvertimeController.php`

**Step 1: Implement Hybrid Formula**
Update the `calculateAmount` method to handle NOC groups specifically.

```php
// app/Http/Controllers/Personnel/OvertimeController.php

private function calculateAmount(Employee $employee, float $totalHours, array $data, bool $isEidAdjacent = false): float
{
    $perHourRate = $this->getEmployeePerHourRate($employee);
    $isNocGroup = in_array($employee->roster_group, ['noc-borak', 'noc-sylhet']);
    
    if ($isEidAdjacent && $employee->roster_group) {
        $specialRate = (float) OvertimeSpecialRate::where('roster_group', $employee->roster_group)
            ->where('is_eid_special', true)
            ->value('rate');
        if ($specialRate > 0) {
            $perHourRate = $specialRate;
        }
    }

    $fullShiftIncome = ($employee->gross_salary * 0.6) / 30;

    if (isset($data['eid_duty']) && $isNocGroup && $isEidAdjacent) {
        // HYBRID LOGIC for NOC on Eid
        $isActualEidDay = ($isEidAdjacent && $this->checkIfActualEidDay($data['date'] ?? null)); // Helper needed or derived
        $units = $isActualEidDay ? 3 : 2;
        
        $baseAmount = $units * $fullShiftIncome;
        $extraHours = max(0, $totalHours - 8);
        $extraAmount = floor($extraHours) * $perHourRate; // Using floor as per existing logic
        
        return round($baseAmount + $extraAmount, 2);
    }

    // ... existing logic for standard duties ...
}
```

**Step 2: Commit**
`git commit -m "feat(ot): implement hybrid calculation logic for NOC on Eid"`

---

### Task 2: Update Frontend Real-time Calculation

**Files:**
- `[ ]` Modify `resources/views/personnel/overtimes/index.blade.php`

**Step 1: Sync JS Calculation**
Update the `calculateAmount` JS function to match the backend logic.

**Step 2: Commit**
`git commit -m "feat(ot): update JS calculation for NOC hybrid overtime"`

---

### Task 3: Enhance Auto-Fill for NOC

**Files:**
- `[ ]` Modify `app/Http/Controllers/Personnel/OvertimeController.php`

**Step 1: Update Auto-Fill suggestions**
For NOC on Eid days, set `ot_start` to `in_time` and mark `eid_duty` as true.

**Step 2: Commit**
`git commit -m "feat(ot): automate Eid duty suggestions for NOC in auto-fill"`

---

### Task 4: Verification

**Step 1: Manual Verification**
1. Select an NOC employee.
2. Enter 12 hours on an Eid day.
3. Check "Eid Duty".
4. Verify Amount = (3 units) + (4 hours * 200).
