<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\ManualAttendanceAdjustment;
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

        $date = $request->input('date', now()->toDateString());
        $departmentId = $request->input('department_id');

        $query = AttendanceRecord::with(['employee.department', 'employee.designation'])
            ->where('date', $date);

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $records = $query->get();
        $departments = \App\Models\Department::all();

        return view('personnel.attendance.index', compact('records', 'departments', 'date', 'user', 'roleName', 'employeeRecord'));
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
            'reason' => 'required|string',
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
}
