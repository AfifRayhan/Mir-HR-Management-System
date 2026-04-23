<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
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

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithDrawings, WithCustomStartCell
{
    use Exportable;

    protected $request;
    protected $columns;
    protected $format;

    public const DEFAULT_COLUMNS = [
        'employee_code', 'name', 'department', 'section', 'designation',
        'office', 'contact_no', 'joining_date', 'status',
    ];

    public function __construct($request, $columns = null, $isPdf = false, $format = 'excel')
    {
        $this->request = $request;
        $this->format = $format;
        $allDefs = array_keys(self::getColumnDefinitions());
        $selected = $columns
            ? array_values(array_intersect($columns, $allDefs))
            : self::DEFAULT_COLUMNS;

        // Enforce max 9 columns for PDF
        if ($isPdf && count($selected) > 9) {
            $selected = array_slice($selected, 0, 9);
        }

        $this->columns = $selected;
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

    public static function getColumnDefinitions(): array
    {
        return [
            'employee_code' => 'Employee Code',
            'name' => 'Full Name',
            'email' => 'Corporate Email',
            'personal_email' => 'Personal Email',
            'phone' => 'Phone',
            'blood_group' => 'Blood Group',
            'father_name' => 'Father Name',
            'mother_name' => 'Mother Name',
            'spouse_name' => 'Spouse Name',
            'gender' => 'Gender',
            'religion' => 'Religion',
            'marital_status' => 'Marital Status',
            'national_id' => 'National ID',
            'tin' => 'TIN',
            'nationality' => 'Nationality',
            'no_of_children' => 'No. of Children',
            'contact_no' => 'Contact No',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_relation' => 'Emergency Contact Relation',
            'emergency_contact_no' => 'Emergency Contact No',
            'emergency_contact_address' => 'Emergency Contact Address',
            'date_of_birth' => 'Date of Birth',
            'joining_date' => 'Joining Date',
            'discontinuation_date' => 'Discontinuation Date',
            'discontinuation_reason' => 'Discontinuation Reason',
            'present_address' => 'Present Address',
            'permanent_address' => 'Permanent Address',
            'department' => 'Department',
            'section' => 'Section',
            'designation' => 'Designation',
            'grade' => 'Grade',
            'office' => 'Office',
            'office_time' => 'Office Time',
            'gross_salary' => 'Gross Salary',
            'status' => 'Status',
        ];
    }

    public static function getColumnValue($employee, string $key)
    {
        return match($key) {
            'employee_code' => $employee->employee_code,
            'name' => $employee->name,
            'email' => $employee->email,
            'personal_email' => $employee->personal_email,
            'phone' => $employee->phone,
            'blood_group' => $employee->blood_group,
            'father_name' => $employee->father_name,
            'mother_name' => $employee->mother_name,
            'spouse_name' => $employee->spouse_name,
            'gender' => $employee->gender,
            'religion' => $employee->religion,
            'marital_status' => $employee->marital_status,
            'national_id' => $employee->national_id,
            'tin' => $employee->tin,
            'nationality' => $employee->nationality,
            'no_of_children' => $employee->no_of_children,
            'contact_no' => $employee->contact_no,
            'emergency_contact_name' => $employee->emergency_contact_name,
            'emergency_contact_relation' => $employee->emergency_contact_relation,
            'emergency_contact_no' => $employee->emergency_contact_no,
            'emergency_contact_address' => $employee->emergency_contact_address,
            'date_of_birth' => $employee->date_of_birth,
            'joining_date' => $employee->joining_date,
            'discontinuation_date' => $employee->discontinuation_date,
            'discontinuation_reason' => $employee->discontinuation_reason,
            'present_address' => $employee->present_address,
            'permanent_address' => $employee->permanent_address,
            'department' => $employee->department->name ?? 'N/A',
            'section' => $employee->section->name ?? 'N/A',
            'designation' => $employee->designation->name ?? 'N/A',
            'grade' => $employee->grade->name ?? 'N/A',
            'office' => $employee->office->name ?? 'N/A',
            'office_time' => $employee->officeTime->shift_name ?? 'N/A',
            'gross_salary' => $employee->gross_salary,
            'status' => ucfirst($employee->status),
            default => '',
        };
    }

    public function query()
    {
        $query = Employee::with(['department', 'section', 'designation', 'grade', 'office', 'officeTime', 'user']);

        // Search
        if ($this->request['search'] ?? null) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->request['search'] . '%')
                    ->orWhere('employee_code', 'like', '%' . $this->request['search'] . '%');
            });
        }

        // Filters
        if ($this->request['department_id'] ?? null) {
            $query->where('department_id', $this->request['department_id']);
        }
        if ($this->request['section_id'] ?? null) {
            $query->where('section_id', $this->request['section_id']);
        }
        if ($this->request['designation_id'] ?? null) {
            $query->where('designation_id', $this->request['designation_id']);
        }
        if ($this->request['status'] ?? null) {
            $query->where('status', $this->request['status']);
        }

        // Sorting
        $sortColumn = $this->request['sort'] ?? 'created_at';
        $sortDirection = $this->request['direction'] ?? 'asc';
        
        if ($sortColumn === 'employee_code') {
            $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
                  ->orderBy('employee_code', $sortDirection);
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query;
    }

    public function headings(): array
    {
        $defs = self::getColumnDefinitions();
        return array_map(fn($key) => $defs[$key], $this->columns);
    }

    public function columnWidths(): array
    {
        $widths = [];
        $colMap = [
            'employee_code' => 12,
            'name' => 15,
            'email' => 25,
            'personal_email' => 25,
            'phone' => 15,
            'blood_group' => 8,
            'father_name' => 15,
            'mother_name' => 15,
            'gender' => 10,
            'religion' => 10,
            'marital_status' => 12,
            'national_id' => 18,
            'tin' => 15,
            'nationality' => 12,
            'contact_no' => 15,
            'date_of_birth' => 12,
            'joining_date' => 12,
            'department' => 18,
            'section' => 18,
            'designation' => 16,
            'grade' => 10,
            'office' => 20,
            'office_time' => 15,
            'gross_salary' => 12,
            'status' => 10,
        ];

        foreach ($this->columns as $index => $key) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
            $width = $colMap[$key] ?? 15;
            
            // For first column (A), ensure enough width for logo in non-CSV
            if ($index === 0 && $this->format !== 'csv') {
                $width = max($width, 20);
            }
            
            $widths[$columnLetter] = $width;
        }

        return $widths;
    }

    /**
    * @var Employee $employee
    */
    public function map($employee): array
    {
        return array_map(fn($key) => self::getColumnValue($employee, $key), $this->columns);
    }

    public function styles(Worksheet $sheet)
    {
        $colCount = count($this->columns);
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
        
        if ($this->format !== 'csv') {
            // Set row height for logo area
            $sheet->getRowDimension(1)->setRowHeight(60);

            // Header styling (Mir Telecom Ltd.)
            $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('B1')->getAlignment()->setWrapText(false);
            $sheet->getStyle('B2')->getFont()->setSize(10);
            $sheet->getStyle('B2')->getAlignment()->setWrapText(false);
            
            // White background for the logo/header area to remove gridlines
            $headerArea = "A1:{$lastCol}4";
            $sheet->getStyle($headerArea)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFFFF');
            
            // Report Title (EMPLOYEE LIST)
            $sheet->getStyle("A4:{$lastCol}4")->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle("A4:{$lastCol}4")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            // Table Headings styling (Row 5)
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

        // Content styling
        $highestRow = $sheet->getHighestRow();
        $fullTableRange = "A{$tableStartRow}:{$lastCol}{$highestRow}";
        $dataRange = "A{$dataStartRow}:{$lastCol}{$highestRow}";
        
        $sheet->getStyle($dataRange)->getAlignment()->applyFromArray([
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            'wrapText' => true,
        ]);
        
        // Borders for the table
        $sheet->getStyle($fullTableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // If many columns, reduce font size to prevent cutoff in PDF
        if ($colCount > 8) {
            $fontSize = $colCount > 15 ? 7 : 8;
            $sheet->getStyle($fullTableRange)->getFont()->setSize($fontSize);
        }

        $sheet->freezePane("A{$dataStartRow}");

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                if ($this->format === 'csv') {
                    return;
                }

                $sheet = $event->sheet->getDelegate();
                $colCount = count($this->columns);
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

                // Company Name & Address
                $sheet->setCellValue('B1', 'Mir Telecom Ltd.');
                $sheet->setCellValue('B2', 'House-04, Road-21, Gulshan-1, Dhaka-1212');

                // Merge these to prevent wrapping in PDF
                $mergeEnd = $colCount >= 5 ? 'E' : $lastCol;
                $sheet->mergeCells("B1:{$mergeEnd}1");
                $sheet->mergeCells("B2:{$mergeEnd}2");


                // Disable gridlines for the whole sheet
                $sheet->setShowGridlines(false);

                // Set to Landscape for better fit in PDF
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4);
                
                // Set narrow margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setRight(0.3);
                $sheet->getPageMargins()->setLeft(0.3);
                $sheet->getPageMargins()->setBottom(0.5);
            },
        ];
    }
}
