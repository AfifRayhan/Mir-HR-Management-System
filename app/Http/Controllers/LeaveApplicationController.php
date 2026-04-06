<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\WeeklyHoliday;

class LeaveApplicationController extends Controller
{
    // --- HR methods ---
    public function indexHR()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        $applications = LeaveApplication::with(['employee.user', 'leaveType', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('personnel.leave-applications.index', compact('applications', 'user', 'roleName', 'employee'));
    }

    // --- HR Manual Leave methods ---
    public function manualLeave()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        $employees = Employee::orderBy('name')->get();
        $leaveTypes = LeaveType::orderBy('sort_order')->get();

        return view('personnel.leave.manual', compact('user', 'roleName', 'employee', 'employees', 'leaveTypes'));
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'required|string',
            'leave_address' => 'nullable|string',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $targetEmployee = Employee::findOrFail($request->employee_id);
        $fromDate = Carbon::parse($request->from_date);
        $toDate   = Carbon::parse($request->to_date);

        // Check for overlapping leave applications for this employee
        $overlappingLeave = LeaveApplication::where('employee_id', $targetEmployee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('from_date', [$request->from_date, $request->to_date])
                      ->orWhereBetween('to_date', [$request->from_date, $request->to_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('from_date', '<=', $request->from_date)
                            ->where('to_date', '>=', $request->to_date);
                      });
            })
            ->first();

        if ($overlappingLeave) {
            return redirect()->back()->withInput()->with('error',
                $targetEmployee->name . ' already has a ' . $overlappingLeave->status .
                ' leave application during this period (' .
                Carbon::parse($overlappingLeave->from_date)->format('d M Y') . ' to ' .
                Carbon::parse($overlappingLeave->to_date)->format('d M Y') . ').');
        }

        // Fetch weekly holidays for this employee's office
        $hasOfficeConfig = WeeklyHoliday::where('office_id', $targetEmployee->office_id)->exists();
        $weeklyHolidayDays = WeeklyHoliday::where('is_holiday', true)
            ->where(function ($q) use ($hasOfficeConfig, $targetEmployee) {
                if ($hasOfficeConfig) {
                    $q->where('office_id', $targetEmployee->office_id);
                } else {
                    $q->whereNull('office_id');
                }
            })
            ->pluck('day_name')
            ->toArray();

        // Count working days
        $totalDays = 0;
        $current = $fromDate->copy();
        while ($current->lte($toDate)) {
            if (!in_array($current->format('l'), $weeklyHolidayDays)) {
                $totalDays++;
            }
            $current->addDay();
        }

        if ($totalDays === 0) {
            return redirect()->back()->withInput()->with('error', 'The selected date range falls entirely on weekly holidays. No working days to deduct.');
        }

        // Check max consecutive days
        $leaveType = LeaveType::find($request->leave_type_id);
        if ($leaveType && $leaveType->max_consecutive_days && $totalDays > $leaveType->max_consecutive_days) {
            return redirect()->back()->withInput()->with(
                'error',
                'Maximum of ' . $leaveType->max_consecutive_days . ' consecutive working day(s) allowed for ' . $leaveType->name . '.'
            );
        }

        // Check and deduct balance
        $balance = LeaveBalance::where('employee_id', $targetEmployee->id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', $fromDate->year)
            ->first();

        if (!$balance) {
            return redirect()->back()->withInput()->with('error',
                'Leave account for ' . $targetEmployee->name . ' (' . $leaveType->name . ') has not been initialized for ' . $fromDate->year . '.');
        }

        if ($balance->remaining_days < $totalDays) {
            return redirect()->back()->withInput()->with('error',
                'Insufficient leave balance for ' . $targetEmployee->name . '. Remaining: ' . $balance->remaining_days . ' day(s).');
        }

        $documentPath = null;
        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $documentPath = $file->storeAs('supporting_documents', $fileName, 'public');
        }

        // Create as approved immediately (HR is manually entering it)
        $application = LeaveApplication::create([
            'employee_id'          => $targetEmployee->id,
            'leave_type_id'        => $request->leave_type_id,
            'from_date'            => $request->from_date,
            'to_date'              => $request->to_date,
            'total_days'           => $totalDays,
            'reason'               => $request->reason,
            'leave_address'        => $request->leave_address,
            'supporting_document'  => $documentPath,
            'status'               => 'approved',
            'approved_by'          => Auth::id(),
            'approved_at'          => Carbon::now(),
        ]);

