# Design Doc: NOC Eid Hybrid Overtime Calculation

## Goal
Implement a unique overtime scenario for NOC employees (Borak & Sylhet) during Eid-adjacent holidays. Even on roster duty days, their shift hours (8 hours) count as fixed "Holiday Duty" overtime, and any additional hours worked are paid at an hourly rate.

## Proposed Changes

### 1. Overtime Calculation Logic (PHP & JS)
The core calculation formula for NOC employees on Eid-adjacent days will be updated:
- **Base Units**: 
    - 3 Units if the date is the "Eid Day".
    - 2 Units if the date is "Eid Adjacent" (Day before/after).
- **Hourly Calculation**:
    - Hours over 8 are calculated at the special rate (e.g., 200.00).
- **Formula**: `(Base Units * FullShiftIncome) + (max(0, TotalHours - 8) * HourlyRate)`.

### 2. Auto-Fill Automation
The "Auto-Fill from Attendance" logic will be enhanced for NOC groups:
- **Shift Bypass**: On Eid days, overtime starts from the actual `in_time` instead of after the 8-hour shift.
- **Auto-check**: The `eid_duty` checkbox will be automatically suggested/checked during auto-fill for these dates.

### 3. UI/UX
- The summary footer in the Overtime dashboard will correctly aggregate these hybrid totals.
- Non-NOC employees will continue using the standard calculation rules.

## Verification Plan
- **Manual Test**: Fill 12 hours for an NOC employee on an Eid day. Verify the amount equals `(3 * PerDay) + (4 * 200)`.
- **Manual Test**: Fill 12 hours for an NOC employee on an Eid-adjacent day. Verify the amount equals `(2 * PerDay) + (4 * 200)`.
- **Auto-Fill Test**: Run auto-fill for an NOC employee on an Eid date and ensure `ot_start` matches `in_time`.
