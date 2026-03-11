<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceController extends Controller
{
    /**
     * Display the employee's attendance history with filters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }
        $roleName = optional($user->role)->name ?? 'Unassigned';

        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee) {
            abort(403, 'No employee record linked to your account.');
        }

        // 1. Process Date Filters safely
        try {
            $fromDate = $request->filled('from_date') 
                ? Carbon::parse($request->from_date) 
                : Carbon::now()->startOfMonth();
            
            $toDate = $request->filled('to_date') 
                ? Carbon::parse($request->to_date) 
                : Carbon::now()->endOfDay();
                
        } catch (\Exception $e) {
            $fromDate = Carbon::now()->startOfMonth();
            $toDate = Carbon::now()->endOfDay();
        }

        // 2. Status Filter
        $status = $request->input('status');

        $fromDateStr = $fromDate->format('Y-m-d');
        $toDateStr = $toDate->format('Y-m-d');

        // 3. Build Query
        $query = AttendanceRecord::with('employee')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->orderBy('date', 'desc');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $records = $query->paginate(15)->appends($request->all());

        \Illuminate\Support\Facades\Log::info("Employee Attendance Debug", [
            'user_id' => $user->id,
            'employee_id' => $employee->id,
            'from_date' => $fromDateStr,
            'to_date' => $toDateStr,
            'status' => $status,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'records_count' => $records->count()
        ]);

        // 4. Summary Statistics built from the exact date range
        $allRecords = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->get();

        $totalPresent = $allRecords->whereIn('status', ['present', 'late'])->count();
        $totalLate = $allRecords->where('status', 'late')->count();
        $totalAbsent = $allRecords->where('status', 'absent')->count();
        $totalRecords = $allRecords->count();

        return view('employee.attendance.index', compact(
            'records', 'fromDateStr', 'toDateStr', 'status', 'user', 'roleName',
            'totalPresent', 'totalLate', 'totalAbsent', 'totalRecords', 'employee'
        ));
    }
}