        // Deduct balance
        $balance->used_days      += $totalDays;
        $balance->remaining_days -= $totalDays;
        $balance->save();

        return redirect()->back()->with('success',
            'Manual leave for ' . $targetEmployee->name . ' (' . $totalDays . ' day(s)) has been recorded and approved.');
    }

    public function updateStatus(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leaveApplication->status = $request->status;
        $leaveApplication->approved_by = Auth::id();
        $leaveApplication->approved_at = Carbon::now();
 
        if ($request->status === 'approved') {
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

        // Get departments where this employee is the incharge
        $inchargeDeptIds = \App\Models\Department::where('incharge_id', $employee->id)->pluck('id');

        // Get IDs of all direct reports or employees in those departments
        $teamEmployeeIds = Employee::where('reporting_manager_id', $employee->id)
            ->orWhereIn('department_id', $inchargeDeptIds)
            ->pluck('id');

        $applications = LeaveApplication::with([
            'employee.user', 
            'leaveType', 
            'approver',
            'employee.leaveBalances' => function($query) {
                $query->where('year', date('Y'));
            }
        ])
            ->whereIn('employee_id', $teamEmployeeIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('team-lead.leave-applications.index', compact('applications', 'user', 'roleName', 'employee'));
    }

    public function historyTeamLead(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'No employee record found for your account.');
        }

        $inchargeDeptIds = \App\Models\Department::where('incharge_id', $employee->id)->pluck('id');
        $teamEmployeeIds = Employee::where('reporting_manager_id', $employee->id)
            ->orWhereIn('department_id', $inchargeDeptIds)
            ->pluck('id');
        
        $teamEmployees = Employee::whereIn('id', $teamEmployeeIds)->orderBy('name')->get();

        $month = $request->input('month');
        $year = $request->has('year') ? $request->input('year') : date('Y');
        $employeeId = $request->input('employee_id');
        $leaveTypeId = $request->input('leave_type_id');
        $status = $request->input('status');

        $query = LeaveApplication::with([
            'employee.user', 
            'leaveType', 
            'approver',
            'employee.leaveBalances' => function($q) use ($year) {
                $q->where('year', $year ?: date('Y'));
            }
        ])
            ->whereIn('employee_id', $teamEmployeeIds);

        if ($month) {
            $query->whereMonth('from_date', $month);
        }
        if ($year) {
            $query->whereYear('from_date', $year);
        }
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $leaveTypes = LeaveType::orderBy('name')->get();

        return view('team-lead.leave-applications.history', compact(
            'applications', 'user', 'roleName', 'employee', 
            'month', 'year', 'employeeId', 'leaveTypeId', 'status', 
            'teamEmployees', 'leaveTypes'
        ));
    }

    public function indexTeamLeadSelf(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Only employees can access this page.');
        }

        $query = LeaveApplication::with(['leaveType', 'approver'])
            ->where('employee_id', $employee->id);

        $month = $request->has('month') ? $request->input('month') : null;
        $year = $request->has('year') ? $request->input('year') : date('Y');

        if ($month) {
            $query->whereMonth('from_date', $month);
        }
        if ($year) {
            $query->whereYear('from_date', $year);
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $leaveTypes = LeaveType::where(function ($query) use ($employee) {
            $query->whereNull('office_id')
                ->orWhere('office_id', $employee->office_id);
        })->orderBy('sort_order')->get();
        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get();

        $hasOfficeConfig = \App\Models\WeeklyHoliday::where('office_id', $employee->office_id)->exists();
        $weeklyHolidayDays = \App\Models\WeeklyHoliday::where('is_holiday', true)
            ->where(function ($q) use ($hasOfficeConfig, $employee) {
                if ($hasOfficeConfig) {
                    $q->where('office_id', $employee->office_id);
                } else {
                    $q->whereNull('office_id');
                }
            })
            ->pluck('day_name')
            ->toArray();

        return view('team-lead.leave.index', compact('applications', 'leaveTypes', 'balances', 'user', 'roleName', 'employee', 'weeklyHolidayDays', 'month', 'year'));
    }

    public function updateStatusTeamLead(Request $request, LeaveApplication $leaveApplication)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string|max:50',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $teamLeadEmployee = Employee::where('user_id', $user->id)->first();

        // Security: ensure this applicant is either a direct report OR in a department this user manages
        $inchargeDeptIds = \App\Models\Department::where('incharge_id', $teamLeadEmployee?->id)->pluck('id');
        
        $isAuthorized = Employee::where('id', $leaveApplication->employee_id)
            ->where(function($query) use ($teamLeadEmployee, $inchargeDeptIds) {
                $query->where('reporting_manager_id', $teamLeadEmployee?->id)
                      ->orWhereIn('department_id', $inchargeDeptIds);
            })
            ->exists();

        if (!$isAuthorized) {
            return redirect()->back()->with('error', 'You are not authorized to manage this application.');
        }

        $leaveApplication->status = $request->status;
        if ($request->status === 'rejected' && $request->has('remarks')) {
            $leaveApplication->remarks = $request->remarks;
        }
        $leaveApplication->approved_by = $user->id;
        $leaveApplication->approved_at = Carbon::now();
        if ($request->status === 'approved') {

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
    public function indexEmployee(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'Only employees can access this page.');
        }

        $query = LeaveApplication::with(['leaveType', 'approver'])
            ->where('employee_id', $employee->id);

        $month = $request->has('month') ? $request->input('month') : null;
        $year = $request->has('year') ? $request->input('year') : date('Y');

        if ($month) {
            $query->whereMonth('from_date', $month);
        }
        if ($year) {
            $query->whereYear('from_date', $year);
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $leaveTypes = LeaveType::where(function ($query) use ($employee) {
            $query->whereNull('office_id')
                ->orWhere('office_id', $employee->office_id);
        })->orderBy('sort_order')->get();
        
        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get();

        $hasOfficeConfig = \App\Models\WeeklyHoliday::where('office_id', $employee->office_id)->exists();
        $weeklyHolidayDays = \App\Models\WeeklyHoliday::where('is_holiday', true)
            ->where(function ($q) use ($hasOfficeConfig, $employee) {
                if ($hasOfficeConfig) {
                    $q->where('office_id', $employee->office_id);
                } else {
                    $q->whereNull('office_id');
                }
            })
            ->pluck('day_name')
            ->toArray();

        return view('employee.leave.index', compact('applications', 'leaveTypes', 'balances', 'user', 'roleName', 'employee', 'month', 'year', 'weeklyHolidayDays'));
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
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);

        // Check for overlapping leave applications
        $overlappingLeave = LeaveApplication::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('from_date', [$request->from_date, $request->to_date])
                      ->orWhereBetween('to_date', [$request->from_date, $request->to_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('from_date', '<=', $request->from_date)
                            ->where('to_date', '>=', $request->to_date);
                      });
            })
            ->first();

        if ($overlappingLeave) {
            return redirect()->back()->with('error', 'You already have a ' . $overlappingLeave->status . ' leave application during this period (from ' . Carbon::parse($overlappingLeave->from_date)->format('d M Y') . ' to ' . Carbon::parse($overlappingLeave->to_date)->format('d M Y') . ').');
        }

        // Fetch which days of the week are weekly holidays for this employee's office
        $hasOfficeConfig = \App\Models\WeeklyHoliday::where('office_id', $employee->office_id)->exists();
        
        $weeklyHolidayDays = \App\Models\WeeklyHoliday::where('is_holiday', true)
            ->where(function ($q) use ($hasOfficeConfig, $employee) {
                if ($hasOfficeConfig) {
                    $q->where('office_id', $employee->office_id);
                } else {
                    $q->whereNull('office_id');
                }
            })
            ->pluck('day_name')
            ->toArray();

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

        $documentPath = null;
        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $documentPath = $file->storeAs('supporting_documents', $fileName, 'public');
        }

        LeaveApplication::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $request->leave_type_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'leave_address' => $request->leave_address,
            'supporting_document' => $documentPath,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Leave application submitted successfully.');
    }
}
