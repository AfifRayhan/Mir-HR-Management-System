<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\WeeklyHoliday;
use App\Models\Holiday;
use App\Models\RosterSchedule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class OvertimeExport implements FromArray, WithStyles, WithEvents, WithDrawings, WithColumnWidths
{
    /** @var array */
    protected $params;

    /** @var array|null */
    protected $dataCache = null;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function view(): View
    {
        $data = $this->getData();
        return view('personnel.overtimes.export', $data);
    }

    public function array(): array
    {
        $data = $this->getData();
        $employee = $data['employee'];
        $records = $data['records'];
        $daysInMonth = $data['daysInMonth'];
        $monthName = $data['monthName'];

        $rows = [];

        // 1. Spacing for Logo (Rows 1-2)
        $rows[] = ['', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', ''];

        // 2. Header Info (Rows 3-5)
        $rows[] = ['OT Month:', $monthName, '', 'Employee:', $employee->name];
        $rows[] = ['ID:', $employee->employee_code, '', 'Designation:', $employee->designation->name ?? ''];
        $rows[] = ['Gross Salary:', number_format((float)$employee->gross_salary, 0), '', 'Department:', $employee->department->name ?? ''];

        // 3. Spacing (Row 6)
        $rows[] = ['', '', '', '', '', '', '', '', ''];

        // 4. Title (Row 7)
        $rows[] = ['Over Time Payment Request Form', '', '', '', '', '', '', '', ''];

        // 5. Spacing (Row 8)
        $rows[] = ['', '', '', '', '', '', '', '', ''];

        // 6. Table Headings (Row 9)
        $rows[] = ['Date', 'In', 'Out', 'Hours', 'Workday Duty (+5 hrs)', 'Holiday Duty (+5 hrs)', 'Eid Special Duty', 'Remarks', 'Amount'];

        // 7. Data Rows (Starting Row 10)
        foreach ($daysInMonth as $day) {
            $dateStr = $day->toDateString();
            $record = $records->get($dateStr);
            
            $rows[] = [
                $day->format('l, M d'),
                $record ? $record->ot_start : '',
                $record ? $record->ot_stop : '',
                $record ? number_format((float)$record->total_ot_hours, 2) : '0.00',
                ($record && $record->is_workday_duty_plus_5) ? 'Yes' : '',
                ($record && $record->is_holiday_duty_plus_5) ? 'Yes' : '',
                ($record && $record->is_eid_duty) ? 'Yes' : '',
                $record ? $record->remarks : '',
                $record ? number_format((float)$record->amount, 2) : '0.00'
            ];
        }

        // 8. Summary (4 rows)
        $gross = $employee->gross_salary;
        $basic = $gross * 0.6;
        $perDay = $basic / 30;

        $rows[] = [
            'Gross Salary:', number_format((float)$gross, 0),
            'Total hours/Shift', number_format((float)$data['hourlyOTHours'], 2),
            $data['workdayCount'], $data['holidayCount'], $data['eidCountDisplay'],
            '', ''
        ];
        $rows[] = [
            'Basic Salary', number_format((float)$basic, 2),
            'Rate per hour/Shift/ Eid Special', number_format((float)$data['perHourRate'], 2),
            number_format((float)$data['incomeBase'], 2), number_format((float)$data['incomeBase'], 2), number_format((float)$data['incomeBase'], 2),
            '', ''
        ];
        $rows[] = [
            'Per Day', number_format((float)$perDay, 2),
            'Multiplying Factor', '1', '2', '2', '3',
            '', ''
        ];
        $rows[] = [
            'Per Hour', number_format((float)$data['perHourRate'], 2),
            'Sub-Total', number_format((float)($data['perHourRate'] * $data['hourlyOTHours']), 2),
            number_format((float)($data['incomeBase'] * $data['workdayCount'] * 2), 2),
            number_format((float)($data['incomeBase'] * $data['holidayCount'] * 2), 2),
            number_format((float)($data['incomeBase'] * $data['eidCountDisplay'] * 3), 2),
            '', ''
        ];

        // 9. Total Payable (1 row)
        $rows[] = ['', '', '', '', '', '', '', 'Total Payable Amount', number_format((float)$data['totalPayable'], 2)];

        // 10. Signature Spacing
        $rows[] = ['', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', ''];

        // 11. Signatures
        $rows[] = [
            $employee->name, '', '',
            $employee->reportingManager->name ?? 'Supervisor', '', '',
            'Md. Riaz Mahmud', '', ''
        ];
        $rows[] = [
            'Payment Requested by', '', '',
            'Supervisor', '', '',
            'Head of HR & Administration', '', ''
        ];

        return $rows;
    }

    private function getData()
    {
        if ($this->dataCache) return $this->dataCache;

        $employeeId = $this->params['employee_id'];
        $month = $this->params['month'];
        $year = $this->params['year'];

        $employee = Employee::with(['department', 'designation', 'reportingManager', 'office'])->findOrFail($employeeId);
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $daysInMonth = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $daysInMonth[] = $date->copy();
        }

        $records = Overtime::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function($r) {
                return Carbon::parse($r->date)->toDateString();
            });

        // Fetch Weekly Holidays
        $weeklyHolidays = WeeklyHoliday::where('is_holiday', true)
            ->where(function ($q) use ($employee) {
                $q->where('office_id', $employee->office_id)->orWhereNull('office_id');
            })
            ->pluck('day_name')
            ->toArray();

        // Fetch Holidays
        $holidays = Holiday::where('is_active', true)
            ->where(function ($q) use ($employee) {
                $q->where('all_office', true)->orWhere('office_id', $employee->office_id);
            })
            ->get()
            ->mapWithKeys(function ($h) {
                $dates = [];
                $c = Carbon::parse($h->from_date);
                $e = Carbon::parse($h->to_date);
                while ($c->lte($e)) {
                    $dates[$c->toDateString()] = [
                        'name' => $h->name,
                        'type' => $h->type,
                    ];
                    $c->addDay();
                }
                return $dates;
            })
            ->toArray();

        // Fetch Roster Schedules
        $rosterSchedules = RosterSchedule::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(function($r) {
                return Carbon::parse($r->date)->toDateString();
            });

        $perHourRate = $this->calculatePerHourRate($employee);
        $summary = $this->calculateSummary($employee, $daysInMonth, $records, $perHourRate);

        $this->dataCache = array_merge([
            'employee' => $employee,
            'month' => $month,
            'year' => $year,
            'monthName' => $startDate->format('F'),
            'daysInMonth' => $daysInMonth,
            'records' => $records,
            'perHourRate' => $perHourRate,
            'weeklyHolidays' => $weeklyHolidays,
            'holidays' => $holidays,
            'rosterSchedules' => $rosterSchedules,
        ], $summary);

        return $this->dataCache;
    }

    private function calculatePerHourRate(Employee $employee)
    {
        $perHourRate = 0.0;
        
        if ($employee->designation_id) {
            $designationRate = \App\Models\OvertimeRate::where('designation_id', $employee->designation_id)
                ->whereNull('grade_id')
                ->value('rate');
            if ($designationRate !== null) {
                $perHourRate = (float) $designationRate;
            }
        }

        if ($perHourRate === 0.0 && $employee->grade_id) {
            $gradeRate = \App\Models\OvertimeRate::where('grade_id', $employee->grade_id)
                ->whereNull('designation_id')
                ->value('rate');
            if ($gradeRate !== null) {
                $perHourRate = (float) $gradeRate;
            }
        }

        if ($perHourRate > 0) {
            return $perHourRate;
        }

        $basic = $employee->gross_salary * 0.6;
        return round($basic / 208, 2);
    }

    private function calculateSummary(Employee $employee, array $daysInMonth, Collection $records, float $perHourRate)
    {
        $totalPayable = 0;
        $hourlyCount = 0;
        $workdayCount = 0;
        $holidayCount = 0;
        $eidCount = 0;

        foreach ($daysInMonth as $day) {
            $dateStr = $day->toDateString();
            $record = $records->get($dateStr);

            if ($record) {
                $totalPayable += $record->amount;

                if ($record->is_workday_duty_plus_5 || $record->is_holiday_duty_plus_5 || $record->is_eid_duty) {
                    if ($record->is_workday_duty_plus_5) $workdayCount++;
                    if ($record->is_holiday_duty_plus_5) $holidayCount++;
                    if ($record->is_eid_duty) $eidCount++;
                } else {
                    $hourlyCount += floor($record->total_ot_hours);
                }
            }
        }

        $incomeBase = ($employee->gross_salary * 0.6) / 30;

        return [
            'incomeBase' => $incomeBase,
            'hourlyOTHours' => $hourlyCount,
            'workdayCount' => $workdayCount,
            'holidayCount' => $holidayCount,
            'eidCountDisplay' => $eidCount,
            
            // Explicit unit totals for sub-total calculations
            'hourlyUnits' => $hourlyCount,
            'workdayUnits' => $workdayCount * 2,
            'holidayUnits' => $holidayCount * 2,
            'eidUnits' => $eidCount * 3,
            
            'perHourRate' => $perHourRate,
            'totalPayable' => $totalPayable
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 15,
            'D' => 12,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 30,
            'I' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
        // 1. Employee Info Section (Rows 3-5)
        $sheet->getStyle('A3:I5')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        $sheet->getStyle('D3:D5')->getFont()->setBold(true);
        $sheet->getStyle('A5:I5')->getFont()->setBold(true); // Bold Gross Salary row

        // Merge E-I for text fields to remove redundant lines
        $sheet->mergeCells('E3:I3');
        $sheet->mergeCells('E4:I4');
        $sheet->mergeCells('E5:I5');

        // Alignment for header values (Column B)
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // OT Month
        $sheet->getStyle('B4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // ID
        $sheet->getStyle('B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // Gross Salary

        // 2. Form Title (Row 7)
        $sheet->mergeCells('A7:I7');
        $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 3. Main Table (Starting from row 9)
        $sheet->getStyle('A9:I' . ($lastRow - 5))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('A9:I9')->getFont()->setBold(true);
        $sheet->getStyle('A9:I9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
        $sheet->getStyle('A9:I9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle('A9:I' . $lastRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B10:G' . ($lastRow-6))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I10:I' . ($lastRow-5))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I10:I' . ($lastRow-5))->getFont()->setBold(true); // Bold Amount column

        // Footer/Summary Styling
        $summaryStart = $lastRow - 5;
        $sheet->getStyle('A' . ($summaryStart-3) . ':I' . $summaryStart)->getFont()->setBold(true);
        $sheet->getStyle('A' . ($summaryStart-3) . ':I' . $summaryStart)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle('B' . ($summaryStart-3) . ':G' . $summaryStart)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A' . $summaryStart . ':I' . $summaryStart)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF000000');
        $sheet->getStyle('A' . $summaryStart . ':I' . $summaryStart)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A' . $summaryStart . ':I' . $summaryStart)->getFont()->setBold(true);
        $sheet->getStyle('H' . $summaryStart)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $sigNameRow = $lastRow - 1;
        $sigLabelRow = $lastRow;
        $sheet->mergeCells("A{$sigNameRow}:C{$sigNameRow}");
        $sheet->mergeCells("D{$sigNameRow}:F{$sigNameRow}");
        $sheet->mergeCells("G{$sigNameRow}:I{$sigNameRow}");
        $sheet->mergeCells("A{$sigLabelRow}:C{$sigLabelRow}");
        $sheet->mergeCells("D{$sigLabelRow}:F{$sigLabelRow}");
        $sheet->mergeCells("G{$sigLabelRow}:I{$sigLabelRow}");
        $sheet->getStyle("A{$sigNameRow}:I{$sigNameRow}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle("A{$sigNameRow}:I{$sigLabelRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$sigNameRow}:I{$sigNameRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$sigLabelRow}:I{$sigLabelRow}")->getFont()->setSize(8);

        return [];
    }

    public function drawings()
    {
        $logo = $this->getData()['employee']->office->logo ?? 'images/Mirtel Group Logo .png';
        $logoPath = public_path($logo);
        
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/Mirtel Group Logo .png');
        }

        $drawing = new Drawing();
        $drawing->setName('Company Logo');
        $drawing->setPath($logoPath);
        $drawing->setHeight(60);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(5);
        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getRowDimension(1)->setRowHeight(40);
                $sheet->getRowDimension(2)->setRowHeight(40);
                $sheet->setShowGridlines(false);
            },
        ];
    }
}
