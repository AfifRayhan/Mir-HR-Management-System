# Design Doc: Bonus Ear Leave (EL) Implementation

## Goal
Add a new leave type 'Bonus Ear Leave (EL)' which is only available to employees with at least 1 year of service.

## Design
### 1. Database Seeder
Update `database/seeders/LeaveTypeSeeder.php` to include:
- Name: 'Bonus Ear Leave (EL)'
- Total Days: 10
- Max Consecutive Days: null
- Carry Forward: false
- Sort Order: 5

### 2. Allocation Logic
Update `app/Http/Controllers/Personnel/LeaveBalanceController.php` method `getAllocatedDays`:
- Add logic to identify 'Bonus Ear Leave (EL)'.
- Check `joining_date` vs current date.
- If service < 1 year, return 0.
- Else return 10.

## Implementation Details
- This approach ensures that ineligible employees get a 0 balance, effectively preventing them from using the leave type until they reach 1 year and their balance is re-initialized (or during the next yearly cycle).
