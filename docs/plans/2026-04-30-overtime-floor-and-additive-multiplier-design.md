# Overtime Floor Calculation and Additive Multiplier Logic

This document outlines the design for refining the overtime calculation logic to support whole-hour rounding for short shifts and an additive multiplier system for long shifts.

## Goals
- Round down overtime hours to the nearest whole hour for the hourly rate calculation (Tier 1).
- Implement an additive multiplier system for shifts exceeding 5 hours (Tier 2).
- Ensure the UI checkboxes and amounts update in real-time based on the hours entered.

## Calculation Logic

### Tier 1: Hourly Rate Calculation ( $\le$ 5 hours)
The amount is calculated by multiplying the floored hours by the hourly rate and the total multiplier.
**Formula:** `floor(total_hours) * hourly_rate * total_multiplier`

### Tier 2: Full Shift Calculation ( $>5$ hours)
The amount is a fixed daily rate multiplied by the sum of multipliers from the checked duty types.
**Formula:** `(full_shift_income * 2) * total_multiplier`

### Multiplier Weights
- **Workday Duty (+5 hrs)**: 1
- **Dayoff/Holiday**: 1
- **Eid Special Duty**: 2

The `total_multiplier` is the sum of the weights of all checked boxes. If no boxes are checked (e.g., Tier 1 workday), the base multiplier is 1.

## Real-Time UI Behavior
- **Workday**: If hours $\ge$ 5, check "Workday Duty".
- **Off-Day**:
    - If hours $>0$, check "Dayoff/Holiday".
    - If hours $\ge$ 12, also check "Workday Duty".
- **Eid-Day**:
    - If hours $>0$, check "Eid Special Duty".
    - If hours $\ge$ 12, also check "Workday Duty".

## Components Affected

### Backend: `OvertimeController.php`
- Update `calculateAmount()` to implement the floored hours and additive multiplier logic.
- Ensure the server-side calculation matches the client-side logic exactly.

### Frontend: `index.blade.php`
- Update the JavaScript `calculateAmount()` function to mirror the new logic.
- Update the checkbox auto-toggling logic to support the additive behavior (specifically checking "Workday Duty" for long off-day shifts).
- Ensure the UI updates immediately when hours or checkboxes are modified.
