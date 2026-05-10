<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\AttendanceRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\Holiday;
use App\Models\Notice;
use App\Models\SupervisorRemark;
use App\Models\RosterSchedule;
use App\Models\RosterTime;
use App\Services\AttendanceService;
use App\Exports\PersonalRosterExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display the Employee dashboard.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::with(['designation', 'department'])->where('user_id', $user->id)->first();
        $today = Carbon::today();

        // Default empty variables for guests/unassigned users
        $data = [
            'user' => $user,
            'roleName' => $roleName,
            'employee' => $employee,
            'presentDays' => 0, 'lateDays' => 0, 'absentDays' => 0, 'totalWorkingDays' => 0, 'overtimeHours' => 0, 'isOtEligible' => false,
            'prevPresentDays' => 0, 'prevLateDays' => 0, 'prevAbsentDays' => 0, 'prevTotalWorkingDays' => 0,
            'fullMonthAttendance' => collect(),
            'supervisorRemarks' => collect(),
            'approvedLeaves' => 0, 'pendingLeaves' => 0, 'rejectedLeaves' => 0, 'totalLeaveDays' => 0,
            'totalUsedLeave' => 0, 'totalAvailableLeave' => 0,
            'leaveBalances' => collect(),
            'pendingTeamLeavesCount' => 0,
            'isReportingManager' => false,
            'myRoster' => null,
            'shiftDefinitions' => collect()
        ];

        if ($employee) {
            $data = array_merge($data, $this->getAttendanceMetrics($employee, $today));
            $data = array_merge($data, $this->getPreviousMonthMetrics($employee, $today));
            $data['fullMonthAttendance'] = $this->getAttendanceHistory($employee, $today);
            $data = array_merge($data, $this->getLeaveSummary($employee, $today));
            
            $data['supervisorRemarks'] = SupervisorRemark::active()
                ->where('employee_id', $employee->id)
                ->with('supervisor')
                ->latest()->take(5)->get();

            $data = array_merge($data, $this->getTeamManagementMetrics($employee, $roleName));

            if ($employee->officeTime && $employee->officeTime->shift_name === 'Roster') {
                $rosterData = $this->getMyRosterData($employee, $today);
                $data['myRoster'] = $rosterData['schedule'];
                $data['shiftDefinitions'] = $rosterData['definitions'];
                $data['rosterStart'] = $rosterData['startOfWeek'];
                $data['rosterEnd'] = $rosterData['endOfWeek'];
            }
        }

        $data = array_merge($data, $this->getDashboardCommonData($today, $employee));

        return view('employee-dashboard', $data);
    }

    private function getAttendanceMetrics(Employee $employee, Carbon $today): array
    {
        $startOfMonth = $today->copy()->startOfMonth();
        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $today])
            ->get();

        $presentDays = $records->whereIn('status', ['present', 'late'])->count();
        $lateDays = $records->where('status', 'late')->count();
        $totalWorkingDays = 0;

        $checkDate = $startOfMonth->copy();
        while ($checkDate->lte($today)) {
            if ($this->attendanceService->isWorkingDay($employee, $checkDate)) {
                $totalWorkingDays++;
            }
            $checkDate->addDay();
        }

        $leaveDays = LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startOfMonth, $today) {
                $q->whereBetween('from_date', [$startOfMonth, $today])
                  ->orWhereBetween('to_date', [$startOfMonth, $today]);
            })->sum('total_days');

        $overtimeHours = \App\Models\Overtime::where('employee_id', $employee->id)
            ->whereYear('date', $today->year)
            ->whereMonth('date', $today->month)
            ->sum('total_ot_hours');

        return [
            'presentDays' => $presentDays,
            'lateDays' => $lateDays,
            'totalWorkingDays' => $totalWorkingDays,
            'absentDays' => max(0, $totalWorkingDays - $presentDays - $leaveDays),
            'overtimeHours' => $overtimeHours,
            'isOtEligible' => $employee->designation->is_ot_eligible ?? false,
        ];
    }

    private function getPreviousMonthMetrics(Employee $employee, Carbon $today): array
    {
        $startOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();
        
        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->get();
            
        $present = $records->whereIn('status', ['present', 'late'])->count();
        $workingDays = 0;

        $checkDate = $startOfLastMonth->copy();
        while ($checkDate->lte($endOfLastMonth)) {
            if ($this->attendanceService->isWorkingDay($employee, $checkDate)) {
                $workingDays++;
            }
            $checkDate->addDay();
        }
        
        $leaveDays = LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startOfLastMonth, $endOfLastMonth) {
                $q->whereBetween('from_date', [$startOfLastMonth, $endOfLastMonth])
                  ->orWhereBetween('to_date', [$startOfLastMonth, $endOfLastMonth]);
            })->sum('total_days');

        return [
            'prevPresentDays' => $present,
            'prevLateDays' => $records->where('status', 'late')->count(),
            'prevTotalWorkingDays' => $workingDays,
            'prevAbsentDays' => max(0, $workingDays - $present - $leaveDays)
        ];
    }

    private function getAttendanceHistory(Employee $employee, Carbon $today)
    {
        $startOfMonth = $today->copy()->startOfMonth();
        $existingRecords = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $today])
            ->get()->keyBy(fn($item) => Carbon::parse($item->date)->toDateString());

        $approvedLeaves = LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function($q) use ($startOfMonth, $today) {
                $q->whereBetween('from_date', [$startOfMonth, $today])
                  ->orWhereBetween('to_date', [$startOfMonth, $today])
                  ->orWhere(fn($sub) => $sub->where('from_date', '<=', $startOfMonth)->where('to_date', '>=', $today));
            })->get();

        $history = collect();
        $checkDate = $today->copy();
        while ($checkDate->gte($startOfMonth)) {
            $dateStr = $checkDate->toDateString();
            if (isset($existingRecords[$dateStr])) {
                $history->push($existingRecords[$dateStr]);
            } else {
                $dateStatus = $this->attendanceService->getDateAttendanceStatus($employee, $checkDate);
                $onLeave = $approvedLeaves->contains(fn($l) => $checkDate->between($l->from_date, $l->to_date));
                
                if ($dateStatus === 'working_day' || $onLeave) {
                    $history->push(new AttendanceRecord([
                        'employee_id' => $employee->id, 'date' => $dateStr,
                        'status' => $onLeave ? 'leave' : 'absent', 'late_seconds' => 0
                    ]));
                } else {
                    $history->push(new AttendanceRecord([
                        'employee_id' => $employee->id, 'date' => $dateStr,
                        'status' => $dateStatus, 'late_seconds' => 0
                    ]));
                }
            }
            $checkDate->subDay();
        }
        return $history;
    }

    private function getLeaveSummary(Employee $employee, Carbon $today): array
    {
        $apps = LeaveApplication::where('employee_id', $employee->id)
            ->whereYear('from_date', $today->year)->get();
        
        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', $today->year)->get();

        return [
            'approvedLeaves' => $apps->where('status', 'approved')->sum('total_days'),
            'pendingLeaves' => $apps->where('status', 'pending')->sum('total_days'),
            'rejectedLeaves' => $apps->where('status', 'rejected')->sum('total_days'),
            'totalLeaveDays' => $apps->sum('total_days'),
            'totalUsedLeave' => $balances->sum('used_days'),
            'totalAvailableLeave' => $balances->sum('remaining_days'),
            'leaveBalances' => $balances
        ];
    }

    private function getTeamManagementMetrics(Employee $employee, string $roleName): array
    {
        $isReportingManager = Employee::where('reporting_manager_id', $employee->id)->exists();
        $pendingCount = 0;

        if ($roleName === 'Team Lead' || $isReportingManager) {
            $inchargeDeptIds = Department::where('incharge_id', $employee->id)->pluck('id');
            $teamIds = Employee::where('reporting_manager_id', $employee->id)
                ->orWhereIn('department_id', $inchargeDeptIds)->pluck('id');

            $pendingCount = LeaveApplication::whereIn('employee_id', $teamIds)
                ->where('employee_id', '!=', $employee->id)
                ->where('status', 'pending')->count();
        }

        return [
            'isReportingManager' => $isReportingManager,
            'pendingTeamLeavesCount' => $pendingCount
        ];
    }

    private function getDashboardCommonData(Carbon $today, ?Employee $employee): array
    {
        return [
            'activeNotices' => Notice::active()->orderBy('created_at', 'desc')->take(5)->get(),
            'upcomingHolidays' => Holiday::whereDate('from_date', '>=', $today)
                ->whereYear('from_date', $today->year)
                ->where('is_active', true)->orderBy('from_date', 'asc')->get(),
            'upcomingBirthdays' => Employee::whereNotNull('date_of_birth')->where('status', 'active')->get()
                ->map(function ($emp) use ($today) {
                    $bday = Carbon::parse($emp->date_of_birth);
                    $thisYear = $bday->copy()->year($today->year);
                    if ($thisYear->isBefore($today) && !$thisYear->isSameDay($today)) $thisYear->addYear();
                    $emp->days_until_birthday = $today->diffInDays($thisYear);
                    $emp->next_birthday = $thisYear;
                    return $emp;
                })->sortBy('days_until_birthday')->take(3)
        ];
    }

    /**
     * Fetch roster data for the personal dashboard.
     */
    private function getMyRosterData(Employee $employee, Carbon $today): array
    {
        // Resolve group slug
        $groupSlug = AttendanceService::ROSTER_GROUP_SLUG_MAP[$employee->roster_group] ?? null;

        // Roster week/month logic
        if ($groupSlug === 'drivers') {
            $startView = $today->copy()->startOfMonth();
            $endView = $today->copy()->endOfMonth();
        } else {
            // Roster week starts on Saturday (6)
            $startView = $today->copy()->startOfWeek(6);
            $endView = $startView->copy()->addDays(6);
        }
        
        $startOfWeek = $startView; 
        $endOfWeek = $endView;
        
        if (!$groupSlug) {
            return [
                'schedule' => collect(), 
                'definitions' => collect(),
                'startOfWeek' => $startOfWeek,
                'endOfWeek' => $endOfWeek
            ];
        }

        // Fetch shift definitions
        $definitions = RosterTime::where('group_slug', $groupSlug)
            ->get()
            ->keyBy('shift_key');

        // Fetch schedules for the requested range
        $schedules = RosterSchedule::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->date)->toDateString());

        return [
            'schedule' => $schedules,
            'definitions' => $definitions,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek
        ];
    }

    /**
     * Download personal roster as Excel.
     */
    public function downloadRoster(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        $today = Carbon::today();
        $format = $request->query('format', 'xlsx');

        if (!$employee || !$employee->officeTime || $employee->officeTime->shift_name !== 'Roster') {
            return redirect()->back()->with('error', 'Roster schedule not found.');
        }

        $rosterData = $this->getMyRosterData($employee, $today);
        $data = [
            'employee' => $employee,
            'monthStart' => $today->copy()->startOfMonth(),
            'monthEnd' => $today->copy()->endOfMonth(),
            'myRoster' => $rosterData['schedule'],
            'shiftDefinitions' => $rosterData['definitions'],
        ];

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $exportFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        
        $fileName = 'Roster_' . str_replace(' ', '_', $employee->name) . '_' . $today->format('Y-m') . '.' . $extension;

        return Excel::download(new PersonalRosterExport($data), $fileName, $exportFormat);
    }

    /**
     * Display the dedicated Employee Profile.
     */
    public function profile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        // Load employee with all necessary relationships
        $employee = Employee::where('user_id', $user->id)
            ->with(['department', 'section', 'designation', 'grade', 'officeTime', 'reportingManager'])
            ->first();

        return view('personnel.employees.profile', compact('user', 'roleName', 'employee'));
    }
}
