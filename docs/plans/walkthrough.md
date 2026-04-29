# Walkthrough - Overtime (OT) Management System

I have successfully implemented the monthly Overtime (OT) Management System. This system allows HR administrators to record and calculate overtime for employees based on their grade and specific payment rules.

## Changes Made

### 1. Database and Models
- **Migration**: Created the `overtimes` table to store daily overtime records, including start/stop times, total hours, and special duty flags (Workday > 5, Holiday > 5, Eid Duty).
- **Model**: Created the `Overtime` model with relationships to `Employee` and `User` (creator).
- **Employee Model**: Added the `overtimes` relationship to the `Employee` model.

### 2. Backend Logic
- **Controller**: Implemented `OvertimeController` with calculation logic:
    - **Below 5 hours**: Grade-based hourly rates (Management: 115, Technician: 35, Driver: 30, Peon/Cleaner: 25).
    - **Workday Duty (>= 5 hrs)**: 1 full shift payment `(60% of gross / 30)`.
    - **Holiday Duty (> 5 hrs)**: 2 full shift payments.
    - **Eid Special Duty**: 3 full shift payments.

### 3. Overtime Settings
- **Subsection**: Created a new **Settings** page under the Overtime section.
- **Dynamic Rates**: HR admins can now configure hourly rates for each Grade.
- **Designation Eligibility**: Added a toggle system to specify which designations are eligible for overtime.
- **Implementation**:
    - Created `overtime_rates` table and `OvertimeRate` model.
    - Added `is_ot_eligible` column to the `designations` table.
    - Updated `OvertimeController` to use these dynamic settings instead of hardcoded values.

### 4. User Interface
- **Routes**: Added `overtimes.settings` and `overtimes.settings.save` routes.
- **Menu**: Added a **Settings** link in the sidebar under the Overtime parent.
- **Monthly View**: The employee dropdown now only shows OT-eligible employees based on their designation.
- **Calculation**: Payment amounts are now calculated using the dynamic rates defined in Settings.

## Verification Results

### Automated Tests
Ran `Tests\Feature\OvertimeTest` which covers:
- Index page accessibility.
- Calculation for hours below 5 (Grade-based).
- Calculation for hours above 5 on workdays.
- Calculation for Holiday duty multipliers.
- Calculation for Eid special duty multipliers.

**All 5 tests passed.**

### Manual Verification Steps
1. Navigate to the dedicated **Overtime** section in the sidebar.
2. Click on **Monthly Config**.
2. Select an employee (e.g., Technician).
3. Select Month/Year and click **Load Form**.
4. Enter OT times (e.g., 17:00 to 20:00) -> Verify hours (3.00) and amount (105.00).
5. Check **Eid Special Duty** -> Verify amount changes to 3x full shift.
6. Click **Save Overtime Records** -> Verify success message and data persistence.

---
**Status: Complete**
