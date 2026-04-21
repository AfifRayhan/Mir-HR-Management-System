<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Services\AttendanceService;
use Illuminate\Support\Carbon;

class WorkingDayController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'from_date'   => 'required|date',
            'to_date'     => 'required|date|after_or_equal:from_date',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $from = Carbon::parse($request->from_date);
        $to   = Carbon::parse($request->to_date);

        $totalDays = 0;
        $details = [];

        $current = $from->copy();
        while ($current->lte($to)) {
            $isWorking = $this->attendanceService->isWorkingDay($employee, $current);
            if ($isWorking) {
                $totalDays++;
            }
            
            $details[] = [
                'date' => $current->toDateString(),
                'is_working' => $isWorking,
                'status' => $this->attendanceService->getDateAttendanceStatus($employee, $current)
            ];

            $current->addDay();
        }

        return response()->json([
            'total_days' => $totalDays,
            'details' => $details
        ]);
    }
}
