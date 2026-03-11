<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\Holiday;
use App\Models\Notice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeDashboardController extends Controller
{
    /**
     * Display the Employee dashboard.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        // An Employee might have an associated Employee record
        $employee = Employee::where('user_id', $user->id)->first();

        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();
        $startOfYear = $today->copy()->startOfYear();

        // Default values
        $presentDays = 0;
        $lateDays = 0;
        $absentDays = 0;
        $totalWorkingDays = 0;
        $recentAttendance = collect();
        $approvedLeaves = 0;
        $pendingLeaves = 0;
        $rejectedLeaves = 0;
        $totalLeaveDays = 0;

        if ($employee) {
            // Attendance data for current month
            $attendanceRecords = AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $today])
                ->get();

            $presentDays = $attendanceRecords->whereIn('status', ['present', 'late'])->count();
            $lateDays = $attendanceRecords->where('status', 'late')->count();

            // Calculate working days (exclude weekends - Fri/Sat for BD, or Sat/Sun)
            $totalWorkingDays = 0;
            $checkDate = $startOfMonth->copy();
            while ($checkDate->lte($today)) {
                // Exclude Friday and Saturday (Bangladesh weekend)
                if (!in_array($checkDate->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY])) {
                    $totalWorkingDays++;
                }
                $checkDate->addDay();
            }

            // Approved leaves this month
            $leaveDaysThisMonth = LeaveApplication::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($startOfMonth, $today) {
                    $q->whereBetween('from_date', [$startOfMonth, $today])
                      ->orWhereBetween('to_date', [$startOfMonth, $today]);
                })
                ->sum('total_days');

            $absentDays = max(0, $totalWorkingDays - $presentDays - $leaveDaysThisMonth);

            // Recent attendance records (last 7)
            $recentAttendance = AttendanceRecord::where('employee_id', $employee->id)
                ->orderBy('date', 'desc')
                ->take(7)
                ->get();

            // Leave data for current year
            $leaveApplications = LeaveApplication::where('employee_id', $employee->id)
                ->whereYear('from_date', $today->year)
                ->get();

            $approvedLeaves = $leaveApplications->where('status', 'approved')->sum('total_days');
            $pendingLeaves = $leaveApplications->where('status', 'pending')->sum('total_days');
            $rejectedLeaves = $leaveApplications->where('status', 'rejected')->sum('total_days');
            $totalLeaveDays = $approvedLeaves + $pendingLeaves + $rejectedLeaves;

            // Leave Balance summary (Used vs Available)
            $leaveBalances = LeaveBalance::where('employee_id', $employee->id)
                ->where('year', $today->year)
                ->get();
            
            $totalUsedLeave = $leaveBalances->sum('used_days');
            $totalAvailableLeave = $leaveBalances->sum('remaining_days');
        } else {
            $totalUsedLeave = 0;
            $totalAvailableLeave = 0;
        }

        // Active Notices & Events
        $activeNotices = Notice::active()->orderBy('created_at', 'desc')->get();

        // Upcoming Holidays (same as HR Dashboard)
        $upcomingHolidays = Holiday::whereDate('from_date', '>=', $today)
            ->where('is_active', true)
            ->orderBy('from_date', 'asc')
            ->take(5)
            ->get();

        return view('employee-dashboard', compact(
            'user',
            'roleName',
            'employee',
            'presentDays',
            'lateDays',
            'absentDays',
            'totalWorkingDays',
            'recentAttendance',
            'approvedLeaves',
            'pendingLeaves',
            'rejectedLeaves',
            'totalLeaveDays',
            'totalUsedLeave',
            'totalAvailableLeave',
            'upcomingHolidays',
            'activeNotices'
        ));
    }

    /**
     * Display the dedicated Employee Profile.
     */
    public function profile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';

        // Load employee with all necessary relationships for the profile view
        $employee = Employee::where('user_id', $user->id)
            ->with(['department', 'section', 'designation', 'grade', 'officeTime', 'reportingManager'])
            ->first();

        return view('personnel.employees.profile', compact(
            'user',
            'roleName',
            'employee'
        ));
    }
}
