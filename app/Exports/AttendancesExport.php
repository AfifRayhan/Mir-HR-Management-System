<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Illuminate\Support\Str;

class AttendancesExport implements FromView, WithTitle, ShouldAutoSize, WithDrawings, WithStyles, WithCustomStartCell, WithEvents
{
    use Exportable;

    protected $request;
    protected $format;
    protected $selectedOffice;

    public function __construct($request, $format = 'excel')
    {
        $this->request = $request;
        $this->format = $format;

        if (isset($this->request['office_id'])) {
            $this->selectedOffice = \App\Models\Office::find($this->request['office_id']);
        }
    }

    public function title(): string
    {
        return 'Daily Attendance Report';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function drawings()
    {
        if ($this->format === 'csv') {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Office Logo');
        $drawing->setDescription('Office Logo');
        
        $logoPath = public_path('images/MIRORIGINAL.jpeg');
        if ($this->selectedOffice && $this->selectedOffice->logo) {
            $officeLogo = $this->selectedOffice->logo;
            $resolvedLogoPath = Str::startsWith($officeLogo, 'images/')
                ? public_path($officeLogo)
                : storage_path('app/public/' . $officeLogo);

            if (file_exists($resolvedLogoPath)) {
                $logoPath = $resolvedLogoPath;
            }
        }

        $drawing->setPath($logoPath);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(10);

        return $drawing;
    }

    public function view(): View
    {
        $date         = $this->request['date'] ?? now()->toDateString();
        $departmentId = $this->request['department_id'] ?? null;
        $officeId     = $this->request['office_id'] ?? null;
        $status       = $this->request['status'] ?? null;
        $search       = $this->request['search'] ?? null;

        $query = AttendanceRecord::with(['employee.department', 'employee.designation', 'employee.office'])
            ->join('employees', 'attendance_records.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->join('designations', 'employees.designation_id', '=', 'designations.id')
            ->where('employees.status', 'active')
            ->where('attendance_records.date', $date)
            ->select('attendance_records.*');

        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }

        if ($officeId) {
            $query->where('employees.office_id', $officeId);
        }

        if ($status) {
            $query->where('attendance_records.status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%")
                  ->orWhere('employees.employee_code', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('employees.office_id')
            ->orderBy('departments.order_sequence')
            ->orderBy('designations.priority')
            ->orderBy('employees.id')
            ->orderBy('employees.name')
            ->get();

        return view('personnel.attendance.exports.daily-excel', [
            'records' => $records,
            'date' => $date,
            'selectedOffice' => $this->selectedOffice
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return []; // Styles are mostly handled in the blade via HTML styles
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                if ($this->format === 'csv') return;

                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('B1', $this->selectedOffice->name ?? 'The Mir Group');
                $sheet->setCellValue('B2', 'House-04, Road-21, Gulshan-1, Dhaka-1212');
                $sheet->mergeCells("B1:E1");
                $sheet->mergeCells("B2:E2");
                $sheet->setShowGridlines(false);
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
            },
        ];
    }
}
