<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AttendancesExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithDrawings, WithCustomStartCell
{
    use Exportable;

    protected $request;
    protected $format;

    public function __construct($request, $format = 'excel')
    {
        $this->request = $request;
        $this->format = $format;
    }

    public function startCell(): string
    {
        return ($this->format === 'csv') ? 'A1' : 'A5';
    }

    public function drawings()
    {
        if ($this->format === 'csv') {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Mir Telecom Logo');
        $drawing->setDescription('Mir Telecom Logo');
        $drawing->setPath(public_path('images/Mirtel Group Logo .png'));
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(10);

        return $drawing;
    }

    public function collection()
    {
        $date         = $this->request['date'] ?? now()->toDateString();
        $departmentId = $this->request['department_id'] ?? null;
        $officeId     = $this->request['office_id'] ?? null;
        $status       = $this->request['status'] ?? null;
        $search       = $this->request['search'] ?? null;

        $query = AttendanceRecord::with(['employee.department', 'employee.designation', 'employee.office'])
            ->whereHas('employee', function ($q) {
                $q->where('status', 'active');
            })
            ->where('date', $date);

        if ($departmentId) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $departmentId));
        }

        if ($officeId) {
            $query->whereHas('employee', fn($q) => $q->where('office_id', $officeId));
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        $records = $query->get();
        return $records;
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Department/Designation',
            'In Time',
            'Out Time',
            'Working Hours',
            'Late (H:M:S)',
            'Status',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // Employee
            'B' => 25, // Dept/Desig
            'C' => 15, // In Time
            'D' => 15, // Out Time
            'E' => 15, // Working Hours
            'F' => 15, // Late
            'G' => 12, // Status
        ];
    }

    /**
    * @var AttendanceRecord $record
    */
    public function map($record): array
    {
        return [
            $record->employee->name . ' (' . $record->employee->employee_code . ')',
            ($record->employee->department->name ?? 'N/A') . ' / ' . ($record->employee->designation->name ?? 'N/A'),
            $record->in_time ? $record->in_time->format('h:i A') : '-',
            $record->out_time ? $record->out_time->format('h:i A') : '-',
            $record->working_hours . 'h',
            $record->late_timing,
            $record->status === 'weekly_holiday' ? ($record->employee->roster_group ? 'Off Day' : 'Weekly Holiday') : ($record->status === 'off_day' ? 'Off Day' : ucfirst($record->status)),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = 'G';
        
        if ($this->format !== 'csv') {
            $sheet->getRowDimension(1)->setRowHeight(60);
            $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('B2')->getFont()->setSize(10);
            
            $headerArea = "A1:{$lastCol}4";
            $sheet->getStyle($headerArea)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFFFF');
            
            $title = 'ATTENDANCE REPORT - ' . ($this->request['date'] ?? now()->toDateString());
            $sheet->setCellValue('G4', $title);
            $sheet->getStyle("A4:{$lastCol}4")->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle("A4:{$lastCol}4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $headerRange = "A5:{$lastCol}5";
            $dataStartRow = 6;
            $tableStartRow = 5;
        } else {
            $headerRange = "A1:{$lastCol}1";
            $dataStartRow = 2;
            $tableStartRow = 1;
        }

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF007A10'],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $highestRow = $sheet->getHighestRow();
        $fullTableRange = "A{$tableStartRow}:{$lastCol}{$highestRow}";
        $dataRange = "A{$dataStartRow}:{$lastCol}{$highestRow}";
        
        $sheet->getStyle($dataRange)->getAlignment()->applyFromArray([
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            'wrapText' => true,
        ]);
        
        $sheet->getStyle($fullTableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $sheet->freezePane("A{$dataStartRow}");

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                
                if ($this->format === 'csv') return;

                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('B1', 'Mir Telecom Ltd.');
                $sheet->setCellValue('B2', 'House-04, Road-21, Gulshan-1, Dhaka-1212');
                $sheet->mergeCells("B1:E1");
                $sheet->mergeCells("B2:E2");
                $sheet->setShowGridlines(false);
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4);
            },
        ];
    }
}
