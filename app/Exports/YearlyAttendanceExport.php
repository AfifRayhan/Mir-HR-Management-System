<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Support\Facades\Cache;
use App\Models\Holiday;
use App\Models\WeeklyHoliday;
use App\Models\LeaveApplication;
use App\Models\RosterSchedule;
use App\Models\RosterTime;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use App\Services\AttendanceService;

class YearlyAttendanceExport implements FromView, WithTitle, ShouldAutoSize, WithDrawings, WithStyles, WithCustomStartCell, WithEvents
{
    protected $params;
    protected $attendanceService;

    public function __construct($params)
    {
        $this->params = $params;
        $this->attendanceService = app(AttendanceService::class);
    }

    public function title(): string
    {
        return 'Yearly Attendance Report';
    }

    public function view(): View
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1200);

        $year = $this->params['year'] ?? date('Y');

        $query = Employee::with(['department', 'designation', 'office', 'officeTime'])
            ->where('status', 'active');

        if (!empty($this->params['office_id'])) {
            $query->where('office_id', $this->params['office_id']);
        }
        if (!empty($this->params['department_id'])) {
            $query->where('department_id', $this->params['department_id']);
        }

        $employees = $query->get();
        $totalEmployees = count($employees);
        $employeeIds = $employees->pluck('id');

        $attendance = AttendanceRecord::whereIn('employee_id', $employeeIds)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(['employee_id', function ($item) {
                return (int)$item->date->format('m');
            }]);

        $holidays = Holiday::where(function($q) use ($year) {
                $q->whereYear('from_date', $year)
                  ->orWhereYear('to_date', $year);
            })->get();

        $weeklyHolidays = WeeklyHoliday::where('is_holiday', true)->get()->groupBy('office_id');

        $leaves = LeaveApplication::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where(function($q) use ($year) {
                $q->whereYear('from_date', $year)
                  ->orWhereYear('to_date', $year);
            })->get()
            ->groupBy('employee_id');

        $rosterSchedules = RosterSchedule::whereIn('employee_id', $employeeIds)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(['employee_id', function ($item) {
                return (int)Carbon::parse($item->date)->format('m');
            }]);
            
        $rosterTimes = RosterTime::all()->groupBy('group_slug');
        $groupSlugMap = AttendanceService::ROSTER_GROUP_SLUG_MAP;

        $processedData = [];
        foreach ($employees as $index => $emp) {

            $empAttendanceByMonth = $attendance->get($emp->id, collect());
            $empLeaves = $leaves->get($emp->id, collect());
            $empRosterByMonth = $rosterSchedules->get($emp->id, collect());

            $monthlySummaries = [];
            for ($m = 1; $m <= 12; $m++) {
                $summary = ['P' => 0, 'A' => 0, 'LP' => 0, 'LA' => 0, 'L' => 0, 'H' => 0, 'WD' => 0];
                $daysInMonth = Carbon::createFromDate($year, $m, 1)->daysInMonth;
                
                $monthAttendance = $empAttendanceByMonth->get($m, collect())->keyBy(fn($r) => (int)$r->date->format('d'));
                $monthRoster = $empRosterByMonth->get($m, collect())->keyBy(fn($s) => (int)Carbon::parse($s->date)->format('d'));

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $currentDate = Carbon::createFromDate($year, $m, $d);
                    $dateStr = $currentDate->toDateString();
                    $dayName = $currentDate->format('l');

                    // Determine if working day
                    $isWorkingDay = true;
                    if ($emp->officeTime && $emp->officeTime->shift_name === 'Roster') {
                        $sched = $monthRoster->get($d);
                        if ($sched && $sched->shift_type) {
                            $groupSlug = $groupSlugMap[$emp->roster_group] ?? null;
                            $shift = $rosterTimes->get($groupSlug, collect())->where('shift_key', $sched->shift_type)->first();
                            $isWorkingDay = $shift && !$shift->is_off_day;
                        } else {
                            $isWorkingDay = false;
                        }
                    } else {
                        $officeWH = $weeklyHolidays->get($emp->office_id) ?? $weeklyHolidays->get(null);
                        if ($officeWH && $officeWH->contains('day_name', $dayName)) {
                            $isWorkingDay = false;
                        } else {
                            $isGenHoliday = $holidays->first(function($h) use ($dateStr, $emp) {
                                return ($h->all_office || $h->office_id == $emp->office_id) && 
                                       $dateStr >= $h->from_date && $dateStr <= $h->to_date;
                            });
                            if ($isGenHoliday) $isWorkingDay = false;
                        }
                    }

                    if (isset($monthAttendance[$d])) {
                        $record = $monthAttendance[$d];
                        if ($record->status === 'present') $summary['P']++;
                        elseif ($record->status === 'late') $summary['LP']++;
                        elseif ($record->status === 'absent') $summary['A']++;
                        elseif ($record->status === 'leave') $summary['L']++;
                    } else {
                        $onLeave = $empLeaves->first(fn($l) => $dateStr >= $l->from_date && $dateStr <= $l->to_date);
                        if ($onLeave) $summary['L']++;
                        elseif (!$isWorkingDay) $summary['H']++;
                        else $summary['A']++;
                    }

                    if ($isWorkingDay) $summary['WD']++;
                }
                $monthlySummaries[$m] = $summary;
            }

            $processedData[] = [
                'employee' => $emp,
                'monthlySummaries' => $monthlySummaries,
                'office_name' => $emp->office->name ?? 'Unassigned',
                'department_name' => $emp->department->name ?? 'Unassigned'
            ];
        }

        $groupedData = collect($processedData)->groupBy('office_name')->map(function($officeItems) {
            return $officeItems->groupBy('department_name');
        });

        $format = $this->params['format'] ?? 'excel';
        $viewName = 'personnel.attendance.exports.yearly-excel';
        if ($format === 'pdf') {
            $viewName = 'personnel.attendance.exports.yearly-pdf';
        } elseif ($format === 'word') {
            $viewName = 'personnel.attendance.exports.yearly-word';
        }

        return view($viewName, [
            'groupedData' => $groupedData,
            'year' => $year,
            'params' => $this->params
        ]);
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function drawings()
    {
        if (($this->params['format'] ?? 'excel') === 'pdf' || ($this->params['format'] ?? 'excel') === 'csv') {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Mir Telecom Logo');
        $drawing->setDescription('Mir Telecom Logo');
        $drawing->setPath(public_path('images/Mirtel Group Logo .png'));
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(10);

        return $drawing;
    }

    public function styles(Worksheet $sheet)
    {
        if (($this->params['format'] ?? 'excel') === 'pdf' || ($this->params['format'] ?? 'excel') === 'csv') {
            return [];
        }

        $highestRow = $sheet->getHighestRow();
        $lastColLetter = 'L'; // Emp ID, Name, Designation, Month, P, A, LP, LA, L, H, WD, Total
        
        $sheet->getStyle("A6:{$lastColLetter}{$highestRow}")->getAlignment()->applyFromArray([
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'wrapText' => true,
        ]);

        $sheet->getStyle("B6:B{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C6:C{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle("A6:{$lastColLetter}{$highestRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->freezePane('D8');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                if (($this->params['format'] ?? 'excel') === 'csv') return;
                $sheet = $event->sheet->getDelegate();
                $sheet->getRowDimension(1)->setRowHeight(70);
                $sheet->setShowGridlines(false);
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4);
            },
        ];
    }
}
