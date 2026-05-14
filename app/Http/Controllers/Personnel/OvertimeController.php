<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OvertimeExport;
use App\Models\OvertimeSpecialRate;

class OvertimeController extends Controller
{
    private const NOC_ROSTER_GROUPS = [
        'noc-borak',
        'noc-sylhet',
        'NOC (Borak)',
        'NOC (Sylhet)',
    ];

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $canViewAll = $user->hasMenuAccess('overtime-admin-config');

        $myEmployee = Employee::with('designation')->where('user_id', $user->id)->first();
        $myEmployeeId = $myEmployee ? $myEmployee->id : 0;
        $isMySelfEligible = $myEmployee && $myEmployee->designation && $myEmployee->designation->is_ot_eligible;


        // Filter employees list for the dropdown
        $query = Employee::where('status', 'active')
            ->whereHas('designation', function($q) {
                $q->where('is_ot_eligible', true);
            });

        if ($canViewAll) {
            // Permission granted - see everyone
        } elseif ($myEmployeeId) {
            // No admin config permission - only see themselves
            $query->where('id', $myEmployeeId);
            
            if (!$isMySelfEligible) {
                abort(403, 'You are not eligible for Overtime.');
            }
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
        $eidAdjacentDays = [];

        if ($employeeId) {
            // Authorization check: Can I view this employee?
            $isSelf = ($employeeId == $myEmployeeId);
            if (!$canViewAll && !$isSelf) {
                abort(403, 'You do not have permission to view this employee\'s overtime.');
            }

            $selectedEmployee = Employee::with(['grade', 'officeTime'])->find($employeeId);
            
            // Rule: Users with Admin Config can edit anyone. Others can only edit themselves.
            $canEdit = $canViewAll || $isSelf;
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

            // Find Eid Adjacent days
            foreach ($holidays as $date => $holiday) {
                if ($holiday['type'] === 'Eid Day') {
                    $d = Carbon::parse($date);
                    $eidAdjacentDays[$d->copy()->subDay()->format('Y-m-d')] = true;
                    $eidAdjacentDays[$date] = true;
                    $eidAdjacentDays[$d->copy()->addDay()->format('Y-m-d')] = true;
                }
            }
        }

        // Resolve per-hour rate for JS real-time display
        $perHourRate = 0.0;
        $specialEidRate = 0.0;
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
            if ($selectedEmployee->roster_group) {
                $specialEidRate = (float) OvertimeSpecialRate::where('roster_group', $selectedEmployee->roster_group)
                    ->where('is_eid_special', true)
                    ->value('rate');
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
            'perHourRate',
            'specialEidRate',
            'eidAdjacentDays',
            'canViewAll'
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

        // Fetch Eid adjacent days dynamically for the backend calculateAmount call
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $eidHolidays = \App\Models\Holiday::where('type', 'Eid Day')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate->copy()->subDays(5), $endDate->copy()->addDays(5)])
                  ->orWhereBetween('to_date', [$startDate->copy()->subDays(5), $endDate->copy()->addDays(5)]);
            })->get();
            
        $eidAdjacentDays = [];
        $eidActualDays = [];
        foreach ($eidHolidays as $h) {
            $c = $h->from_date->copy();
            while ($c <= $h->to_date) {
                $dateStr = $c->format('Y-m-d');
                $eidActualDays[$dateStr] = true;
                $eidAdjacentDays[\Illuminate\Support\Carbon::parse($dateStr)->subDay()->format('Y-m-d')] = true;
                $eidAdjacentDays[$dateStr] = true;
                $eidAdjacentDays[\Illuminate\Support\Carbon::parse($dateStr)->addDay()->format('Y-m-d')] = true;
                $c->addDay();
            }
        }

        foreach ($otData as $date => $data) {
            $totalHours = $this->calculateTotalHours($data['start'], $data['stop']);
            
            // If all values are empty/zero, and a record exists, we might want to delete it or just skip
            if ($totalHours <= 0 && empty($data['workday_plus_5']) && empty($data['holiday_plus_5']) && empty($data['eid_duty'])) {
                Overtime::where('employee_id', $employeeId)->where('date', $date)->delete();
                continue;
            }

            $isEidAdjacent = isset($eidAdjacentDays[$date]);
            $isEidDay = isset($eidActualDays[$date]);
            $amount = $this->calculateAmount($employee, $totalHours, $data, $isEidAdjacent, $isEidDay);

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
            ->map(fn ($date) => Carbon::parse($date)->format('Y-m-d'))
            ->flip()
            ->all();

        // Fetch Eid adjacent days for the bypass logic
        $startDate = \Illuminate\Support\Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $eidHolidays = \App\Models\Holiday::where('type', 'Eid Day')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate->copy()->subDays(5), $endDate->copy()->addDays(5)])
                  ->orWhereBetween('to_date', [$startDate->copy()->subDays(5), $endDate->copy()->addDays(5)]);
            })->get();
            
        $eidAdjacentDays = [];
        foreach ($eidHolidays as $h) {
            $c = $h->from_date->copy();
            while ($c <= $h->to_date) {
                $dateStr = $c->format('Y-m-d');
                $eidAdjacentDays[\Illuminate\Support\Carbon::parse($dateStr)->subDay()->format('Y-m-d')] = true;
                $eidAdjacentDays[$dateStr] = true;
                $eidAdjacentDays[\Illuminate\Support\Carbon::parse($dateStr)->addDay()->format('Y-m-d')] = true;
                $c->addDay();
            }
        }

        $suggestions = [];
        $attendanceService = app(\App\Services\AttendanceService::class);
        $isNocGroup = $this->isNocRosterGroup($employee->roster_group);

        foreach ($attendanceRecords as $dateStr => $record) {
            if (isset($existingOt[$dateStr])) {
                continue;
            }

            $inTime  = \Illuminate\Support\Carbon::parse($record->in_time);
            $outTime = \Illuminate\Support\Carbon::parse($record->out_time);
            $isEidAdjacent = isset($eidAdjacentDays[$dateStr]);

            // Check if it's a working day (standard or roster)
            $isWorkingDay = $attendanceService->isWorkingDay($employee, $dateStr);

            // SPECIAL BYPASS: NOC on Eid days treat the whole shift as OT
            if ($isNocGroup && $isEidAdjacent) {
                $otStart = $inTime->copy();
            } else if ($isWorkingDay) {
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
                'eid_duty'       => ($isNocGroup && $isEidAdjacent)
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

    private function calculateTotalHours(?string $start, ?string $stop): float
    {
        if (!$start || !$stop) return 0;

        $startTime = Carbon::parse($start);
        $stopTime = Carbon::parse($stop);

        if ($stopTime->lt($startTime)) {
            $stopTime->addDay();
        }

        return $startTime->diffInMinutes($stopTime) / 60;
    }

    private function isNocRosterGroup(?string $rosterGroup): bool
    {
        return in_array($rosterGroup, self::NOC_ROSTER_GROUPS, true);
    }

    private function resolveNocHybridExtraHours(float $totalHours): float
    {
        if ($totalHours <= 0) {
            return 0;
        }

        // Support both entry styles:
        // - full duty span entered (e.g. 12h => 4 extra)
        // - extra block only entered (e.g. 4h => 4 extra)
        return $totalHours > 8 ? ($totalHours - 8) : $totalHours;
    }

    private function calculateAmount(Employee $employee, float $totalHours, array $data, bool $isEidAdjacent = false, bool $isEidDay = false): float
    {
        $perHourRate = $this->getEmployeePerHourRate($employee);
        $isNocGroup = $this->isNocRosterGroup($employee->roster_group);
        $hasHybridDuty = isset($data['eid_duty']) || isset($data['holiday_plus_5']);
        
        if ($isEidAdjacent && $employee->roster_group) {
            $specialRate = (float) OvertimeSpecialRate::where('roster_group', $employee->roster_group)
                ->where('is_eid_special', true)
                ->value('rate');
            if ($specialRate > 0) {
                $perHourRate = $specialRate;
            }
        }

        // Full-shift income (per-day amount) — used when hours > 5
        $fullShiftIncome = ($employee->gross_salary * 0.6) / 30;

        // If any duty is marked, use shift-based (units) calculation
        if (isset($data['eid_duty']) || isset($data['holiday_plus_5']) || isset($data['workday_plus_5'])) {
            
            // HYBRID LOGIC: NOC on Eid-adjacent dates get (Base Units + Extra Hourly)
            if ($hasHybridDuty && $isNocGroup && $isEidAdjacent) {
                $units = $isEidDay ? 3 : 2;
                $baseAmount = $units * $fullShiftIncome;
                $extraHours = $this->resolveNocHybridExtraHours($totalHours);
                // Floor the extra hours before multiplying by rate, to match existing hourly policy
                $extraAmount = floor($extraHours) * $perHourRate;
                
                return round($baseAmount + $extraAmount, 2);
            }

            $units = 0;
            
            // Base category units
            if (isset($data['eid_duty'])) {
                $units = 3;
            } elseif (isset($data['holiday_plus_5'])) {
                $units = 2;
            }

            // Workday Duty (+5 hrs) checkbox units
            if (isset($data['workday_plus_5'])) {
                if (isset($data['eid_duty'])) {
                    $units += 3; // Eid Long Shift: 3+3=6
                } elseif (isset($data['holiday_plus_5'])) {
                    $units += 2; // Holiday Long Shift: 2+2=4
                } else {
                    $units += 2; // Regular Workday: 2 units
                    if ($totalHours > 12) {
                        $units += 1; // Long Shift Bonus: +1
                    }
                }
            }
            return round($units * $fullShiftIncome, 2);
        }

        // Fallback: Hourly OT (Floor hours)
        return round(floor($totalHours) * $perHourRate, 2);
    }

    /**
     * Check if the logged-in user can edit the target employee's overtime.
     */
    private function canUserEditOvertime(Employee $targetEmployee): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        
        // Users with Admin Config permission can edit ANYONE
        if ($user->hasMenuAccess('overtime-admin-config')) {
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

        return false; // No longer allowing manager edits unless they have Admin Config
    }

    private function getEmployeePerHourRate(Employee $employee): float
    {
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
        return $perHourRate;
    }

    public function export(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $format = $request->input('format', 'pdf');

        if (!$employeeId) {
            return redirect()->back()->with('error', 'Please select an employee first.');
        }

        $employee = Employee::with(['designation', 'department', 'reportingManager', 'grade', 'officeTime'])->findOrFail($employeeId);
        
        if (!$this->canUserEditOvertime($employee)) {
            abort(403, 'Unauthorized.');
        }

        // Increase memory and time for PDF generation
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $records = Overtime::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->toDateString();
            });

        $daysInMonth = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $daysInMonth[] = $curr->copy();
            $curr->addDay();
        }

        $weeklyHolidays = \App\Models\WeeklyHoliday::where('is_holiday', true)
            ->where(function ($q) use ($employee) {
                $q->where('office_id', $employee->office_id)->orWhereNull('office_id');
            })
            ->pluck('day_name')
            ->toArray();

        $holidays = \App\Models\Holiday::where('is_active', true)
            ->where(function ($q) use ($employee) {
                $q->where('all_office', true)->orWhere('office_id', $employee->office_id);
            })
            ->get()
            ->mapWithKeys(function ($h) {
                $dates = [];
                $c = Carbon::parse($h->from_date);
                $e = Carbon::parse($h->to_date);
                while ($c->lte($e)) {
                    $dates[$c->toDateString()] = [
                        'name' => $h->name,
                        'type' => $h->type,
                    ];
                    $c->addDay();
                }
                return $dates;
            })
            ->toArray();

        $rosterSchedules = \App\Models\RosterSchedule::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(function($r) {
                return Carbon::parse($r->date)->toDateString();
            });

        $perHourRate = $this->getEmployeePerHourRate($employee);

        $data = [
            'employee' => $employee,
            'month' => $month,
            'year' => $year,
            'monthName' => $startDate->format('F'),
            'daysInMonth' => $daysInMonth,
            'records' => $records,
            'weeklyHolidays' => $weeklyHolidays,
            'holidays' => $holidays,
            'rosterSchedules' => $rosterSchedules,
            'perHourRate' => $perHourRate,
        ];

        if ($format === 'pdf') {
            $export = new OvertimeExport($request->all());
            $view = $export->view();
            $viewData = $view->getData();
            $pdf = PDF::loadView($view->name(), $viewData);
            $pdf->setPaper('a4', 'portrait');
            $filename = "Overtime_{$employee->name}_" . ($viewData['monthName'] ?? 'Report') . "_{$year}.pdf";
            return $pdf->download($filename);
        } elseif (in_array($format, ['excel', 'csv'])) {
            $filename = "Overtime_{$employee->name}_" . Carbon::createFromDate($year, $month, 1)->format('F') . "_{$year}." . ($format === 'excel' ? 'xlsx' : 'csv');
            return Excel::download(new OvertimeExport($request->all()), $filename, $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : null);
        } elseif ($format === 'word') {
            $export = new OvertimeExport($request->all());
            $view = $export->view();
            $filename = "Overtime_{$employee->name}_" . Carbon::createFromDate($year, $month, 1)->format('F') . "_{$year}.doc";
            return response($view->render())
                ->header('Content-Type', 'application/vnd.ms-word')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        return abort(404);
    }
}
