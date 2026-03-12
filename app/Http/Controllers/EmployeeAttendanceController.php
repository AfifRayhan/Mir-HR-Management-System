<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }
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

        // 3. Status Filter
        $status = $request->input('status');

        $fromDateStr = $fromDate->format('Y-m-d');
        $toDateStr = $toDate->format('Y-m-d');

        // 4. Generate Date Sequence and Merge with Records
        $allWorkingDates = [];
        $checkDate = $fromDate->copy()->startOfDay();
        $toDateEndOfDay = $toDate->copy()->endOfDay();
        
        while ($checkDate->lte($toDateEndOfDay)) {
            if ($this->attendanceService->isWorkingDay($employee, $checkDate)) {
                $allWorkingDates[] = $checkDate->toDateString();
            }
            $checkDate->addDay();
        }
 
        // Fetch approved leaves for the range
        $approvedLeaves = \App\Models\LeaveApplication::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function($q) use ($fromDateStr, $toDateStr) {
                $q->whereBetween('from_date', [$fromDateStr, $toDateStr])
                  ->orWhereBetween('to_date', [$fromDateStr, $toDateStr])
                  ->orWhere(function($sub) use ($fromDateStr, $toDateStr) {
                      $sub->where('from_date', '<=', $fromDateStr)
                          ->where('to_date', '>=', $toDateStr);
                  });
            })
            ->get();

        // Fetch existing records
        $existingRecords = AttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$fromDateStr, $toDateStr])
            ->get()
            ->keyBy(function($item) {
                return $item->date->format('Y-m-d');
            });

        // Merge and Filter by Status if needed
        $mergedRecords = collect();
        foreach (array_reverse($allWorkingDates) as $dateStr) {
            if (isset($existingRecords[$dateStr])) {
                $record = $existingRecords[$dateStr];
            } else {
                // Check if on approved leave
                $carbonDate = Carbon::parse($dateStr);
                $onLeave = $approvedLeaves->contains(function($leave) use ($carbonDate) {
                    return $carbonDate->between($leave->from_date, $leave->to_date);
                });
 
                // Create a temporary record for "Absent" or "Leave"
                $record = new AttendanceRecord([
                    'employee_id' => $employee->id,
                    'date' => $dateStr,
                    'status' => $onLeave ? 'leave' : 'absent',
                    'late_seconds' => 0
                ]);
            }
            
            if (empty($status) || strtolower($record->status) == strtolower($status)) {
                $mergedRecords->push($record);
            }
        }

        // 5. Pagination
        $perPage = 15;
        $page = $request->input('page', 1);
        $records = new \Illuminate\Pagination\LengthAwarePaginator(
            $mergedRecords->forPage($page, $perPage),
            $mergedRecords->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 6. Summary Statistics
        $totalPresent = $mergedRecords->whereIn('status', ['present', 'late'])->count();
        $totalLate = $mergedRecords->where('status', 'late')->count();
        $totalAbsent = $mergedRecords->where('status', 'absent')->count();
        $totalRecords = $mergedRecords->count();

        return view('employee.attendance.index', compact(
            'records', 'fromDateStr', 'toDateStr', 'status', 'user', 'roleName',
            'totalPresent', 'totalLate', 'totalAbsent', 'totalRecords', 'employee'
        ));
    }
}
