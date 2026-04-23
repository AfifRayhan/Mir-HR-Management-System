<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\ManualAttendanceAdjustment;
use App\Models\WeeklyHoliday;
use App\Models\Holiday;
use App\Models\RosterSchedule;
use App\Models\RosterTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Map roster_group labels to group_slugs used in roster_times table.
     */
    const ROSTER_GROUP_SLUG_MAP = [
        'NOC (Borak)'           => 'noc-borak',
        'NOC (Sylhet)'          => 'noc-sylhet',
        'Technician (Gulshan)'  => 'tech-gulshan',
        'Technician (Borak)'    => 'tech-borak',
        'Technician (Jessore)'  => 'tech-jessore',
        'Technician (Sylhet)'   => 'tech-sylhet',
    ];
    /**
     * Process logs for a specific date and update attendance records.
     */
    public function processLogsForDate($date)
    {
        $date = Carbon::parse($date)->toDateString();

        // Get all employees
        $employees = Employee::where('status', 'active')->get();

        foreach ($employees as $employee) {
            $this->processEmployeeAttendance($employee, $date);
        }
    }

    /**
     * Process attendance for a specific employee and date.
     */
    public function processEmployeeAttendance(Employee $employee, $date)
    {
        $date = Carbon::parse($date)->toDateString();

        // Skip processing for roster off-days or unscheduled roster days
        $officeTime = $employee->officeTime;
        if ($officeTime && $officeTime->shift_name === 'Roster') {
            $rosterShift = $this->getRosterShiftForDate($employee, $date);
            if (!$rosterShift || $rosterShift->is_off_day) {
                return;
            }
        } else {
            $rosterShift = null;
        }

        $machineId = null;
        $isManual = false;

        // Check for manual adjustment first
        $adjustment = ManualAttendanceAdjustment::where('employee_id', $employee->id)
            ->where('date', $date)
            ->where('status', 'approved')
            ->first();

        if (!$adjustment) {
            // Fallback for older records where status might not be approved or we just check if it exists
            $adjustment = ManualAttendanceAdjustment::where('employee_id', $employee->id)
                ->where('date', $date)
                ->first();
        }

        if ($adjustment && (!isset($adjustment->status) || $adjustment->status === 'approved')) {
            $inTime = $adjustment->in_time;
            $outTime = $adjustment->out_time;
            $isManual = true;
        } else {
            // Get logs from synced attendances table for this employee
            if (!$employee->employee_code) {
                return;
            }

            // Determine if this is an overnight roster shift
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

            if ($logs->isEmpty()) {
                $inTime = null;
                $outTime = null;
            } else {
                // Find first valid inTime within 4 hours of scheduled start if it's a roster shift
                if ($rosterShift && $rosterShift->start_time) {
                    $scheduledStart = Carbon::parse($date . ' ' . $rosterShift->start_time);
                    
                    $validInLog = $logs->first(function($log) use ($scheduledStart) {
                        // Use abs() to cover both early and late check-ins within 4 hours
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
        }

        $this->updateOrCreateRecord($employee, $date, $inTime, $outTime, $machineId, $isManual, $rosterShift);
    }

    /**
     * Update or create attendance record based on in/out times.
     */
    protected function updateOrCreateRecord(Employee $employee, $date, $inTime, $outTime, $machineId = null, $isManual = false, ?RosterTime $rosterShift = null)
    {
        $officeTime = $employee->officeTime;

        $status = 'absent';
        $lateSeconds = 0;
        $workingHours = 0;

        if ($inTime) {
            $inTime = Carbon::parse($inTime);
            // Normalize inTime to the record date to avoid day-offset errors
            $inTime->setDateFrom(Carbon::parse($date));

            $status = 'present';

            if ($rosterShift && $rosterShift->start_time && !$rosterShift->is_off_day) {
                // Roster employee: late = start_time + 1 hour
                $shiftStart = Carbon::parse($date . ' ' . $rosterShift->start_time);
                $lateAfter = $shiftStart->copy()->addHour();

                if ($inTime->greaterThan($shiftStart) && $inTime->greaterThan($lateAfter)) {
                    $status = 'late';
                    $lateSeconds = abs($inTime->diffInSeconds($lateAfter));
                }
            } elseif ($officeTime && $officeTime->late_after) {
                // Non-roster employee: use existing OfficeTime logic
                $startTime = Carbon::parse($date . ' ' . $officeTime->start_time);
                $lateAfter = Carbon::parse($date . ' ' . $officeTime->late_after);

                // Only mark as late if inTime is AFTER both shift start and late threshold
                if ($inTime->greaterThan($startTime) && $inTime->greaterThan($lateAfter)) {
                    $status = 'late';
                    $lateSeconds = abs($inTime->diffInSeconds($lateAfter));
                }
            }

            if ($outTime) {
                $outTime = Carbon::parse($outTime);
                // Normalize outTime to the record date
                $outTime->setDateFrom(Carbon::parse($date));

                // If out_time is actually before in_time after normalization, 
                // it might be an overnight shift (though not fully supported yet) OR a standard overnight shift
                if ($outTime->lessThan($inTime)) {
                    $outTime->addDay();
                }

                $workingHours = round(abs($outTime->diffInSeconds($inTime)) / 3600, 2);
            }
        }

        AttendanceRecord::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date],
            [
                'machine_id' => $machineId,
                'in_time' => $inTime,
                'out_time' => $outTime,
                'working_hours' => $workingHours,
                'late_seconds' => $lateSeconds,
                'status' => $status,
                'is_manual' => $isManual,
            ]
        );
    }

    /**
     * Get the RosterTime config for an employee on a given date.
     * Returns null if the employee is not a roster employee or has no schedule.
     */
    public function getRosterShiftForDate(Employee $employee, $date): ?RosterTime
    {
        $date = Carbon::parse($date)->toDateString();

        // Check if this employee is on a Roster shift
        $officeTime = $employee->officeTime;
        if (!$officeTime || $officeTime->shift_name !== 'Roster') {
            return null;
        }

        // Look up their schedule for this date
        $schedule = RosterSchedule::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        if (!$schedule || !$schedule->shift_type) {
            return null;
        }

        // Resolve group_slug from employee's roster_group
        $groupSlug = self::ROSTER_GROUP_SLUG_MAP[$employee->roster_group] ?? null;
        if (!$groupSlug) {
            return null;
        }

        // Look up the shift timing
        return RosterTime::where('group_slug', $groupSlug)
            ->where('shift_key', $schedule->shift_type)
            ->first();
    }

    /**
     * Check if a given date is a working day for the employee.
     */
    public function isWorkingDay(Employee $employee, $date)
    {
        $carbonDate = Carbon::parse($date);
        
        $officeTime = $employee->officeTime;
        if ($officeTime && $officeTime->shift_name === 'Roster') {
            // Roster Employee: Only roster schedule matters. 
            // They work on national holidays (as per user clarification).
            $rosterShift = $this->getRosterShiftForDate($employee, $date);
            return $rosterShift && !$rosterShift->is_off_day;
        }

        // General Employee Logic
        $dayName = $carbonDate->format('l');

        // 1. Check Weekly Holidays
        $hasOfficeConfig = WeeklyHoliday::where('office_id', $employee->office_id)->exists();
        $isWeeklyHoliday = WeeklyHoliday::where('day_name', $dayName)
            ->where(function ($q) use ($hasOfficeConfig, $employee) {
                if ($hasOfficeConfig) {
                    $q->where('office_id', $employee->office_id);
                } else {
                    $q->whereNull('office_id');
                }
            })
            ->where('is_holiday', true)
            ->exists();

        if ($isWeeklyHoliday) {
            return false;
        }

        // 2. Check General Holidays
        $isHoliday = Holiday::where('is_active', true)
            ->where(function ($q) use ($carbonDate, $employee) {
                $q->where(function ($sq) use ($carbonDate) {
                    $sq->whereDate('from_date', '<=', $carbonDate)
                        ->whereDate('to_date', '>=', $carbonDate);
                })
                ->where(function ($sq) use ($employee) {
                    $sq->where('all_office', true)
                        ->orWhere('office_id', $employee->office_id);
                });
            })
            ->exists();

        if ($isHoliday) {
            return false;
        }

        return true;
    }

    public function getDateAttendanceStatus(Employee $employee, $date): string
    {
        $carbonDate = Carbon::parse($date);
        $dayName = $carbonDate->format('l');

        $officeTime = $employee->officeTime;
        if ($officeTime && $officeTime->shift_name === 'Roster') {
            $rosterShift = $this->getRosterShiftForDate($employee, $date);
            if (!$rosterShift || $rosterShift->is_off_day) {
                // For roster, if it's Fri/Sat and an off-day, we can still label as weekly holiday for UI clarity
                if (in_array($dayName, ['Friday', 'Saturday'])) {
                    return 'weekly_holiday';
                }
                return 'off_day';
            }
            return 'working_day';
        }

        // General Employee Logic: Check for specific holidays first (gazetted)
        $isHoliday = Holiday::where('is_active', true)
            ->where(function ($q) use ($carbonDate, $employee) {
                $q->where(function ($sq) use ($carbonDate) {
                    $sq->whereDate('from_date', '<=', $carbonDate)
                        ->whereDate('to_date', '>=', $carbonDate);
                })
                ->where(function ($sq) use ($employee) {
                    $sq->where('all_office', true)
                        ->orWhere('office_id', $employee->office_id);
                });
            })
            ->exists();

        if ($isHoliday) {
            return 'holiday';
        }

        // Check Weekly Holidays (Friday/Saturday usually)
        $hasOfficeConfig = WeeklyHoliday::where('office_id', $employee->office_id)->exists();
        $isWeeklyHoliday = WeeklyHoliday::where('day_name', $dayName)
            ->where(function ($q) use ($hasOfficeConfig, $employee) {
                if ($hasOfficeConfig) {
                    $q->where('office_id', $employee->office_id);
                } else {
                    $q->whereNull('office_id');
                }
            })
            ->where('is_holiday', true)
            ->exists();

        if ($isWeeklyHoliday) {
            return 'weekly_holiday';
        }

        return 'working_day';
    }
}
