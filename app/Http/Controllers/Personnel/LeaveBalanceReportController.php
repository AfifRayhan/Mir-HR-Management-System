<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Exports\LeaveBalanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;

class LeaveBalanceReportController extends Controller
{
    public function preview(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employeeRecord = Employee::where('user_id', $user->id)->first();

        $employees = Employee::with(['department', 'designation'])->orderBy('name')->get();
        $selectedEmployeeId = $request->input('employee_id');
        $year = $request->input('year', now()->year);

        $leaveBalances = collect();
        if ($selectedEmployeeId) {
            $leaveBalances = LeaveBalance::with('leaveType')
                ->where('employee_id', $selectedEmployeeId)
                ->where('year', $year)
                ->get();
        }

        return view('personnel.reports.leave-balance.preview', compact(
            'employees', 'selectedEmployeeId', 'year', 'leaveBalances', 'user', 'roleName', 'employeeRecord'
        ));
    }

    public function exportExcel(Request $request)
    {
        $params = $request->all();
        $params['format'] = 'excel';
        $filename = 'leave_balance_' . $params['employee_id'] . '_' . $params['year'] . '.xlsx';
        return Excel::download(new LeaveBalanceExport($params), $filename);
    }

    public function exportCsv(Request $request)
    {
        $params = $request->all();
        $params['format'] = 'csv';
        $filename = 'leave_balance_' . $params['employee_id'] . '_' . $params['year'] . '.csv';
        return Excel::download(new LeaveBalanceExport($params), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        
        $params = $request->all();
        $params['format'] = 'pdf';
        $export = new LeaveBalanceExport($params);
        $view = $export->view();
        
        return Pdf::loadView($view->name(), $view->getData())
            ->setPaper('a4', 'portrait')
            ->download('leave_balance_' . $params['employee_id'] . '_' . $params['year'] . '.pdf');
    }

    public function exportWord(Request $request)
    {
        $params = $request->all();
        $params['format'] = 'word';
        $export = new LeaveBalanceExport($params);
        $view = $export->view();
        $filename = 'leave_balance_' . $params['employee_id'] . '_' . $params['year'] . '.doc';

        return response($view->render())
            ->header('Content-Type', 'application/vnd.ms-word')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
