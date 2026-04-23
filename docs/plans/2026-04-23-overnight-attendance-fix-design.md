# Design Doc: Strict Check-In Thresholds for Roster Attendance

## Problem
Roster employees with overnight shifts (e.g., 10 PM - 7 AM) have their check-out logs incorrectly reused as check-in logs for the following day. This happens because the system fetches all logs on a calendar day and treats the first one as `in_time`, regardless of how far it is from the scheduled shift start.

## Solution: Approach 3 (Strict Check-In Thresholds)
Implement a proximity check for the `in_time` selection. A log will only be considered a valid check-in if it occurs within a specific window around the scheduled shift start time.

### Key Constraints
- **Threshold**: 4 hours for both early and late arrivals.
- **Scope**: Primary focus on Roster shifts, but can be extended to General shifts if applicable.

### Proposed Changes

#### `App\Services\AttendanceService`
- Update `processEmployeeAttendance` to calculate the `scheduledStart` for the shift.
- Filter the `$logs` collection to find the first log where `abs(punch_time - scheduledStart) <= 4 hours`.
- If a valid `in_time` is found:
    - Set `inTime` to that log.
    - Set `outTime` to the last log in the collection (as long as it's after `inTime`).
- If no valid `in_time` is found within the window:
    - `inTime` and `outTime` remain `null` (Absent status).

## Verification Plan

### Manual Verification
1. Create a roster shift for an employee from 10:00 PM to 7:00 AM.
2. Add attendance logs:
    - Day 1, 10:05 PM (Check-in)
    - Day 2, 7:05 AM (Check-out)
3. Process attendance for Day 1 and Day 2.
4. Verify:
    - Day 1 record has In: 10:05 PM, Out: 7:05 AM.
    - Day 2 record is "Absent" (or has no `in_time` from the 7:05 AM log).
5. Add a check-in for Day 2 at 9:55 PM.
6. Process Day 2 again.
7. Verify Day 2 record has In: 9:55 PM.
