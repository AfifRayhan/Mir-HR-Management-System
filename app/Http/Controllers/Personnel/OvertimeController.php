<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $isAdmin = in_array($roleName, ['HR Admin', 'Superadmin']); // Assuming Superadmin also exists or HR Admin is enough

        $myEmployee = Employee::with('designation')->where('user_id', $user->id)->first();
        $myEmployeeId = $myEmployee ? $myEmployee->id : 0;
        $isMySelfEligible = $myEmployee && $myEmployee->designation && $myEmployee->designation->is_ot_eligible;

        // Resolve subordinates
        $subordinateIds = [];
        if ($myEmployeeId) {
            $directReportIds = Employee::where('reporting_manager_id', $myEmployeeId)->pluck('id')->toArray();
            $departmentIds = \App\Models\Department::where('incharge_id', $myEmployeeId)->pluck('id')->toArray();
            $departmentalEmployeeIds = Employee::whereIn('department_id', $departmentIds)->pluck('id')->toArray();
            $subordinateIds = array_diff(array_unique(array_merge($directReportIds, $departmentalEmployeeIds)), [$myEmployeeId]);
        }

        // Filter employees list for the dropdown
        $query = Employee::where('status', 'active')
            ->whereHas('designation', function($q) {
                $q->where('is_ot_eligible', true);
            });

        if ($isAdmin) {
            // HR Admin sees everyone
        } elseif ($myEmployeeId) {
            // Team Lead / Employee sees themselves (if eligible) + subordinates (if eligible)
            $viewableIds = $subordinateIds;
            if ($isMySelfEligible) {
                $viewableIds[] = $myEmployeeId;
            }
            
            if (empty($viewableIds)) {
                // If I'm not eligible and have no subordinates (or none are eligible)
                if (!$isMySelfEligible) {
                    abort(403, 'You are not eligible for Overtime.');
                }
            }
            
            $query->whereIn('id', $viewableIds);
        } else {
            // No linked employee? Maybe just see nothing or self
            $query->whereRaw('1 = 0');
        }

        $employees = $query->orderBy('name')->get();

        $employeeId = $request->input('employee_id');
        
        // Default to self if no employee selected AND I am eligible
        if (!$employeeId && $myEmployeeId && $isMySelfEligible) {
            $employeeId = $myEmployeeId;
        }

        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $isTeamLeadRole = optional($user->role)->name === 'Team Lead';
        $isReportingManager = Employee::where('reporting_manager_id', $myEmployeeId)->exists();
        $isTeamLeadLayout = $isTeamLeadRole || $isReportingManager;

        $selectedEmployee = null;
        $canEdit = false;
        $daysInMonth = [];
        $overtimeRecords = [];
        $rosterSchedules = [];
        $weeklyHolidays = [];
        $holidays = [];

        if ($employeeId) {
            // Authorization check: Can I view this employee?
            $isSubordinate = in_array($employeeId, $subordinateIds);
            $isSelf = ($employeeId == $myEmployeeId);

            if (!$isAdmin && !$isSubordinate && !$isSelf) {
                abort(403, 'You do not have permission to view this employee\'s overtime.');
            }

            $selectedEmployee = Employee::with(['grade', 'officeTime'])->find($employeeId);
            
            // Rule: Admin can edit anyone. Managers can edit subordinates. Employees can edit self.
            $canEdit = $isAdmin || $isSubordinate || $isSelf;
            $startDate = Carbon::createFromDate($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $daysInMonth[] = $currentDate->copy();
                $currentDate->addDay();
            }

            $overtimeRecords = Overtime::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(function ($record) {
                    return $record->date->format('Y-m-d');
                });

            $rosterSchedules = \App\Models\RosterSchedule::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(function ($record) {
                    return $record->date->format('Y-m-d');
                });
            
            $weeklyHolidays = \App\Models\WeeklyHoliday::where('is_holiday', true)
                ->where(function($q) use ($selectedEmployee) {
                    $q->whereNull('office_id')
                      ->orWhere('office_id', $selectedEmployee->office_id);
                })
                ->pluck('day_name')
                ->toArray();

            $holidays = \App\Models\Holiday::where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('from_date', [$startDate, $endDate])
                      ->orWhereBetween('to_date', [$startDate, $endDate])
                      ->orWhere(function($q2) use ($startDate, $endDate) {
                          $q2->where('from_date', '<=', $startDate)
                             ->where('to_date', '>=', $endDate);
                      });
                })->get();

            $holidayMap = [];
            foreach ($holidays as $h) {
                $cursor = $h->from_date->copy();
                while ($cursor <= $h->to_date) {
                    $holidayMap[$cursor->format('Y-m-d')] = [
                        'title' => $h->title,
                        'type'  => $h->type
                    ];
                    $cursor->addDay();
                }
            }
            $holidays = $holidayMap;
        }

        // Resolve per-hour rate for JS real-time display
        $perHourRate = 0.0;
        if ($selectedEmployee) {
            if ($selectedEmployee->designation_id) {
                $designationRate = \App\Models\OvertimeRate::where('designation_id', $selectedEmployee->designation_id)
                    ->whereNull('grade_id')
                    ->value('rate');
                if ($designationRate !== null) {
                    $perHourRate = (float) $designationRate;
                }
            }
            if ($perHourRate === 0.0 && $selectedEmployee->grade_id) {
                $gradeRate = \App\Models\OvertimeRate::where('grade_id', $selectedEmployee->grade_id)
                    ->whereNull('designation_id')
                    ->value('rate');
                if ($gradeRate !== null) {
                    $perHourRate = (float) $gradeRate;
                }
            }
        }

        return view('personnel.overtimes.index', compact(
            'employees',
            'employeeId',
            'month',
            'year',
            'selectedEmployee',
            'daysInMonth',
            'overtimeRecords',
            'rosterSchedules',
            'weeklyHolidays',
            'holidays',
            'canEdit',
            'isTeamLeadLayout',
            'perHourRate'
        ));
    }

    public function save(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required',
            'year' => 'required',
            'ot' => 'required|array',
        ]);

        $employeeId = $request->employee_id;
        $employee = Employee::with('grade')->findOrFail($employeeId);

        if (!$this->canUserEditOvertime($employee)) {
            abort(403, 'You do not have permission to edit this employee\'s overtime.');
        }

        $otData = $request->ot;

        foreach ($otData as $date => $data) {
            $totalHours = $this->calculateTotalHours($data['start'], $data['stop']);
            
            // If all values are empty/zero, and a record exists, we might want to delete it or just skip
            if ($totalHours <= 0 && empty($data['workday_plus_5']) && empty($data['holiday_plus_5']) && empty($data['eid_duty'])) {
                Overtime::where('employee_id', $employeeId)->where('date', $date)->delete();
                continue;
            }

            $amount = $this->calculateAmount($employee, $totalHours, $data);

            Overtime::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                [
                    'ot_start' => $data['start'] ?: null,
                    'ot_stop' => $data['stop'] ?: null,
                    'total_ot_hours' => $totalHours,
                    'is_workday_duty_plus_5' => isset($data['workday_plus_5']),
                    'is_holiday_duty_plus_5' => isset($data['holiday_plus_5']),
                    'is_eid_duty' => isset($data['eid_duty']),
                    'amount' => $amount,
                    'remarks' => $data['remarks'] ?? null,
                    'created_by' => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Overtime records saved successfully.');
    }

    /**
     * Return attendance-based OT suggestions for a given employee/month.
     *
     * Only returns days that:
     *   - Have an attendance record with both in_time and out_time
     *   - Do NOT already have a saved Overtime record (non-destructive)
     *   - Have out_time > (in_time + shift_duration) — genuine OT
     *
     * OT rule (late employees complete their full shift first):
     *   ot_start = in_time + shift_duration_hours
     *   ot_stop  = out_time
     */
    public function autoFill(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month'       => 'required|string',
            'year'        => 'required|integer',
        ]);

        $employeeId = $request->employee_id;
        $month      = $request->month;
        $year       = $request->year;

        $employee = Employee::with(['officeTime'])->findOrFail($employeeId);

        if (!$this->canUserEditOvertime($employee)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Fetch attendance records for this month (need both in and out times)
        $attendanceRecords = \App\Models\AttendanceRecord::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereNotNull('in_time')
            ->whereNotNull('out_time')
            ->get()
            ->keyBy(fn($r) => \Illuminate\Support\Carbon::parse($r->date)->format('Y-m-d'));

        // Dates that already have saved OT records — skip these
        $existingOt = Overtime::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->pluck('date')
            ->map(fn($d) => \Illuminate\Support\Carbon::parse($d)->format('Y-m-d'))
            ->flip()
            ->toArray(); // use as a lookup set

        $suggestions = [];
        $attendanceService = app(\App\Services\AttendanceService::class);

        foreach ($attendanceRecords as $dateStr => $record) {
            if (isset($existingOt[$dateStr])) {
                continue;
            }

            $inTime  = \Illuminate\Support\Carbon::parse($record->in_time);
            $outTime = \Illuminate\Support\Carbon::parse($record->out_time);

            // Check if it's a working day (standard or roster)
            $isWorkingDay = $attendanceService->isWorkingDay($employee, $dateStr);

            if ($isWorkingDay) {
                // Workday: OT starts after full shift duration is completed
                $shiftHours = $this->resolveShiftDuration($employee, $dateStr);
                if ($shiftHours === null || $shiftHours <= 0) {
                    continue;
                }
                $otStart = $inTime->copy()->addHours($shiftHours);
            } else {
                // Off-day: OT starts from in_time (entire duration is OT)
                $otStart = $inTime->copy();
            }

            if (!$outTime->greaterThan($otStart)) {
                continue;
            }

            $suggestions[$dateStr] = [
                'ot_start'       => $otStart->format('H:i'),
                'ot_stop'        => $outTime->format('H:i'),
                'total_ot_hours' => round($otStart->diffInMinutes($outTime) / 60, 2),
            ];
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Resolve the scheduled shift duration (decimal hours) for an employee on a given date.
     * Returns null if no shift definition can be found.
     * Lunch breaks are intentionally ignored per business rule.
     */
    private function resolveShiftDuration(Employee $employee, string $date): ?float
    {
        $officeTime = $employee->officeTime;

        if (!$officeTime) {
            return null;
        }

        // Roster employee: get shift from RosterTime via AttendanceService
        if ($officeTime->shift_name === 'Roster') {
            $attendanceService = app(\App\Services\AttendanceService::class);
            $rosterShift = $attendanceService->getRosterShiftForDate($employee, $date);

            if (!$rosterShift || $rosterShift->is_off_day || !$rosterShift->start_time || !$rosterShift->end_time) {
                return null;
            }

            $start = \Illuminate\Support\Carbon::parse($date . ' ' . $rosterShift->start_time);
            $end   = \Illuminate\Support\Carbon::parse($date . ' ' . $rosterShift->end_time);
            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay(); // overnight shift
            }

            return round($start->diffInMinutes($end) / 60, 2);
        }

        // Standard employee: use office_times start/end
        if (!$officeTime->start_time || !$officeTime->end_time) {
            return null;
        }

        $start = \Illuminate\Support\Carbon::parse($date . ' ' . $officeTime->start_time);
        $end   = \Illuminate\Support\Carbon::parse($date . ' ' . $officeTime->end_time);
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        return round($start->diffInMinutes($end) / 60, 2);
    }

    private function calculateTotalHours(string $start, string $stop): float
    {
        if (!$start || !$stop) return 0;

        $startTime = Carbon::parse($start);
        $stopTime = Carbon::parse($stop);

        if ($stopTime->lt($startTime)) {
            $stopTime->addDay();
        }

        return $startTime->diffInMinutes($stopTime) / 60;
    }

    private function calculateAmount(Employee $employee, float $totalHours, array $data): float
    {
        // Resolve per-hour rate: designation rate takes priority over grade rate.
        $perHourRate = 0.0;

        if ($employee->designation_id) {
            $designationRate = \App\Models\OvertimeRate::where('designation_id', $employee->designation_id)
                ->whereNull('grade_id')
                ->value('rate');
            if ($designationRate !== null) {
                $perHourRate = (float) $designationRate;
            }
        }

        if ($perHourRate === 0.0 && $employee->grade_id) {
            $gradeRate = \App\Models\OvertimeRate::where('grade_id', $employee->grade_id)
                ->whereNull('designation_id')
                ->value('rate');
            if ($gradeRate !== null) {
                $perHourRate = (float) $gradeRate;
            }
        }

        // Full-shift income (per-day amount) — used when hours > 5
        $fullShiftIncome = ($employee->gross_salary * 0.6) / 30;

        // Tier 1: <= 5 hours (Floor hours, NO multipliers)
        if ($totalHours <= 5) {
            return round(floor($totalHours) * $perHourRate, 2);
        }

        // Tier 2: > 5 hours (Base 1000 + bonuses)
        // Base: 2 units (1000 BDT)
        $units = 2;

        // Eid Bonus: +4 units (2000 BDT)
        if (isset($data['eid_duty'])) {
            $units += 4;
        }

        // Long Shift Bonus: +1 unit (500 BDT) if > 12 hours
        if ($totalHours > 12) {
            $units += 1;
        }

        return round($fullShiftIncome * $units, 2);
    }

    /**
     * Check if the logged-in user can edit the target employee's overtime.
     */
    private function canUserEditOvertime(Employee $targetEmployee): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        
        // HR Admin / Superadmin can edit ANYONE (including themselves)
        if (in_array($roleName, ['HR Admin', 'Superadmin'])) {
            return true;
        }

        $myEmployee = Employee::where('user_id', $user->id)->first();
        if (!$myEmployee) return false;

        $myEmployeeId = $myEmployee->id;
        $targetEmployeeId = $targetEmployee->id;

        // Employees can edit their own overtime records
        if ($myEmployeeId == $targetEmployeeId) {
            return true;
        }

        // Check if Reporting Manager (Direct or Departmental)
        $isDirectManager = ($targetEmployee->reporting_manager_id == $myEmployeeId);
        $isDeptIncharge  = false;
        if ($targetEmployee->department_id) {
            $isDeptIncharge = \App\Models\Department::where('id', $targetEmployee->department_id)
                ->where('incharge_id', $myEmployeeId)
                ->exists();
        }

        return $isDirectManager || $isDeptIncharge;
    }
}
