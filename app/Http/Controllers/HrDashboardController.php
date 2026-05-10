<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Section;
use App\Models\AttendanceRecord;
use App\Models\LeaveApplication;
use App\Models\Holiday;
use App\Models\Notice;
use App\Models\Grade;
use App\Models\Office;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        $employee = Employee::query()->where('user_id', $user->id)->first();
        $today = Carbon::today();

        // Basic Counts & Metrics
        $activeEmployeesCount = Employee::query()->where('status', 'active')->count();
        $pendingLeavesCount = LeaveApplication::query()->where('status', 'pending')->count();
        
        // Extracted Metrics & Data
        $metrics = $this->getStatusMetrics($today, $activeEmployeesCount);
        $activeEmployees = Employee::query()->where('status', 'active')->get();
        $recentAttendance = $this->getRecentAttendance($today, $activeEmployees);
        $upcomingBirthdays = $this->getUpcomingBirthdays($today);

        // Core Data
        $upcomingHolidays = Holiday::query()->whereDate('from_date', '>=', $today)
            ->whereYear('from_date', $today->year)
            ->where('is_active', true)
            ->orderBy('from_date', 'asc')
            ->get();

        $totalEmployees = $activeEmployeesCount;
        $totalDepartments = Department::query()->count();
        $totalSections = Section::query()->count();
        $totalGrades = Grade::query()->count();
        $totalOffices = Office::query()->count();
        $activeNotices = Notice::query()->active()->orderBy('created_at', 'desc')->get();
        // Office chart data
        $officeAttendanceData = $this->getOfficeAttendanceData($today, $activeEmployees);

        return view('hr-dashboard', array_merge($metrics, compact(
            'user',
            'roleName',
            'employee',
            'totalEmployees',
            'activeEmployeesCount',
            'totalDepartments',
            'totalSections',
            'totalGrades',
            'totalOffices',
            'pendingLeavesCount',
            'recentAttendance',
            'upcomingHolidays',
            'upcomingBirthdays',
            'activeNotices',
            'officeAttendanceData'
        )));
    }

    /**
     * Get status metrics for today (Present, Late, On Leave, Absent).
     */
    private function getStatusMetrics(Carbon $today, int $activeEmployeesCount): array
    {
        $presentToday = AttendanceRecord::query()->whereDate('date', $today)
            ->whereHas('employee', function($q) {
                $q->where('status', 'active');
            })
            ->whereIn('status', ['present', 'late'])
            ->distinct('employee_id')
            ->count();

        $lateToday = AttendanceRecord::query()->whereDate('date', $today)
            ->whereHas('employee', function($q) {
                $q->where('status', 'active');
            })
            ->where('status', 'late')
            ->distinct('employee_id')
            ->count();

        $onLeaveToday = LeaveApplication::query()->where('status', 'approved')
            ->whereHas('employee', function($q) {
                $q->where('status', 'active');
            })
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->count();

        // Accurately calculate absent count by checking who SHOULD have worked today
        $absentToday = 0;
        $activeEmployees = Employee::query()->where('status', 'active')->get();
        $todayAttendance = AttendanceRecord::query()->whereDate('date', $today)
            ->whereIn('status', ['present', 'late'])
            ->pluck('employee_id')->toArray();
        $todayLeaves = LeaveApplication::query()->where('status', 'approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->pluck('employee_id')->toArray();

        foreach ($activeEmployees as $emp) {
            if (!in_array($emp->id, $todayAttendance) && !in_array($emp->id, $todayLeaves)) {
                if ($this->attendanceService->isWorkingDay($emp, $today)) {
                    $absentToday++;
                }
            }
        }

        return compact('presentToday', 'lateToday', 'onLeaveToday', 'absentToday');
    }

    /**
     * Get recent attendance for today, including synthesized absent/leave records.
     */
    private function getRecentAttendance(Carbon $today, $activeEmployees)
    {
        $todayAttendance = AttendanceRecord::query()->whereDate('date', $today)
            ->get()
            ->keyBy('employee_id');

        $approvedLeavesToday = LeaveApplication::query()->where('status', 'approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->get()
            ->keyBy('employee_id');

        $recentAttendance = collect();
        foreach ($activeEmployees as $emp) {
            if (isset($todayAttendance[$emp->id])) {
                $recentAttendance->push($todayAttendance[$emp->id]);
            } else {
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

        return $recentAttendance->sortBy(function ($record) {
            return in_array($record->status, ['present', 'late']) ? 0 : 1;
        })->take(7);
    }

    /**
     * Get upcoming birthdays for management-grade active employees.
     */
    private function getUpcomingBirthdays(Carbon $today)
    {
        return Employee::query()->whereNotNull('date_of_birth')
            ->where('status', 'active')
            ->whereHas('grade', function($query) {
                $query->where('name', 'Management');
            })
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
            ->take(10);
    }

    /**
     * Get attendance data separated by office for charting.
     */
    private function getOfficeAttendanceData(Carbon $today, $activeEmployees)
    {
        $offices = Office::all()->keyBy('id');
        $officeData = [];
        
        foreach ($offices as $office) {
            $officeData[$office->id] = [
                'name' => $office->name,
                'present' => 0,
                'late' => 0,
                'leave' => 0,
                'absent' => 0,
            ];
        }
        
        $todayAttendance = AttendanceRecord::query()->whereDate('date', $today)->get()->keyBy('employee_id');
        $approvedLeavesToday = LeaveApplication::query()->where('status', 'approved')
            ->whereDate('from_date', '<=', $today)
            ->whereDate('to_date', '>=', $today)
            ->get()->keyBy('employee_id');
            
        foreach ($activeEmployees as $emp) {
            if (!$emp->office_id || !isset($officeData[$emp->office_id])) {
                continue;
            }
            $oId = $emp->office_id;
            
            $hasAttended = false;
            if (isset($todayAttendance[$emp->id])) {
                $status = strtolower($todayAttendance[$emp->id]->status);
                if ($status === 'late') {
                    $officeData[$oId]['late']++;
                    $hasAttended = true;
                } else if ($status === 'present') {
                    $officeData[$oId]['present']++;
                    $hasAttended = true;
                }
            }
            
            if (!$hasAttended) {
                if (isset($approvedLeavesToday[$emp->id])) {
                    $officeData[$oId]['leave']++;
                } else if ($this->attendanceService->isWorkingDay($emp, $today)) {
                    $officeData[$oId]['absent']++;
                }
            }
        }
        
        return array_values($officeData);
    }

}
