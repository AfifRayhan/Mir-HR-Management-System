<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Cache;
use App\Models\Office;
use App\Models\Holiday;
use App\Models\WeeklyHoliday;
use App\Models\LeaveApplication;
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

class MonthlyAttendanceExport implements FromView, WithTitle, ShouldAutoSize, WithDrawings, WithStyles, WithCustomStartCell, WithEvents
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
        return 'Monthly Attendance Report';
    }

    public function view(): View
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $month = $this->params['month'] ?? date('m');
        $year = $this->params['year'] ?? date('Y');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $query = Employee::with(['department', 'designation', 'office'])
            ->where('status', 'active');

        if (!empty($this->params['office_id'])) {
            $query->where('office_id', $this->params['office_id']);
        }
        if (!empty($this->params['department_id'])) {
            $query->where('department_id', $this->params['department_id']);
        }

        $holidays = Holiday::where(function($q) use ($month, $year) {
                $q->whereYear('from_date', $year)->whereMonth('from_date', $month)
                  ->orWhereYear('to_date', $year)->whereMonth('to_date', $month);
            })->get();

        $weeklyHolidays = WeeklyHoliday::all()->groupBy('office_id');
        $processedData = [];

        $query->chunk(100, function ($employees) use (&$processedData, $month, $year, $daysInMonth, $holidays, $weeklyHolidays) {
            $employeeIds = $employees->pluck('id');

            $attendance = AttendanceRecord::whereIn('employee_id', $employeeIds)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->groupBy('employee_id');

            $leaves = LeaveApplication::whereIn('employee_id', $employeeIds)
                ->where('status', 'approved')
                ->where(function($q) use ($month, $year) {
                    $q->whereYear('from_date', $year)->whereMonth('from_date', $month)
                      ->orWhereYear('to_date', $year)->whereMonth('to_date', $month);
                })->get()
                ->groupBy('employee_id');

            foreach ($employees as $index => $emp) {
                $empAttendance = $attendance->get($emp->id, collect())->keyBy(fn($item) => (int)$item->date->format('d'));
                $empLeaves = $leaves->get($emp->id, collect());
                $empWeeklyHolidays = $weeklyHolidays->get($emp->office_id, collect())->pluck('day')->toArray();
                
                $days = [];
                $summary = ['P' => 0, 'A' => 0, 'LP' => 0, 'LA' => 0, 'L' => 0, 'H' => 0, 'WD' => 0];

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $date = Carbon::createFromDate($year, $month, $d);
                    $dateStr = $date->toDateString();
                    $dayName = $date->format('l');

                    $isWorkingDay = true;
                    if (in_array($dayName, $empWeeklyHolidays)) {
                        $isWorkingDay = false;
                    } else {
                        $isGenHoliday = $holidays->first(function($h) use ($dateStr) {
                            return $dateStr >= $h->from_date && $dateStr <= $h->to_date;
                        });
                        if ($isGenHoliday) $isWorkingDay = false;
                    }

                    $status = '';
                    if (isset($empAttendance[$d])) {
                        $record = $empAttendance[$d];
                        if ($record->status === 'present') { $status = 'P'; $summary['P']++; }
                        elseif ($record->status === 'late') { $status = 'LP'; $summary['LP']++; }
                        elseif ($record->status === 'absent') { $status = 'A'; $summary['A']++; }
                        elseif ($record->status === 'leave') { $status = 'L'; $summary['L']++; }
                    } else {
                        $onLeave = $empLeaves->first(function($leave) use ($dateStr) {
                            return $dateStr >= $leave->from_date && $dateStr <= $leave->to_date;
                        });

                        if ($onLeave) { $status = 'L'; $summary['L']++; }
                        elseif (!$isWorkingDay) { $status = 'H'; $summary['H']++; }
                        else { $status = 'A'; $summary['A']++; }
                    }
                    
                    if ($isWorkingDay) $summary['WD']++;
                    $days[$d] = $status;
                }

                $processedData[] = [
                    'employee' => $emp,
                    'days' => $days,
                    'summary' => $summary,
                    'office_name' => $emp->office->name ?? 'Unassigned',
                    'department_name' => $emp->department->name ?? 'Unassigned'
                ];
            }
        });

        $groupedData = collect($processedData)->groupBy('office_name')->map(function($officeItems) {
            return $officeItems->groupBy('department_name');
        });

        $format = $this->params['format'] ?? 'excel';
        $viewName = 'personnel.attendance.exports.monthly-excel';
        if ($format === 'pdf') {
            $viewName = 'personnel.attendance.exports.monthly-pdf';
        } elseif ($format === 'word') {
            $viewName = 'personnel.attendance.exports.monthly-word';
        }

        return view($viewName, [
            'groupedData' => $groupedData,
            'daysInMonth' => $daysInMonth,
            'monthName' => date('F', mktime(0, 0, 0, $month, 1)),
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

        $month = $this->params['month'] ?? date('m');
        $year = $this->params['year'] ?? date('Y');
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($daysInMonth + 10);
        
        $highestRow = $sheet->getHighestRow();
        
        // Table data styling - starting from Row 6 (where the data table starts)
        $sheet->getStyle("A6:{$lastColLetter}{$highestRow}")->getAlignment()->applyFromArray([
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'wrapText' => true,
        ]);

        // Left align Emp Name and Designation
        $sheet->getStyle("B6:C{$highestRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Apply borders to the table area
        $sheet->getStyle("A6:{$lastColLetter}{$highestRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Freeze panes: Keep headers and identity columns visible
        $sheet->freezePane('D8');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                if (($this->params['format'] ?? 'excel') === 'csv') return;

                $sheet = $event->sheet->getDelegate();

                // Set header row height for the logo area
                $sheet->getRowDimension(1)->setRowHeight(70);
                
                $sheet->setShowGridlines(false);
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4);
            },
        ];
    }
}
