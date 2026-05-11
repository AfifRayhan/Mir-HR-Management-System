<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\WeeklyHoliday;
use App\Models\LeaveApplication;
use App\Services\AttendanceService;
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
use Illuminate\Support\Str;

class EmployeeLogExport implements FromView, WithTitle, ShouldAutoSize, WithDrawings, WithStyles, WithCustomStartCell, WithEvents
{
    protected $params;
    protected $attendanceService;

    protected $employee;

    public function __construct($params)
    {
        $this->params = $params;
        $this->attendanceService = app(AttendanceService::class);
        $this->employee = Employee::with('office')->find($params['employee_id']);
    }

    public function title(): string
    {
        return 'Employee Attendance Log';
    }

    public function view(): View
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600);

        $employeeId = $this->params['employee_id'];
        $fromDate = $this->params['from_date'];
        $toDate = $this->params['to_date'];
        $format = $this->params['format'] ?? 'excel';

        $employee = Employee::with(['department', 'designation', 'office', 'officeTime'])->findOrFail($employeeId);
        
        $startDate = Carbon::parse($fromDate);
        $endDate = Carbon::parse($toDate);
        
        $attendanceData = AttendanceRecord::where('employee_id', $employeeId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

        $leaves = LeaveApplication::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function($q) use ($fromDate, $toDate) {
                $q->whereBetween('from_date', [$fromDate, $toDate])
                  ->orWhereBetween('to_date', [$fromDate, $toDate]);
            })->get();

        $holidays = Holiday::where(function($q) use ($fromDate, $toDate, $employee) {
                $q->where(function($sq) use ($fromDate, $toDate) {
                    $sq->whereBetween('from_date', [$fromDate, $toDate])
                       ->orWhereBetween('to_date', [$fromDate, $toDate]);
                })->where(function($sq) use ($employee) {
                    $sq->where('all_office', true)
                       ->orWhere('office_id', $employee->office_id);
                });
            })->get();

        $weeklyHolidays = WeeklyHoliday::where('is_holiday', true)
            ->where(function($q) use ($employee) {
                $q->where('office_id', $employee->office_id)
                  ->orWhereNull('office_id');
            })->get();

        $records = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->toDateString();
            $dayName = $current->format('l');
            
            if (isset($attendanceData[$dateStr])) {
                $records[] = $attendanceData[$dateStr];
            } else {
                $isWorkingDay = true;
                if ($weeklyHolidays->contains('day_name', $dayName)) {
                    $isWorkingDay = false;
                } else {
                    $isGenHoliday = $holidays->first(fn($h) => $dateStr >= $h->from_date && $dateStr <= $h->to_date);
                    if ($isGenHoliday) $isWorkingDay = false;
                }

                $onLeave = $leaves->first(fn($l) => $dateStr >= $l->from_date && $dateStr <= $l->to_date);

                $records[] = new AttendanceRecord([
                    'employee_id' => $employeeId,
                    'date' => $dateStr,
                    'status' => $onLeave ? 'leave' : (!$isWorkingDay ? 'holiday' : 'absent'),
                    'late_seconds' => 0
                ]);
            }
            $current->addDay();
        }

        $viewName = 'personnel.attendance.exports.employee-log-' . ($format === 'pdf' ? 'pdf' : ($format === 'word' ? 'word' : 'excel'));
        if ($format === 'excel' && !view()->exists($viewName)) {
            $viewName = 'personnel.attendance.exports.employee-log-excel';
        }

        return view($viewName, [
            'employee' => $employee,
            'records' => collect($records)->sortByDesc('date'),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
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
        $drawing->setName('Office Logo');
        $drawing->setDescription('Office Logo');
        
        $logoPath = public_path('images/MIRORIGINAL.jpeg');
        if ($this->employee && $this->employee->office && $this->employee->office->logo) {
            $officeLogo = $this->employee->office->logo;
            $resolvedLogoPath = Str::startsWith($officeLogo, 'images/')
                ? public_path($officeLogo)
                : storage_path('app/public/' . $officeLogo);

            if (file_exists($resolvedLogoPath)) {
                $logoPath = $resolvedLogoPath;
            }
        }

        $drawing->setPath($logoPath);
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(10);

        return $drawing;
    }

    public function styles(Worksheet $sheet)
    {
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
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
            },
        ];
    }
}
