<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Section;
use App\Models\AttendanceRecord;
use App\Models\LeaveApplication;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrDashboardController extends Controller
{
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

        $recentAttendance = AttendanceRecord::with('employee')
            ->whereDate('date', $today)
            ->whereIn('status', ['present', 'late'])
            ->orderBy('created_at', 'desc')
            ->take(7)
            ->get();

        $upcomingHolidays = Holiday::whereDate('from_date', '>=', $today)
            ->where('is_active', true)
            ->orderBy('from_date', 'asc')
            ->take(5)
            ->get();

        $totalEmployees = Employee::count();
        $totalDepartments = Department::count();
        $totalSections = Section::count();

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
            'upcomingHolidays'
        ));
    }
}
