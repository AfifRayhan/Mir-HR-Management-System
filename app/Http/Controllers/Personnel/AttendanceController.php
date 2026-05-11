<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ManualAttendanceAdjustment;
use App\Models\Office;
use App\Services\AttendanceService;
use App\Services\NotificationService;
use App\Models\WeeklyHoliday;
use App\Models\Holiday;
use App\Models\RosterSchedule;
use App\Models\RosterTime;
use App\Models\LeaveApplication;
use App\Exports\AttendancesExport;
use App\Exports\MonthlyAttendanceExport;
use App\Exports\YearlyAttendanceExport;
use App\Exports\EmployeeLogExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new AttendancesExport($request->all(), 'excel'), 'attendance_' . date('Y-m-d') . '.xlsx');
    }

    public function exportCsv(Request $request)
    {
        return Excel::download(new AttendancesExport($request->all(), 'csv'), 'attendance_' . date('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $date = $request->input('date', now()->toDateString());
        $selectedOffice = $request->office_id ? Office::find($request->office_id) : null;
        
        $query = AttendanceRecord::with(['employee.department', 'employee.designation', 'employee.office'])
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            })
            ->where('date', $date);

        if ($request->department_id) $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        if ($request->office_id) $query->whereHas('employee', fn($q) => $q->where('office_id', $request->office_id));
        if ($request->status) $query->where('status', $request->status);
        if ($request->search) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('employee_code', 'like', "%{$request->search}%");
            });
        }

        $records = $query->lazy();

        return PDF::loadView('personnel.attendance.exports.daily-pdf', [
                'records' => $records,
                'date' => $date,
                'selectedOffice' => $selectedOffice,
            ])
            ->setPaper('a3', 'landscape')
            ->setOption('margin-bottom', 10)
            ->setOption('margin-top', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10)
            ->download('attendance_' . $date . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $departmentId = $request->input('department_id');
        $officeId = $request->input('office_id');
        $selectedOffice = $officeId ? Office::find($officeId) : null;
        $status = $request->input('status');
        $search = $request->input('search');

        $query = AttendanceRecord::with(['employee.department', 'employee.designation', 'employee.office'])
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            })
            ->where('date', $date);

        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }
        if ($officeId) {
            $query->whereHas('employee', fn($q) => $q->where('office_id', $officeId));
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $records = $query->lazy();
        $filename = 'attendance_' . $date . '.doc';

        return response()->view('personnel.attendance.exports.word', [
            'records' => $records,
            'date' => $date,
            'selectedOffice' => $selectedOffice,
        ])
        ->header('Content-Type', 'application/vnd.ms-word')
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        $date         = $request->input('date', now()->toDateString());
        $departmentId = $request->input('department_id');
        $officeId     = $request->input('office_id');
        $status       = $request->input('status');
        $search       = $request->input('search');

        $query = AttendanceRecord::with(['employee.department', 'employee.designation', 'employee.office'])
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            })
            ->where('date', $date);

        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ($officeId) {
            $query->whereHas('employee', fn($q) => $q->where('office_id', $officeId));
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $records     = $query->get();
        $departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => Department::all());
        $offices     = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => Office::all());
        $statuses    = ['present', 'late', 'absent', 'leave'];

        return view('personnel.attendance.index', compact(
            'records', 'departments', 'offices', 'statuses', 'date',
            'user', 'roleName', 'employeeRecord'
        ));
    }

    public function exportPreview(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        $date         = $request->input('date', now()->toDateString());
        $departmentId = $request->input('department_id');
        $officeId     = $request->input('office_id');
        $designationId = $request->input('designation_id');
        $status       = $request->input('status');
        $search       = $request->input('search');

        $query = AttendanceRecord::with(['employee.department', 'employee.designation', 'employee.office'])
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            })
            ->where('date', $date);

        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ($officeId) {
            $query->whereHas('employee', fn($q) => $q->where('office_id', $officeId));
        }

        if ($designationId) {
            $query->whereHas('employee', fn($q) => $q->where('designation_id', $designationId));
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $records = $query->paginate(20)->withQueryString();
        $departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => Department::all());
        $offices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => Office::all());
        $designations = \Illuminate\Support\Facades\Cache::remember('designations_all', 3600, fn() => \App\Models\Designation::all());
        $statuses = ['present', 'late', 'absent', 'leave'];
        
        $selectedOffice = null;
        if ($officeId) {
            $selectedOffice = Office::find($officeId);
        }

        return view('personnel.attendance.export-preview', compact(
            'records', 'departments', 'offices', 'designations', 'statuses', 'date',
            'user', 'roleName', 'employeeRecord', 'selectedOffice'
        ));
    }

    public function processLogs(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $this->attendanceService->processLogsForDate($date);

        return redirect()->back()->with('success', "Attendance processed for $date");
    }

    public function adjust(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        $employees = Employee::where('status', 'active')->get();

        return view('personnel.attendance.adjust', compact('employees', 'user', 'roleName', 'employeeRecord'));
    }

    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'in_time' => 'required|date_format:H:i',
            'out_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:50',
        ]);

        $date = $validated['date'];
        
        // Combine date and time string to full timestamp
        $validated['in_time'] = $date . ' ' . $validated['in_time'];
        if ($validated['out_time']) {
            $validated['out_time'] = $date . ' ' . $validated['out_time'];
            
            // Validate after_or_equal:in_time manually or using custom logic
            if (strtotime($validated['out_time']) < strtotime($validated['in_time'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['out_time' => 'The out time must be after or equal to in time.']);
            }
        }

        $validated['adjusted_by'] = \Illuminate\Support\Facades\Auth::id();
        $validated['status'] = 'approved';
        $validated['approved_by'] = \Illuminate\Support\Facades\Auth::id();

        ManualAttendanceAdjustment::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        // Reprocess for this employee
        $employee = Employee::find($validated['employee_id']);
        $this->attendanceService->processEmployeeAttendance($employee, $validated['date']);

        return redirect()->route('personnel.attendances.index', ['date' => $validated['date']])
            ->with('success', 'Manual adjustment saved and attendance recalculated.');
    }

    public function approvals(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        // Get all pending requests
        $adjustments = ManualAttendanceAdjustment::with(['employee.department', 'employee.designation'])
            ->where('status', 'pending')
            ->orderBy('date', 'desc')
            ->get();

        return view('personnel.attendance.approvals', compact('adjustments', 'user', 'roleName', 'employeeRecord'));
    }

    public function approveAdjustment(Request $request, $id)
    {
        $adjustment = ManualAttendanceAdjustment::findOrFail($id);
        
        $adjustment->status = 'approved';
        $adjustment->approved_by = \Illuminate\Support\Facades\Auth::id();
        $adjustment->save();

        NotificationService::clearNotificationsForSource($adjustment);

        $this->attendanceService->processEmployeeAttendance($adjustment->employee, Carbon::parse($adjustment->date)->format('Y-m-d'));

        return redirect()->back()->with('success', 'Adjustment approved and attendance recalculated.');
    }

    public function rejectAdjustment(Request $request, $id)
    {
        $request->validate(['reject_reason' => 'required|string|max:50']);
        $adjustment = ManualAttendanceAdjustment::findOrFail($id);
        
        $adjustment->status = 'rejected';
        $adjustment->reject_reason = $request->reject_reason;
        $adjustment->approved_by = \Illuminate\Support\Facades\Auth::id();
        $adjustment->save();

        NotificationService::clearNotificationsForSource($adjustment);

        return redirect()->back()->with('success', 'Adjustment request rejected.');
    }
    public function records(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        // 1. Get List of Employees for Search
        $employees = Employee::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'employee_code']);

        // 2. Process Filters
        $targetEmployeeId = $request->input('employee_id');
        $fromDateStr = $request->input('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDateStr   = $request->input('to_date', Carbon::now()->toDateString());
        $status      = $request->input('status');

        $selectedEmployee = null;
        $records = collect();
        $stats = [
            'totalPresent' => 0,
            'totalLate'    => 0,
            'totalAbsent'  => 0,
            'totalRecords' => 0
        ];

        if ($targetEmployeeId) {
            $selectedEmployee = Employee::with(['department', 'designation'])->find($targetEmployeeId);

            if ($selectedEmployee) {
                $fromDate = Carbon::parse($fromDateStr)->startOfDay();
                $toDate   = Carbon::parse($toDateStr)->endOfDay();

                // 3. Generate Date Sequence and Merge with Records (Detail logic from EmployeeAttendanceController)
                $allWorkingDates = [];
                $checkDate = $fromDate->copy();
                
                while ($checkDate->lte($toDate)) {
                    if ($this->attendanceService->isWorkingDay($selectedEmployee, $checkDate)) {
                        $allWorkingDates[] = $checkDate->toDateString();
                    }
                    $checkDate->addDay();
                }

                // Fetch approved leaves
                $approvedLeaves = LeaveApplication::where('employee_id', $selectedEmployee->id)
                    ->where('status', 'approved')
                    ->where(function($q) use ($fromDateStr, $toDateStr) {
                        $q->whereBetween('from_date', [$fromDateStr, $toDateStr])
                          ->orWhereBetween('to_date', [$fromDateStr, $toDateStr]);
                    })
                    ->get();

                // Fetch existing records
                $existingRecords = AttendanceRecord::where('employee_id', $selectedEmployee->id)
                    ->whereBetween('date', [$fromDateStr, $toDateStr])
                    ->get()
                    ->keyBy(function($item) {
                        return $item->date->format('Y-m-d');
                    });

                // Merge
                foreach (array_reverse($allWorkingDates) as $dateStr) {
                    if (isset($existingRecords[$dateStr])) {
                        $record = $existingRecords[$dateStr];
                    } else {
                        $carbonDate = Carbon::parse($dateStr);
                        $onLeave = $approvedLeaves->contains(function($leave) use ($carbonDate) {
                            return $carbonDate->between($leave->from_date, $leave->to_date);
                        });

                        $record = new AttendanceRecord([
                            'employee_id' => $selectedEmployee->id,
                            'date' => $dateStr,
                            'status' => $onLeave ? 'leave' : 'absent',
                            'late_seconds' => 0
                        ]);
                    }
                    
                    if (empty($status) || strtolower($record->status) == strtolower($status)) {
                        $records->push($record);
                    }
                }

                // Stats
                $stats['totalPresent'] = $records->whereIn('status', ['present', 'late'])->count();
                $stats['totalLate']    = $records->where('status', 'late')->count();
                $stats['totalAbsent']  = $records->where('status', 'absent')->count();
                $stats['totalRecords'] = $records->count();

                // Pagination (Simplified for now, can be manual as in EmployeeAttendanceController)
                $perPage = 15;
                $page = $request->input('page', 1);
                $records = new \Illuminate\Pagination\LengthAwarePaginator(
                    $records->forPage($page, $perPage),
                    $records->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            }
        }

        return view('personnel.attendance.records', compact(
            'employees', 'selectedEmployee', 'records', 
            'fromDateStr', 'toDateStr', 'status', 'stats',
            'user', 'roleName', 'employeeRecord'
        ));
    }

    public function exportMonthlyPreview(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $departmentId = $request->input('department_id');
        $officeId = $request->input('office_id');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $query = Employee::with(['department', 'designation', 'office', 'officeTime'])
            ->where('status', 'active');

        if ($officeId) {
            $query->where('office_id', $officeId);
        }
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        // Add Pagination for speed
        $employees = $query->orderBy('office_id')->orderBy('department_id')->paginate(30)->withQueryString();

        // Batch fetch data for current page employees
        $employeeIds = $employees->pluck('id');
        $attendanceData = AttendanceRecord::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('employee_id');

        $leavesData = LeaveApplication::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate->toDateString(), $endDate->toDateString()])
                  ->orWhereBetween('to_date', [$startDate->toDateString(), $endDate->toDateString()])
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->where('from_date', '<', $startDate->toDateString())
                         ->where('to_date', '>', $endDate->toDateString());
                  });
            })
            ->get()
            ->groupBy('employee_id');

        $holidays = Holiday::where('is_active', true)
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate->toDateString(), $endDate->toDateString()])
                  ->orWhereBetween('to_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->get();

        $weeklyHolidays = WeeklyHoliday::where('is_holiday', true)->get()->groupBy('office_id');
        $rosterSchedules = RosterSchedule::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('employee_id');
            
        $rosterTimes = RosterTime::all()->groupBy('group_slug');
        $groupSlugMap = AttendanceService::ROSTER_GROUP_SLUG_MAP;

        $processedData = [];
        foreach ($employees as $emp) {
            $empAttendance = $attendanceData->get($emp->id, collect())->keyBy(fn($r) => $r->date->format('j'));
            $empLeaves = $leavesData->get($emp->id, collect());
            $empRoster = $rosterSchedules->get($emp->id, collect())->keyBy(fn($s) => Carbon::parse($s->date)->format('j'));
            
            $days = [];
            $summary = ['P' => 0, 'A' => 0, 'LP' => 0, 'LA' => 0, 'L' => 0, 'H' => 0, 'WD' => 0];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $currentDate = Carbon::createFromDate($year, $month, $d);
                $dateStr = $currentDate->toDateString();
                $dayName = $currentDate->format('l');
                
                // Determine if working day (Optimized check)
                $isWorkingDay = true;
                if ($emp->officeTime && $emp->officeTime->shift_name === 'Roster') {
                    $sched = $empRoster->get($d);
                    if ($sched && $sched->shift_type) {
                        $groupSlug = $groupSlugMap[$emp->roster_group] ?? null;
                        $shift = $rosterTimes->get($groupSlug, collect())->where('shift_key', $sched->shift_type)->first();
                        $isWorkingDay = $shift && !$shift->is_off_day;
                    } else {
                        $isWorkingDay = false;
                    }
                } else {
                    $officeWH = $weeklyHolidays->get($emp->office_id) ?? $weeklyHolidays->get(null);
                    if ($officeWH && $officeWH->contains('day_name', $dayName)) {
                        $isWorkingDay = false;
                    } else {
                        $isGenHoliday = $holidays->first(function($h) use ($dateStr, $emp) {
                            return ($h->all_office || $h->office_id == $emp->office_id) && 
                                   $dateStr >= $h->from_date && $dateStr <= $h->to_date;
                        });
                        if ($isGenHoliday) $isWorkingDay = false;
                    }
                }

                $status = '';
                if (isset($empAttendance[$d])) {
                    $record = $empAttendance[$d];
                    if ($record->status === 'present') { $status = 'P'; $summary['P']++; }
                    elseif ($record->status === 'late') { $status = 'LP'; $summary['LP']++; }
                    elseif ($record->status === 'absent') { $status = 'A'; $summary['A']++; }
                    elseif ($record->status === 'leave') { $status = 'L'; $summary['L']++; }
                } else {
                    $onLeave = $empLeaves->first(function($leave) use ($dateStr) {
                        return $dateStr >= $leave->from_date && $dateStr <= $leave->to_date;
                    });

                    if ($onLeave) { $status = 'L'; $summary['L']++; }
                    elseif (!$isWorkingDay) { $status = 'H'; $summary['H']++; }
                    else { $status = 'A'; $summary['A']++; }
                }
                
                if ($isWorkingDay) $summary['WD']++;
                $days[$d] = $status;
            }

            $processedData[] = [
                'employee' => $emp,
                'days' => $days,
                'summary' => $summary
            ];
        }

        $departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => Department::all());
        $offices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => Office::all());

        $selectedOffice = null;
        if ($officeId) {
            $selectedOffice = Office::find($officeId);
        }

        return view('personnel.attendance.export-monthly-preview', compact(
            'processedData', 'departments', 'offices', 'month', 'year', 'daysInMonth', 'employees',
            'user', 'roleName', 'employeeRecord', 'selectedOffice'
        ));
    }

    public function exportMonthlyExcel(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);
        $params = $request->all();
        $params['format'] = 'excel';
        return Excel::download(new MonthlyAttendanceExport($params), 'monthly_attendance_' . date('Y-m') . '.xlsx');
    }

    public function exportMonthlyPdf(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);
        $params = $request->all();
        $params['format'] = 'pdf';
        
        $export = new MonthlyAttendanceExport($params);
        $view = $export->view();
        
        return PDF::loadView($view->name(), $view->getData())
            ->setPaper('a3', 'landscape')
            ->setOption('margin-bottom', 5)
            ->setOption('margin-top', 5)
            ->setOption('margin-left', 5)
            ->setOption('margin-right', 5)
            ->download('monthly_attendance_' . date('Y-m') . '.pdf');
    }

    public function exportMonthlyCsv(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);
        $params = $request->all();
        $params['format'] = 'csv';
        return Excel::download(new MonthlyAttendanceExport($params), 'monthly_attendance_' . date('Y-m') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportMonthlyWord(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);
        $params = $request->all();
        $params['format'] = 'word';
        $export = new MonthlyAttendanceExport($params);
        $view = $export->view();
        $filename = 'monthly_attendance_' . date('Y-m') . '.doc';

        return response($view->render())
            ->header('Content-Type', 'application/vnd.ms-word')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportYearlyPreview(Request $request)
    {
        $year = $request->input('year', now()->year);
        $departmentId = $request->input('department_id');
        $officeId = $request->input('office_id');

        $query = Employee::with(['department', 'designation', 'office', 'officeTime'])
            ->where('status', 'active');

        if ($officeId) {
            $query->where('office_id', $officeId);
        }
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->orderBy('office_id')->orderBy('department_id')->paginate(30)->withQueryString();
        $employeeIds = $employees->pluck('id');

        $attendance = AttendanceRecord::whereIn('employee_id', $employeeIds)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(['employee_id', function ($item) {
                return (int)$item->date->format('m');
            }]);

        $leaves = LeaveApplication::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where(function($q) use ($year) {
                $q->whereYear('from_date', $year)
                  ->orWhereYear('to_date', $year);
            })->get()
            ->groupBy('employee_id');

        $holidays = Holiday::where(function($q) use ($year) {
                $q->whereYear('from_date', $year)
                  ->orWhereYear('to_date', $year);
            })->get();

        $weeklyHolidays = WeeklyHoliday::where('is_holiday', true)->get()->groupBy('office_id');
        
        $rosterSchedules = RosterSchedule::whereIn('employee_id', $employeeIds)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(['employee_id', function ($item) {
                return (int)Carbon::parse($item->date)->format('m');
            }]);
            
        $rosterTimes = RosterTime::all()->groupBy('group_slug');
        $groupSlugMap = AttendanceService::ROSTER_GROUP_SLUG_MAP;

        $processedData = [];
        foreach ($employees as $emp) {
            $empAttendanceByMonth = $attendance->get($emp->id, collect());
            $empLeaves = $leaves->get($emp->id, collect());
            $empRosterByMonth = $rosterSchedules->get($emp->id, collect());

            $monthlySummaries = [];
            for ($m = 1; $m <= 12; $m++) {
                $summary = ['P' => 0, 'A' => 0, 'LP' => 0, 'LA' => 0, 'L' => 0, 'H' => 0, 'WD' => 0];
                $daysInMonth = Carbon::createFromDate($year, $m, 1)->daysInMonth;
                
                $monthAttendance = $empAttendanceByMonth->get($m, collect())->keyBy(fn($r) => (int)$r->date->format('d'));
                $monthRoster = $empRosterByMonth->get($m, collect())->keyBy(fn($s) => (int)Carbon::parse($s->date)->format('d'));

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $currentDate = Carbon::createFromDate($year, $m, $d);
                    $dateStr = $currentDate->toDateString();
                    $dayName = $currentDate->format('l');

                    $isWorkingDay = true;
                    if ($emp->officeTime && $emp->officeTime->shift_name === 'Roster') {
                        $sched = $monthRoster->get($d);
                        if ($sched && $sched->shift_type) {
                            $groupSlug = $groupSlugMap[$emp->roster_group] ?? null;
                            $shift = $rosterTimes->get($groupSlug, collect())->where('shift_key', $sched->shift_type)->first();
                            $isWorkingDay = $shift && !$shift->is_off_day;
                        } else {
                            $isWorkingDay = false;
                        }
                    } else {
                        $officeWH = $weeklyHolidays->get($emp->office_id) ?? $weeklyHolidays->get(null);
                        if ($officeWH && $officeWH->contains('day_name', $dayName)) {
                            $isWorkingDay = false;
                        } else {
                            $isGenHoliday = $holidays->first(function($h) use ($dateStr, $emp) {
                                return ($h->all_office || $h->office_id == $emp->office_id) && 
                                       $dateStr >= $h->from_date && $dateStr <= $h->to_date;
                            });
                            if ($isGenHoliday) $isWorkingDay = false;
                        }
                    }

                    if (isset($monthAttendance[$d])) {
                        $record = $monthAttendance[$d];
                        if ($record->status === 'present') $summary['P']++;
                        elseif ($record->status === 'late') $summary['LP']++;
                        elseif ($record->status === 'absent') $summary['A']++;
                        elseif ($record->status === 'leave') $summary['L']++;
                    } else {
                        $onLeave = $empLeaves->first(fn($l) => $dateStr >= $l->from_date && $dateStr <= $l->to_date);
                        if ($onLeave) $summary['L']++;
                        elseif (!$isWorkingDay) $summary['H']++;
                        else $summary['A']++;
                    }
                    if ($isWorkingDay) $summary['WD']++;
                }
                $monthlySummaries[$m] = $summary;
            }

            $processedData[] = [
                'employee' => $emp,
                'monthlySummaries' => $monthlySummaries
            ];
        }

        $departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => Department::all());
        $offices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => Office::all());

        $selectedOffice = null;
        if ($officeId) {
            $selectedOffice = Office::find($officeId);
        }

        return view('personnel.attendance.export-yearly-preview', compact(
            'processedData', 'departments', 'offices', 'year', 'employees', 'selectedOffice'
        ));
    }

    public function exportYearlyExcel(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $params = $request->all();
        $params['year'] = $params['year'] ?? now()->year;
        $params['format'] = 'excel';
        return Excel::download(new YearlyAttendanceExport($params), 'yearly_attendance_' . $params['year'] . '.xlsx');
    }

    public function exportYearlyPdf(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $params = $request->all();
        $params['year'] = $params['year'] ?? now()->year;
        $params['format'] = 'pdf';
        
        $export = new YearlyAttendanceExport($params);
        $view = $export->view();
        
        return PDF::loadView($view->name(), $view->getData())
            ->setPaper('a4', 'portrait')
            ->setOption('margin-bottom', 5)
            ->setOption('margin-top', 5)
            ->setOption('margin-left', 5)
            ->setOption('margin-right', 5)
            ->download('yearly_attendance_' . $params['year'] . '.pdf');
    }

    public function exportYearlyCsv(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $params = $request->all();
        $params['year'] = $params['year'] ?? now()->year;
        $params['format'] = 'csv';
        return Excel::download(new YearlyAttendanceExport($params), 'yearly_attendance_' . $params['year'] . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportYearlyWord(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);
        $params = $request->all();
        $params['year'] = $params['year'] ?? now()->year;
        $params['format'] = 'word';
        $export = new YearlyAttendanceExport($params);
        $view = $export->view();
        $filename = 'yearly_attendance_' . $params['year'] . '.doc';

        return response($view->render())
            ->header('Content-Type', 'application/vnd.ms-word')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportLogPreview(Request $request)
    {
        $employees = Employee::with(['department', 'designation'])->orderBy('name')->get();
        $selectedEmployeeId = $request->input('employee_id');
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $records = collect();
        $selectedEmployee = null;
        if ($selectedEmployeeId) {
            $selectedEmployee = Employee::with(['department', 'designation', 'office'])->find($selectedEmployeeId);
            $params = [
                'employee_id' => $selectedEmployeeId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'format' => 'preview'
            ];
            $export = new EmployeeLogExport($params);
            $view = $export->view();
            $records = $view->getData()['records'];
        }

        return view('personnel.attendance.export-log-preview', compact(
            'employees', 'selectedEmployeeId', 'selectedEmployee', 'fromDate', 'toDate', 'records'
        ));
    }

    public function exportLogExcel(Request $request)
    {
        $params = $request->all();
        $params['format'] = 'excel';
        $filename = 'employee_log_' . $params['employee_id'] . '_' . date('Ymd') . '.xlsx';
        return Excel::download(new EmployeeLogExport($params), $filename);
    }

    public function exportLogCsv(Request $request)
    {
        $params = $request->all();
        $params['format'] = 'csv';
        $filename = 'employee_log_' . $params['employee_id'] . '_' . date('Ymd') . '.csv';
        return Excel::download(new EmployeeLogExport($params), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportLogPdf(Request $request)
    {
        $params = $request->all();
        $params['format'] = 'pdf';
        $export = new EmployeeLogExport($params);
        $view = $export->view();
        
        return PDF::loadView($view->name(), $view->getData())
            ->setPaper('a4', 'portrait')
            ->download('employee_log_' . $params['employee_id'] . '.pdf');
    }

    public function exportLogWord(Request $request)
    {
        $params = $request->all();
        $params['format'] = 'word';
        $export = new EmployeeLogExport($params);
        $view = $export->view();
        $filename = 'employee_log_' . $params['employee_id'] . '.doc';

        return response($view->render())
            ->header('Content-Type', 'application/vnd.ms-word')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
