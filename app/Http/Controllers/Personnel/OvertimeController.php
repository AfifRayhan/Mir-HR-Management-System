<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::where('status', 'active')
            ->whereHas('designation', function($q) {
                $q->where('is_ot_eligible', true);
            })
            ->orderBy('name')
            ->get();
        $employeeId = $request->input('employee_id');
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $selectedEmployee = null;
        $daysInMonth = [];
        $overtimeRecords = [];
        $rosterSchedules = [];
        $weeklyHolidays = [];
        $holidays = [];

        if ($employeeId) {
            $selectedEmployee = Employee::with(['grade', 'officeTime'])->find($employeeId);
            $startDate = Carbon::createFromDate($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $daysInMonth[] = $currentDate->copy();
                $currentDate->addDay();
            }

            $overtimeRecords = Overtime::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(function ($record) {
                    return $record->date->format('Y-m-d');
                });

            $rosterSchedules = \App\Models\RosterSchedule::where('employee_id', $employeeId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->keyBy(function ($record) {
                    return $record->date->format('Y-m-d');
                });
            
            $weeklyHolidays = \App\Models\WeeklyHoliday::where('is_holiday', true)
                ->where(function($q) use ($selectedEmployee) {
                    $q->whereNull('office_id')
                      ->orWhere('office_id', $selectedEmployee->office_id);
                })
                ->pluck('day_name')
                ->toArray();

            $holidays = \App\Models\Holiday::where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('from_date', [$startDate, $endDate])
                      ->orWhereBetween('to_date', [$startDate, $endDate])
                      ->orWhere(function($q2) use ($startDate, $endDate) {
                          $q2->where('from_date', '<=', $startDate)
                             ->where('to_date', '>=', $endDate);
                      });
                })->get();

            $holidayMap = [];
            foreach ($holidays as $h) {
                $cursor = $h->from_date->copy();
                while ($cursor <= $h->to_date) {
                    $holidayMap[$cursor->format('Y-m-d')] = [
                        'title' => $h->title,
                        'type'  => $h->type
                    ];
                    $cursor->addDay();
                }
            }
            $holidays = $holidayMap;
        }

        return view('personnel.overtimes.index', compact(
            'employees',
            'employeeId',
            'month',
            'year',
            'selectedEmployee',
            'daysInMonth',
            'overtimeRecords',
            'rosterSchedules',
            'weeklyHolidays',
            'holidays'
        ));
    }

    public function save(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required',
            'year' => 'required',
            'ot' => 'required|array',
        ]);

        $employeeId = $request->employee_id;
        $employee = Employee::with('grade')->findOrFail($employeeId);
        $otData = $request->ot;

        foreach ($otData as $date => $data) {
            $totalHours = $this->calculateTotalHours($data['start'], $data['stop']);
            
            // If all values are empty/zero, and a record exists, we might want to delete it or just skip
            if ($totalHours <= 0 && empty($data['workday_plus_5']) && empty($data['holiday_plus_5']) && empty($data['eid_duty'])) {
                Overtime::where('employee_id', $employeeId)->where('date', $date)->delete();
                continue;
            }

            $amount = $this->calculateAmount($employee, $totalHours, $data);

            Overtime::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                [
                    'ot_start' => $data['start'] ?: null,
                    'ot_stop' => $data['stop'] ?: null,
                    'total_ot_hours' => $totalHours,
                    'is_workday_duty_plus_5' => isset($data['workday_plus_5']),
                    'is_holiday_duty_plus_5' => isset($data['holiday_plus_5']),
                    'is_eid_duty' => isset($data['eid_duty']),
                    'amount' => $amount,
                    'remarks' => $data['remarks'] ?? null,
                    'created_by' => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Overtime records saved successfully.');
    }

    private function calculateTotalHours($start, $stop)
    {
        if (!$start || !$stop) return 0;

        $startTime = Carbon::parse($start);
        $stopTime = Carbon::parse($stop);

        if ($stopTime->lt($startTime)) {
            $stopTime->addDay();
        }

        return $startTime->diffInMinutes($stopTime) / 60;
    }

    private function calculateAmount($employee, $totalHours, $data)
    {
        $gross = $employee->gross_salary;
        $fullShiftIncome = ($gross * 0.6) / 30;

        // Determine base multiplier for this day's work
        $multiplier = 1;
        if (isset($data['eid_duty'])) {
            $multiplier = 3;
        } elseif (isset($data['holiday_plus_5'])) {
            $multiplier = 2;
        }

        $baseValue = $fullShiftIncome * $multiplier;
        
        // Count how many duty components are active
        $count = 0;
        if (isset($data['eid_duty'])) $count++;
        if (isset($data['holiday_plus_5'])) $count++;
        if (isset($data['workday_plus_5'])) $count++;

        return $baseValue * $count;
    }
}
