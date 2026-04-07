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
        $employee = Employee::with(['designation', 'department'])->where('user_id', $user->id)->first();

        $today = Carbon::today();
        
        // Initialize all variables with default values to ensure they are always present for compact()
        $presentDays = 0;
        $lateDays = 0;
        $absentDays = 0;
        $totalWorkingDays = 0;
        $fullMonthAttendance = collect();
        $approvedLeaves = 0;
        $pendingLeaves = 0;
        $rejectedLeaves = 0;
        $totalLeaveDays = 0;
        $leaveBalances = collect();
        $supervisorRemarks = collect();
        $totalAvailableLeave = 0;
        $pendingTeamLeavesCount = 0;
        
        $prevPresentDays = 0;
        $prevLateDays = 0;
        $prevAbsentDays = 0;
        $prevTotalWorkingDays = 0;

        if ($employee) {
            $startOfMonth = $today->copy()->startOfMonth();
            
            // Attendance data for current month
            $attendanceRecords = AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $today])
                ->get();

            $presentDays = $attendanceRecords->whereIn('status', ['present', 'late'])->count();
            $lateDays = $attendanceRecords->where('status', 'late')->count();

            // Calculate working days (using project's holiday configuration)
            $checkDate = $startOfMonth->copy();
            while ($checkDate->lte($today)) {
                if ($this->attendanceService->isWorkingDay($employee, $checkDate)) {
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
                if ($this->attendanceService->isWorkingDay($employee, $checkDate)) {
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

            // Full attendance records for current month
            $existingRecords = AttendanceRecord::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $today])
                ->get()
                ->keyBy(function($item) {
                    return $item->date->format('Y-m-d');
                });
 
            // Fetch approved leaves for the current month window
            $approvedLeavesWindow = LeaveApplication::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function($q) use ($startOfMonth, $today) {
                    $q->whereBetween('from_date', [$startOfMonth, $today])
                      ->orWhereBetween('to_date', [$startOfMonth, $today])
                      ->orWhere(function($sub) use ($startOfMonth, $today) {
                          $sub->where('from_date', '<=', $startOfMonth)
                              ->where('to_date', '>=', $today);
                      });
                })
                ->get();

            $checkDate = $today->copy();
            while ($checkDate->gte($startOfMonth)) {
                $dateStr = $checkDate->toDateString();
                if (isset($existingRecords[$dateStr])) {
                    $fullMonthAttendance->push($existingRecords[$dateStr]);
                } elseif ($this->attendanceService->isWorkingDay($employee, $checkDate)) {
                    $onLeave = $approvedLeavesWindow->contains(function($leave) use ($checkDate) {
                        return $checkDate->between($leave->from_date, $leave->to_date);
                    });
                    $fullMonthAttendance->push(new AttendanceRecord([
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

            // Leave Balance summary
            $leaveBalances = LeaveBalance::with('leaveType')
                ->where('employee_id', $employee->id)
                ->where('year', $today->year)
                ->get();
            
            $totalUsedLeave = $leaveBalances->sum('used_days');
            $totalAvailableLeave = $leaveBalances->sum('remaining_days');

            // Fetch supervisor remarks (active only)
            $supervisorRemarks = SupervisorRemark::active()
                ->where('employee_id', $employee->id)
                ->with('supervisor')
                ->latest()
                ->take(5)
                ->get();

            // Calculate pending team leaves for Team Leads / Department Heads / Reporting Managers
            $isReportingManager = Employee::where('reporting_manager_id', $employee->id)->exists();

            if ($roleName === 'Team Lead' || $isReportingManager) {
                $inchargeDeptIds = Department::where('incharge_id', $employee->id)->pluck('id');
                $teamEmployeeIds = Employee::where('reporting_manager_id', $employee->id)
                    ->orWhereIn('department_id', $inchargeDeptIds)
                    ->pluck('id');

                $pendingTeamLeavesCount = LeaveApplication::whereIn('employee_id', $teamEmployeeIds)
                    ->where('employee_id', '!=', $employee->id) // Skip self
                    ->where('status', 'pending')
                    ->count();
            }
        }

        // Active Notices & Events (at max 5)
        $activeNotices = Notice::active()->orderBy('created_at', 'desc')->take(5)->get();

        // Upcoming Holidays
        $upcomingHolidays = Holiday::whereDate('from_date', '>=', $today)
            ->where('is_active', true)
            ->orderBy('from_date', 'asc')
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
            'fullMonthAttendance',
            'supervisorRemarks',
            'approvedLeaves',
            'pendingLeaves',
            'rejectedLeaves',
            'totalLeaveDays',
            'totalUsedLeave',
            'totalAvailableLeave',
            'leaveBalances',
            'pendingTeamLeavesCount',
            'upcomingHolidays',
            'upcomingBirthdays',
            'activeNotices',
            'isReportingManager'
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

        // Load employee with all necessary relationships
        $employee = Employee::where('user_id', $user->id)
            ->with(['department', 'section', 'designation', 'grade', 'officeTime', 'reportingManager'])
            ->first();

        return view('personnel.employees.profile', compact('user', 'roleName', 'employee'));
    }
}
