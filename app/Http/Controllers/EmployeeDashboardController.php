<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\Holiday;
use App\Models\Notice;
use App\Services\AttendanceService;
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
        
        $prevPresentDays = 0;
        $prevLateDays = 0;
        $prevAbsentDays = 0;
        $prevTotalWorkingDays = 0;
        $prevLeaveDays = 0;

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

            // Previous month attendance breakdown
            $startOfLastMonth = $today->copy()->subMonth()->startOfMonth();
            $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();
            
            $prevAttendanceRecords = AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
                ->get();
                
            $prevPresentDays = $prevAttendanceRecords->whereIn('status', ['present', 'late'])->count();
            $prevLateDays = $prevAttendanceRecords->where('status', 'late')->count();
            
            $prevTotalWorkingDays = 0;
            $checkDate = $startOfLastMonth->copy();
            while ($checkDate->lte($endOfLastMonth)) {
                if (!in_array($checkDate->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY])) {
                    $prevTotalWorkingDays++;
                }
                $checkDate->addDay();
            }
            
            $prevLeaveDays = LeaveApplication::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($startOfLastMonth, $endOfLastMonth) {
                    $q->whereBetween('from_date', [$startOfLastMonth, $endOfLastMonth])
                      ->orWhereBetween('to_date', [$startOfLastMonth, $endOfLastMonth]);
                })
                ->sum('total_days');
                
            $prevAbsentDays = max(0, $prevTotalWorkingDays - $prevPresentDays - $prevLeaveDays);

            // Recent attendance records (last 7 working days)
            $existingRecords = AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('date', [$today->copy()->subDays(14), $today])
                ->get()
                ->keyBy(function($item) {
                    return $item->date->format('Y-m-d');
                });
 
            // Fetch approved leaves for the same window
            $approvedLeavesWindow = LeaveApplication::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function($q) use ($today) {
                    $startOfWindow = $today->copy()->subDays(14);
                    $q->whereBetween('from_date', [$startOfWindow, $today])
                      ->orWhereBetween('to_date', [$startOfWindow, $today])
                      ->orWhere(function($sub) use ($startOfWindow, $today) {
                          $sub->where('from_date', '<=', $startOfWindow)
                              ->where('to_date', '>=', $today);
                      });
                })
                ->get();

            $recentAttendance = collect();
            $checkDate = $today->copy();
            while ($recentAttendance->count() < 7 && $checkDate->gte($today->copy()->subDays(30))) {
                $dateStr = $checkDate->toDateString();
                if (isset($existingRecords[$dateStr])) {
                    $recentAttendance->push($existingRecords[$dateStr]);
                } elseif ($this->attendanceService->isWorkingDay($employee, $checkDate)) {
                    $onLeave = $approvedLeavesWindow->contains(function($leave) use ($checkDate) {
                        return $checkDate->between($leave->from_date, $leave->to_date);
                    });
                    $recentAttendance->push(new AttendanceRecord([
                        'employee_id' => $employee->id,
                        'date' => $dateStr,
                        'status' => $onLeave ? 'leave' : 'absent',
                        'late_seconds' => 0
                    ]));
                }
                $checkDate->subDay();
            }

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

        // Upcoming Birthdays
        $upcomingBirthdays = Employee::whereNotNull('date_of_birth')
            ->where('status', 'active')
            ->get()
            ->map(function ($employee) use ($today) {
                $birthday = Carbon::parse($employee->date_of_birth);
                $birthdayThisYear = $birthday->copy()->year($today->year);
                
                if ($birthdayThisYear->isBefore($today) && !$birthdayThisYear->isSameDay($today)) {
                    $birthdayThisYear->addYear();
                }
                
                $employee->days_until_birthday = $today->diffInDays($birthdayThisYear);
                $employee->next_birthday = $birthdayThisYear;
                return $employee;
            })
            ->sortBy('days_until_birthday')
            ->take(3);

        return view('employee-dashboard', compact(
            'user',
            'roleName',
            'employee',
            'presentDays',
            'lateDays',
            'absentDays',
            'totalWorkingDays',
            'prevPresentDays',
            'prevLateDays',
            'prevAbsentDays',
            'prevTotalWorkingDays',
            'recentAttendance',
            'approvedLeaves',
            'pendingLeaves',
            'rejectedLeaves',
            'totalLeaveDays',
            'totalUsedLeave',
            'totalAvailableLeave',
            'upcomingHolidays',
            'upcomingBirthdays',
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
