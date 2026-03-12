<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Section;
use App\Models\AttendanceRecord;
use App\Models\LeaveApplication;
use App\Models\Holiday;
use App\Models\Notice;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrDashboardController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
    /**
     * Display the HR dashboard.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        if ($roleName !== 'HR Admin') {
            abort(403, 'Unauthorized action. Only HR Admins can access this dashboard.');
        }

        $employee = Employee::where('user_id', $user->id)->first();
        $today = Carbon::today();

        // Status Metrics for Today
        $activeEmployeesCount = Employee::where('status', 'active')->count();
        $presentToday = AttendanceRecord::whereDate('date', $today)
            ->whereIn('status', ['present', 'late'])
            ->distinct('employee_id')
            ->count();
        $lateToday = AttendanceRecord::whereDate('date', $today)
            ->where('status', 'late')
            ->distinct('employee_id')
            ->count();
        $onLeaveToday = LeaveApplication::where('status', 'approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->count();
        $absentToday = max(0, $activeEmployeesCount - ($presentToday + $onLeaveToday));

        // Summaries
        $pendingLeavesCount = LeaveApplication::where('status', 'pending')->count();

        // Recent Attendance: Show all active employees for today
        $activeEmployees = Employee::where('status', 'active')->get();
        $todayAttendance = AttendanceRecord::whereDate('date', $today)
            ->get()
            ->keyBy('employee_id');
 
        // Fetch approved leaves for today
        $approvedLeavesToday = LeaveApplication::where('status', 'approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->get()
            ->keyBy('employee_id');

        $recentAttendance = collect();
        foreach ($activeEmployees as $emp) {
            if (isset($todayAttendance[$emp->id])) {
                $recentAttendance->push($todayAttendance[$emp->id]);
            } else {
                // Check if today is a working day for this employee
                if ($this->attendanceService->isWorkingDay($emp, $today)) {
                    $onLeave = isset($approvedLeavesToday[$emp->id]);
                    $absentRecord = new AttendanceRecord([
                        'employee_id' => $emp->id,
                        'date' => $today->toDateString(),
                        'status' => $onLeave ? 'leave' : 'absent',
                        'late_seconds' => 0
                    ]);
                    $absentRecord->setRelation('employee', $emp);
                    $recentAttendance->push($absentRecord);
                }
            }
        }

        // Sort by status (Present/Late first, then Absent) and take 7
        $recentAttendance = $recentAttendance->sortBy(function ($record) {
            return in_array($record->status, ['present', 'late']) ? 0 : 1;
        })->take(7);

        $upcomingHolidays = Holiday::whereDate('from_date', '>=', $today)
            ->where('is_active', true)
            ->orderBy('from_date', 'asc')
            ->take(5)
            ->get();

        $totalEmployees = Employee::count();
        $totalDepartments = Department::count();
        $totalSections = Section::count();

        // Active Notices & Events
        $activeNotices = Notice::active()->orderBy('created_at', 'desc')->get();

        return view('hr-dashboard', compact(
            'user',
            'roleName',
            'employee',
            'totalEmployees',
            'activeEmployeesCount',
            'totalDepartments',
            'totalSections',
            'presentToday',
            'absentToday',
            'lateToday',
            'onLeaveToday',
            'pendingLeavesCount',
            'recentAttendance',
            'upcomingHolidays',
            'activeNotices'
        ));
    }
}
