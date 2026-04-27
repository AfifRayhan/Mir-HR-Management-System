<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\LeaveBalance;
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

class LeaveBalanceExport implements FromView, WithTitle, ShouldAutoSize, WithDrawings, WithStyles, WithCustomStartCell, WithEvents
{
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function title(): string
    {
        return 'Leave Balance Report';
    }

    public function view(): View
    {
        $employeeId = $this->params['employee_id'];
        $year = $this->params['year'];
        $format = $this->params['format'] ?? 'excel';

        $employee = Employee::with(['department', 'designation'])->findOrFail($employeeId);
        
        $leaveBalances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employeeId)
            ->where('year', $year)
            ->get();

        $viewName = 'personnel.reports.leave-balance.export-' . ($format === 'pdf' ? 'pdf' : ($format === 'word' ? 'word' : 'excel'));
        if ($format === 'excel' && !view()->exists($viewName)) {
            $viewName = 'personnel.reports.leave-balance.export';
        }
        if ($format === 'pdf' && !view()->exists($viewName)) {
            $viewName = 'personnel.reports.leave-balance.export';
        }
        if ($format === 'word' && !view()->exists($viewName)) {
            $viewName = 'personnel.reports.leave-balance.export';
        }
        if ($format === 'csv' && !view()->exists($viewName)) {
            $viewName = 'personnel.reports.leave-balance.export';
        }

        return view($viewName, [
            'employee' => $employee,
            'leaveBalances' => $leaveBalances,
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
