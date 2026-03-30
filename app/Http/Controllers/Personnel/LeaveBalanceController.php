<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Carbon\Carbon;

class LeaveBalanceController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        $employees  = Employee::with('department', 'designation')->where('status', 'active')->orderBy('name')->get();
        $leaveTypes = LeaveType::orderBy('sort_order')->get();

        $currentYear = date('Y');

        // Eager load balances for the current year to show in the list
        $balances = LeaveBalance::with(['employee', 'leaveType'])
            ->where('year', $currentYear)
            ->get()
            ->groupBy('employee_id');

        return view('personnel.leave-balances.index', compact(
            'employees', 'balances', 'leaveTypes', 'currentYear', 'user', 'roleName', 'employee'
        ));
    }

    /**
     * Return already-initialized leave_type_ids for an employee + year (AJAX).
     */
    public function existing(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year'        => 'required|integer|min:2020|max:2050',
        ]);

        $initialized = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('year', $request->year)
            ->pluck('leave_type_id');

        return response()->json(['initialized' => $initialized]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'year'             => 'required|integer|min:2020|max:2050',
            'leave_type_ids'   => 'required|array|min:1',
            'leave_type_ids.*' => 'exists:leave_types,id',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $selectedTypes = LeaveType::whereIn('id', $request->leave_type_ids)->get();

        $initializedCount = 0;
        $skippedCount     = 0;

        foreach ($selectedTypes as $type) {
            $exists = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('year', $request->year)
                ->exists();

            if (!$exists) {
                LeaveBalance::create([
                    'employee_id'     => $employee->id,
                    'leave_type_id'   => $type->id,
                    'year'            => $request->year,
                    'opening_balance' => $type->total_days_per_year,
                    'used_days'       => 0,
                    'remaining_days'  => $type->total_days_per_year,
                ]);
                $initializedCount++;
            } else {
                $skippedCount++;
            }
        }

        if ($initializedCount > 0) {
            $msg = "Initialized {$initializedCount} leave type(s) for {$employee->name} ({$request->year}).";
            if ($skippedCount > 0) {
                $msg .= " {$skippedCount} type(s) were already set up and skipped.";
            }
            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with('error', "All selected leave types for {$employee->name} ({$request->year}) were already initialized.");
    }
}
