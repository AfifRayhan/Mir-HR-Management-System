<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\RosterSchedule;
use App\Exports\RosterExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RosterController extends Controller
{
    // Group Map is now handled via RosterTime or explicitly if needed, 
    // but for the index dropdown, we'll maintain a logical list.
    const GROUP_MAP = [
        'noc-borak'     => 'NOC (Borak)',
        'noc-sylhet'    => 'NOC (Sylhet)',
        'tech-gulshan'  => 'Technician (Gulshan)',
        'tech-borak'    => 'Technician (Borak)',
        'tech-jessore'  => 'Technician (Jessore)',
        'tech-sylhet'   => 'Technician (Sylhet)',
    ];

    public function index(Request $request)
    {
        $data = $this->getRosterData($request);
        return view('roster.index', $data);
    }

    public function export(Request $request)
    {
        $data = $this->getRosterData($request);
        $format = $request->query('format', 'xlsx');
        $fileName = 'Roster_' . $data['groupLabel'] . '_' . $data['monthStart']->format('Y-m');

        if ($format === 'csv') {
            return Excel::download(new RosterExport($data), $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new RosterExport($data), $fileName . '.xlsx');
    }

    private function getRosterData(Request $request)
    {
        $groupSlug  = $request->query('group', 'noc-borak');
        $groupLabel = self::GROUP_MAP[$groupSlug] ?? 'NOC (Borak)';
        $monthParam = $request->query('month', now()->format('Y-m'));
        $monthStart = Carbon::parse($monthParam . '-01')->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();
        
        $mode = $request->query('mode', 'weekly');

        // Determine the query range based on mode
        if ($mode === 'weekly') {
            $startOfDisplay = Carbon::now()->startOfWeek(6);
            $endOfDisplay   = $startOfDisplay->copy()->addDays(6);
        } else {
            $startOfDisplay = $monthStart;
            $endOfDisplay   = $monthEnd;
        }

        $query = Employee::whereHas('officeTime', fn($q) => $q->where('shift_name', 'Roster'))
            ->where('status', 'active');

        if (in_array($groupSlug, ['tech-jessore'])) {
            $query->where(function($q) use ($groupLabel) {
                $q->where('roster_group', $groupLabel)
                  ->orWhere('roster_group', 'All')
                  ->orWhereNull('roster_group');
            });
        } else {
            $query->where('roster_group', $groupLabel);
        }

        $employees = $query->orderBy('name')->get();

        $schedules = RosterSchedule::whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('date', [$startOfDisplay->toDateString(), $endOfDisplay->toDateString()])
            ->get();

        $scheduleMap = [];
        foreach ($schedules as $s) {
            $scheduleMap[$s->employee_id][Carbon::parse($s->date)->toDateString()] = $s->shift_type;
        }

        // For weekly mode, build a pattern map indexed by day-of-week index (0=Sat..6=Fri)
        $patternMap = [];
        if ($mode === 'weekly') {
            $dayIndexMap = [6 => 0, 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6];
            
            // Build pattern from the actual displayed week
            $cursor = $startOfDisplay->copy();
            $index = 0;
            while ($cursor->lte($endOfDisplay)) {
                $dateStr = $cursor->toDateString();
                foreach ($employees as $emp) {
                    if (isset($scheduleMap[$emp->id][$dateStr])) {
                        $patternMap[$index][$emp->id] = $scheduleMap[$emp->id][$dateStr];
                    }
                }
                $cursor->addDay();
                $index++;
            }
        }

        $days = [];
        if ($mode === 'weekly') {
            for ($i = 0; $i < 7; $i++) {
                $days[] = $startOfDisplay->copy()->addDays($i);
            }
        } else {
            $cursor = $monthStart->copy();
            while ($cursor->lte($monthEnd)) {
                $days[] = $cursor->copy();
                $cursor->addDay();
            }
        }

        $groups = self::GROUP_MAP;
        
        // Fetch dynamic shifts from database
        $dbShifts = \App\Models\RosterTime::where('group_slug', $groupSlug)->get();

        // Map database records to the internal SHIFT_CONFIG format used by the view
        $shiftTypes = [];
        foreach ($dbShifts as $rt) {
            $timeRange = $rt->start_time && $rt->end_time 
                ? Carbon::parse($rt->start_time)->format('gA') . '–' . Carbon::parse($rt->end_time)->format('gA')
                : '';
            
            $shiftTypes[$rt->shift_key] = [
                'label' => $rt->display_label,
                'time'  => $timeRange,
                'badge' => $rt->badge_class,
            ];
        }

        // Ensure "Off" is always present and placed at the rightmost (end of array)
        if (isset($shiftTypes['Off'])) {
            $offConfig = $shiftTypes['Off'];
            unset($shiftTypes['Off']);
            $shiftTypes['Off'] = $offConfig;
        } else {
            $shiftTypes['Off'] = ['label' => 'Off Day', 'time' => '', 'badge' => 'badge-off'];
        }

        return compact(
            'groupSlug', 'groupLabel', 'monthStart', 'monthEnd',
            'employees', 'scheduleMap', 'patternMap',
            'days', 'groups', 'shiftTypes', 'monthParam', 'mode'
        );
    }

    public function save(Request $request)
    {
        $request->validate([
            'mode'                    => 'required|in:weekly,monthly',
            'month'                   => 'required|date_format:Y-m',
            'schedules'               => 'required|array',
            'schedules.*.employee_id' => 'required|exists:employees,id',
            'schedules.*.shift_type'  => 'required|string', // Allow any string now as they are dynamic
            // In monthly mode, date is required. In weekly mode, day_index is required.
            'schedules.*.date'        => 'required_if:mode,monthly|date',
            'schedules.*.day_index'   => 'required_if:mode,weekly|integer|between:0,6',
        ]);

        $userId     = Auth::id();
        $mode       = $request->input('mode');
        $monthParam = $request->input('month');
        $monthStart = Carbon::parse($monthParam . '-01')->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        DB::transaction(function () use ($request, $userId, $mode, $monthStart, $monthEnd) {
            if ($mode === 'weekly') {
                // Map day_index (0=Sat, 1=Sun... 6=Fri) to assignments
                $pattern = [];
                foreach ($request->input('schedules') as $entry) {
                    $pattern[$entry['day_index']][$entry['employee_id']] = $entry['shift_type'];
                }

                // Expand pattern to every day of the month
                $cursor = $monthStart->copy();
                while ($cursor->lte($monthEnd)) {
                    // Carbon's dayOfWeek is 0=Sun... 6=Sat. 
                    // Our pattern is 0=Sat, 1=Sun, 2=Mon, 3=Tue, 4=Wed, 5=Thu, 6=Fri.
                    // Conversion:
                    $carbonDow = $cursor->dayOfWeek; // 0-6
                    $dayIndexMap = [6 => 0, 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6];
                    $myIndex = $dayIndexMap[$carbonDow];

                    if (isset($pattern[$myIndex])) {
                        foreach ($pattern[$myIndex] as $empId => $shiftType) {
                            RosterSchedule::updateOrCreate(
                                ['employee_id' => $empId, 'date' => $cursor->toDateString()],
                                ['shift_type'  => $shiftType, 'created_by' => $userId]
                            );
                        }
                    }
                    $cursor->addDay();
                }
            } else {
                // Monthly: Save day-by-day as before
                foreach ($request->input('schedules') as $entry) {
                    RosterSchedule::updateOrCreate(
                        ['employee_id' => $entry['employee_id'], 'date' => $entry['date']],
                        ['shift_type'  => $entry['shift_type'],  'created_by' => $userId]
                    );
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Roster saved successfully.']);
    }

    public function importPrevious(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'group' => 'required|string'
        ]);

        $currentMonth = $request->input('month');
        $prevMonth = Carbon::parse($currentMonth . '-01')->subMonth();
        $startOfPrev = $prevMonth->copy()->startOfMonth();

        // Find first Saturday of previous month
        $firstSaturday = $startOfPrev->copy();
        while ($firstSaturday->dayOfWeek !== 6) { // 6 = Saturday
            $firstSaturday->addDay();
        }
        $endOfWeek = $firstSaturday->copy()->addDays(6); // Friday

        // Get schedules for that 7-day period
        $schedules = RosterSchedule::whereBetween('date', [
            $firstSaturday->toDateString(), 
            $endOfWeek->toDateString()
        ])->get();

        $pattern = [];
        foreach ($schedules as $s) {
            $carbonDow = Carbon::parse($s->date)->dayOfWeek;
            // Map Carbon (0=Sun...6=Sat) to Roster (0=Sat...6=Fri)
            $dayIndexMap = [
                6 => 0, // Saturday
                0 => 1, // Sunday
                1 => 2, // Monday
                2 => 3, // Tuesday
                3 => 4, // Wednesday
                4 => 5, // Thursday
                5 => 6, // Friday
            ];
            $myIndex = $dayIndexMap[$carbonDow];
            $pattern[$myIndex][$s->employee_id] = $s->shift_type;
        }

        return response()->json($pattern);
    }

    public function employees(Request $request)
    {
        $groupSlug = $request->group ?? 'noc-borak';
        $groupLabel = self::GROUP_MAP[$groupSlug] ?? 'NOC (Borak)';

        $query = Employee::whereHas('officeTime', fn($q) => $q->where('shift_name', 'Roster'))
            ->where('status', 'active');

        if (in_array($groupSlug, ['tech-jessore'])) {
            $query->where(function($q) use ($groupLabel) {
                $q->where('roster_group', $groupLabel)
                  ->orWhere('roster_group', 'All')
                  ->orWhereNull('roster_group');
            });
        } else {
            $query->where('roster_group', $groupLabel);
        }

        $employees = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($employees);
    }
}
