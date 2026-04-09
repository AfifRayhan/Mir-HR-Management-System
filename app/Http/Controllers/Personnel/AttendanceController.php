<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ManualAttendanceAdjustment;
use App\Models\Office;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        $date         = $request->input('date', now()->toDateString());
        $departmentId = $request->input('department_id');
        $officeId     = $request->input('office_id');
        $status       = $request->input('status');
        $search       = $request->input('search');

        $query = AttendanceRecord::with(['employee.department', 'employee.designation', 'employee.office'])
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            })
            ->where('date', $date);

        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ($officeId) {
            $query->whereHas('employee', fn($q) => $q->where('office_id', $officeId));
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $records     = $query->get();
        $departments = Department::all();
        $offices     = Office::all();
        $statuses    = ['present', 'late', 'absent', 'leave'];

        return view('personnel.attendance.index', compact(
            'records', 'departments', 'offices', 'statuses', 'date',
            'user', 'roleName', 'employeeRecord'
        ));
    }

    public function processLogs(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $this->attendanceService->processLogsForDate($date);

        return redirect()->back()->with('success', "Attendance processed for $date");
    }

    public function adjust(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        $employees = Employee::where('status', 'active')->get();

        return view('personnel.attendance.adjust', compact('employees', 'user', 'roleName', 'employeeRecord'));
    }

    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'in_time' => 'required|date_format:H:i',
            'out_time' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:50',
        ]);

        $date = $validated['date'];
        
        // Combine date and time string to full timestamp
        $validated['in_time'] = $date . ' ' . $validated['in_time'];
        if ($validated['out_time']) {
            $validated['out_time'] = $date . ' ' . $validated['out_time'];
            
            // Validate after_or_equal:in_time manually or using custom logic
            if (strtotime($validated['out_time']) < strtotime($validated['in_time'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['out_time' => 'The out time must be after or equal to in time.']);
            }
        }

        $validated['adjusted_by'] = \Illuminate\Support\Facades\Auth::id();
        $validated['status'] = 'approved';
        $validated['approved_by'] = \Illuminate\Support\Facades\Auth::id();

        ManualAttendanceAdjustment::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            $validated
        );

        // Reprocess for this employee
        $employee = Employee::find($validated['employee_id']);
        $this->attendanceService->processEmployeeAttendance($employee, $validated['date']);

        return redirect()->route('personnel.attendances.index', ['date' => $validated['date']])
            ->with('success', 'Manual adjustment saved and attendance recalculated.');
    }

    public function approvals(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        // Get all pending requests
        $adjustments = ManualAttendanceAdjustment::with(['employee.department', 'employee.designation'])
            ->where('status', 'pending')
            ->orderBy('date', 'desc')
            ->get();

        return view('personnel.attendance.approvals', compact('adjustments', 'user', 'roleName', 'employeeRecord'));
    }

    public function approveAdjustment(Request $request, $id)
    {
        $adjustment = ManualAttendanceAdjustment::findOrFail($id);
        
        $adjustment->status = 'approved';
        $adjustment->approved_by = \Illuminate\Support\Facades\Auth::id();
        $adjustment->save();

        $this->attendanceService->processEmployeeAttendance($adjustment->employee, \Carbon\Carbon::parse($adjustment->date)->format('Y-m-d'));

        return redirect()->back()->with('success', 'Adjustment approved and attendance recalculated.');
    }

    public function rejectAdjustment(Request $request, $id)
    {
        $request->validate(['reject_reason' => 'required|string|max:50']);
        $adjustment = ManualAttendanceAdjustment::findOrFail($id);
        
        $adjustment->status = 'rejected';
        $adjustment->reject_reason = $request->reject_reason;
        $adjustment->approved_by = \Illuminate\Support\Facades\Auth::id();
        $adjustment->save();

        return redirect()->back()->with('success', 'Adjustment request rejected.');
    }
    public function records(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        // 1. Get List of Employees for Search
        $employees = Employee::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'employee_code']);

        // 2. Process Filters
        $targetEmployeeId = $request->input('employee_id');
        $fromDateStr = $request->input('from_date', \Carbon\Carbon::now()->startOfMonth()->toDateString());
        $toDateStr   = $request->input('to_date', \Carbon\Carbon::now()->toDateString());
        $status      = $request->input('status');

        $selectedEmployee = null;
        $records = collect();
        $stats = [
            'totalPresent' => 0,
            'totalLate'    => 0,
            'totalAbsent'  => 0,
            'totalRecords' => 0
        ];

        if ($targetEmployeeId) {
            $selectedEmployee = Employee::with(['department', 'designation'])->find($targetEmployeeId);

            if ($selectedEmployee) {
                $fromDate = \Carbon\Carbon::parse($fromDateStr)->startOfDay();
                $toDate   = \Carbon\Carbon::parse($toDateStr)->endOfDay();

                // 3. Generate Date Sequence and Merge with Records (Detail logic from EmployeeAttendanceController)
                $allWorkingDates = [];
                $checkDate = $fromDate->copy();
                
                while ($checkDate->lte($toDate)) {
                    if ($this->attendanceService->isWorkingDay($selectedEmployee, $checkDate)) {
                        $allWorkingDates[] = $checkDate->toDateString();
                    }
                    $checkDate->addDay();
                }

                // Fetch approved leaves
                $approvedLeaves = \App\Models\LeaveApplication::where('employee_id', $selectedEmployee->id)
                    ->where('status', 'approved')
                    ->where(function($q) use ($fromDateStr, $toDateStr) {
                        $q->whereBetween('from_date', [$fromDateStr, $toDateStr])
                          ->orWhereBetween('to_date', [$fromDateStr, $toDateStr]);
                    })
                    ->get();

                // Fetch existing records
                $existingRecords = AttendanceRecord::where('employee_id', $selectedEmployee->id)
                    ->whereBetween('date', [$fromDateStr, $toDateStr])
                    ->get()
                    ->keyBy(function($item) {
                        return $item->date->format('Y-m-d');
                    });

                // Merge
                foreach (array_reverse($allWorkingDates) as $dateStr) {
                    if (isset($existingRecords[$dateStr])) {
                        $record = $existingRecords[$dateStr];
                    } else {
                        $carbonDate = \Carbon\Carbon::parse($dateStr);
                        $onLeave = $approvedLeaves->contains(function($leave) use ($carbonDate) {
                            return $carbonDate->between($leave->from_date, $leave->to_date);
                        });

                        $record = new AttendanceRecord([
                            'employee_id' => $selectedEmployee->id,
                            'date' => $dateStr,
                            'status' => $onLeave ? 'leave' : 'absent',
                            'late_seconds' => 0
                        ]);
                    }
                    
                    if (empty($status) || strtolower($record->status) == strtolower($status)) {
                        $records->push($record);
                    }
                }

                // Stats
                $stats['totalPresent'] = $records->whereIn('status', ['present', 'late'])->count();
                $stats['totalLate']    = $records->where('status', 'late')->count();
                $stats['totalAbsent']  = $records->where('status', 'absent')->count();
                $stats['totalRecords'] = $records->count();

                // Pagination (Simplified for now, can be manual as in EmployeeAttendanceController)
                $perPage = 15;
                $page = $request->input('page', 1);
                $records = new \Illuminate\Pagination\LengthAwarePaginator(
                    $records->forPage($page, $perPage),
                    $records->count(),
                    $perPage,
                    $page,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            }
        }

        return view('personnel.attendance.records', compact(
            'employees', 'selectedEmployee', 'records', 
            'fromDateStr', 'toDateStr', 'status', 'stats',
            'user', 'roleName', 'employeeRecord'
        ));
    }
}
