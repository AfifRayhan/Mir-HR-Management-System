<?php

namespace App\Http\Controllers\TeamLead;

use App\Http\Controllers\Controller;
use App\Models\ManualAttendanceAdjustment;
use App\Models\Employee;
use App\Services\AttendanceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceApprovalController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) abort(403);
        $roleName = optional($user->role)->name ?? 'Unassigned';
        
        $employeeRecord = Employee::where('user_id', $user->id)->first();
        $myEmployeeId = $employeeRecord ? $employeeRecord->id : 0;

        // SCOPE: Direct reports OR Departmental reports (if I am the incharge of any department)
        $directReportIds = Employee::where('reporting_manager_id', $myEmployeeId)->pluck('id')->toArray();
        $departmentIds = \App\Models\Department::where('incharge_id', $myEmployeeId)->pluck('id')->toArray();
        $departmentalEmployeeIds = Employee::whereIn('department_id', $departmentIds)->pluck('id')->toArray();

        // Combine and unique, excluding self
        $subordinateIds = array_unique(array_merge($directReportIds, $departmentalEmployeeIds));
        $subordinateIds = array_diff($subordinateIds, [$myEmployeeId]);

        // Get all pending requests for subordinates
        $adjustments = ManualAttendanceAdjustment::with(['employee.department', 'employee.designation'])
            ->whereIn('employee_id', $subordinateIds)
            ->where('status', 'pending')
            ->orderBy('date', 'desc')
            ->get();

        return view('team_lead.attendance.approvals', compact('adjustments', 'user', 'roleName', 'employeeRecord'));
    }

    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        $employeeRecord = Employee::where('user_id', $user->id)->first();
        
        if (!$employeeRecord) abort(403, 'No employee record linked to your account.');
        
        $myEmployeeId = $employeeRecord->id;
        $directReportIds = Employee::where('reporting_manager_id', $myEmployeeId)->pluck('id')->toArray();
        $departmentIds = \App\Models\Department::where('incharge_id', $myEmployeeId)->pluck('id')->toArray();
        $departmentalEmployeeIds = Employee::whereIn('department_id', $departmentIds)->pluck('id')->toArray();
        $subordinateIds = array_diff(array_unique(array_merge($directReportIds, $departmentalEmployeeIds)), [$myEmployeeId]);

        $adjustment = ManualAttendanceAdjustment::whereIn('employee_id', $subordinateIds)->findOrFail($id);
        
        $adjustment->status = 'approved';
        $adjustment->approved_by = $user->id;
        $adjustment->save();

        NotificationService::clearNotificationsForSource($adjustment);

        $this->attendanceService->processEmployeeAttendance($adjustment->employee, \Carbon\Carbon::parse($adjustment->date)->format('Y-m-d'));

        // Notify the employee
        $employee = $adjustment->employee;
        if ($employee) {
            NotificationService::notifyEmployee(
                $employee,
                'attendance_decision',
                'Attendance Adjustment Approved',
                'Your attendance adjustment request for ' .
                    \Carbon\Carbon::parse($adjustment->date)->format('d M Y') .
                    ' has been approved.',
                route('employee.attendance.index')
            );
        }

        return redirect()->back()->with('success', 'Adjustment approved and attendance recalculated.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reject_reason' => 'required|string|max:50']);
        
        $user = Auth::user();
        $employeeRecord = Employee::where('user_id', $user->id)->first();
        
        if (!$employeeRecord) abort(403, 'No employee record linked to your account.');
        
        $myEmployeeId = $employeeRecord->id;
        $directReportIds = Employee::where('reporting_manager_id', $myEmployeeId)->pluck('id')->toArray();
        $departmentIds = \App\Models\Department::where('incharge_id', $myEmployeeId)->pluck('id')->toArray();
        $departmentalEmployeeIds = Employee::whereIn('department_id', $departmentIds)->pluck('id')->toArray();
        $subordinateIds = array_diff(array_unique(array_merge($directReportIds, $departmentalEmployeeIds)), [$myEmployeeId]);

        $adjustment = ManualAttendanceAdjustment::whereIn('employee_id', $subordinateIds)->findOrFail($id);
        
        $adjustment->status = 'rejected';
        $adjustment->reject_reason = $request->reject_reason;
        $adjustment->approved_by = $user->id;
        $adjustment->save();

        NotificationService::clearNotificationsForSource($adjustment);

        // Notify the employee
        $employee = $adjustment->employee;
        if ($employee) {
            NotificationService::notifyEmployee(
                $employee,
                'attendance_decision',
                'Attendance Adjustment Rejected',
                'Your attendance adjustment request for ' .
                    \Carbon\Carbon::parse($adjustment->date)->format('d M Y') .
                    ' has been rejected. Reason: ' . $request->reject_reason,
                route('employee.attendance.index')
            );
        }

        return redirect()->back()->with('success', 'Adjustment request rejected.');
    }
}
