<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveApplicationController extends Controller
{
    // --- HR methods ---
    public function indexHR()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        $applications = LeaveApplication::with(['employee.user', 'leaveType'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('personnel.leave-applications.index', compact('applications', 'user', 'roleName', 'employee'));
    }

    public function updateStatus(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leaveApplication->status = $request->status;

        if ($request->status === 'approved') {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $leaveApplication->approved_by = $user->id;
            $leaveApplication->approved_at = now();

            // Deduct from balance
            $balance = LeaveBalance::where('employee_id', $leaveApplication->employee_id)
                ->where('leave_type_id', $leaveApplication->leave_type_id)
                ->where('year', date('Y', strtotime($leaveApplication->from_date)))
                ->first();

            if ($balance) {
                $balance->used_days += $leaveApplication->total_days;
                $balance->remaining_days -= $leaveApplication->total_days;
                $balance->save();
            }
        }

        $leaveApplication->save();

        return redirect()->back()->with('success', 'Leave application status updated.');
    }

    // --- Team Lead methods ---
    public function indexTeamLead()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'No employee record found for your account.');
        }

        // Get IDs of all direct reports
        $directReportIds = Employee::where('reporting_manager_id', $employee->id)->pluck('id');

        $applications = LeaveApplication::with(['employee.user', 'leaveType'])
            ->whereIn('employee_id', $directReportIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('team-lead.leave-applications.index', compact('applications', 'user', 'roleName', 'employee'));
    }

    public function indexTeamLeadSelf()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Only employees can access this page.');
        }

        $applications = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $leaveTypes = LeaveType::all();
        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get();

        return view('team-lead.leave.index', compact('applications', 'leaveTypes', 'balances', 'user', 'roleName', 'employee'));
    }

    public function updateStatusTeamLead(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $teamLeadEmployee = Employee::where('user_id', $user->id)->first();

        // Security: ensure this applicant is actually a direct report of this team lead
        $isDirectReport = Employee::where('id', $leaveApplication->employee_id)
            ->where('reporting_manager_id', $teamLeadEmployee?->id)
            ->exists();

        if (!$isDirectReport) {
            return redirect()->back()->with('error', 'You are not authorized to manage this application.');
        }

        $leaveApplication->status = $request->status;

        if ($request->status === 'approved') {
            $leaveApplication->approved_by = $user->id;
            $leaveApplication->approved_at = now();

            $balance = LeaveBalance::where('employee_id', $leaveApplication->employee_id)
                ->where('leave_type_id', $leaveApplication->leave_type_id)
                ->where('year', date('Y', strtotime($leaveApplication->from_date)))
                ->first();

            if ($balance) {
                $balance->used_days += $leaveApplication->total_days;
                $balance->remaining_days -= $leaveApplication->total_days;
                $balance->save();
            }
        }

        $leaveApplication->save();

        return redirect()->back()->with('success', 'Leave application status updated.');
    }

    // --- Employee methods ---
    public function indexEmployee()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Only employees can access this page.');
        }

        $applications = LeaveApplication::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $leaveTypes = LeaveType::all();
        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get();

        return view('employee.leave.index', compact('applications', 'leaveTypes', 'balances', 'user', 'roleName', 'employee'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'You are not registered as an employee.');
        }

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string',
            'leave_address' => 'nullable|string',
        ]);

        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);

        // Fetch which days of the week are weekly holidays (e.g. ['Friday', 'Saturday'])
        $weeklyHolidayDays = \App\Models\WeeklyHoliday::where('is_holiday', true)->pluck('day_name')->toArray();

        // Count only working days (skip weekly holidays)
        $totalDays = 0;
        $current = $fromDate->copy();
        while ($current->lte($toDate)) {
            if (!in_array($current->format('l'), $weeklyHolidayDays)) {
                $totalDays++;
            }
            $current->addDay();
        }

        if ($totalDays === 0) {
            return redirect()->back()->with('error', 'Your selected date range falls entirely on weekly holidays. No working days to deduct.');
        }

        // Check max consecutive days limit (against working days)
        $leaveType = \App\Models\LeaveType::find($request->leave_type_id);
        if ($leaveType && $leaveType->max_consecutive_days && $totalDays > $leaveType->max_consecutive_days) {
            return redirect()->back()->with(
                'error',
                'You can only request a maximum of ' . $leaveType->max_consecutive_days . ' consecutive working day(s) for ' . $leaveType->name . '.'
            );
        }

        // Check balance (Ensure it's initialized by HR)
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $fromDate->year)
            ->first();

        if (!$balance) {
            return redirect()->back()->with('error', 'Your leave account for this type has not been initialized for ' . $fromDate->year . '. Please contact HR.');
        }

        if ($balance->remaining_days < $totalDays) {
            return redirect()->back()->with('error', 'Insufficient leave balance.');
        }

        LeaveApplication::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $request->leave_type_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'leave_address' => $request->leave_address,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Leave application submitted successfully.');
    }
}
