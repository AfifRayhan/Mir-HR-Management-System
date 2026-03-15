<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\DeviceLog;
use App\Models\AttendanceRecord;
use App\Models\ManualAttendanceAdjustment;
use App\Models\WeeklyHoliday;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
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

        // Check for manual adjustment first
        $adjustment = ManualAttendanceAdjustment::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        if ($adjustment) {
            $inTime = $adjustment->in_time;
            $outTime = $adjustment->out_time;
        } else {
            // Get logs from devices for this employee
            // We match by employee_code
            if (!$employee->employee_code) {
                return;
            }

            $logs = DeviceLog::where('employee_code', $employee->employee_code)
                ->whereDate('punch_time', $date)
                ->orderBy('punch_time', 'asc')
                ->get();

            if ($logs->isEmpty()) {
                $inTime = null;
                $outTime = null;
            } else {
                $inTime = $logs->first()->punch_time;
                $outTime = $logs->count() > 1 ? $logs->last()->punch_time : null;
            }
        }

        $this->updateOrCreateRecord($employee, $date, $inTime, $outTime);
    }

    /**
     * Update or create attendance record based on in/out times.
     */
    protected function updateOrCreateRecord(Employee $employee, $date, $inTime, $outTime)
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

            if ($officeTime && $officeTime->late_after) {
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
                // it might be an overnight shift (though not fully supported yet)
                if ($outTime->lessThan($inTime)) {
                    $outTime->addDay();
                }

                $workingHours = round(abs($outTime->diffInSeconds($inTime)) / 3600, 2);
            }
        }

        // Check if date is a holiday or weekly holiday (simplified for now)
        // In a real system, we'd check Holiday model and WeeklyHoliday model

        AttendanceRecord::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date],
            [
                'in_time' => $inTime,
                'out_time' => $outTime,
                'working_hours' => $workingHours,
                'late_seconds' => $lateSeconds,
                'status' => $status,
            ]
        );
    }

    /**
     * Check if a given date is a working day for the employee.
     */
    public function isWorkingDay(Employee $employee, $date)
    {
        $carbonDate = Carbon::parse($date);
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
}
