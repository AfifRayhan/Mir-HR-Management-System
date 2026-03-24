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

        $employees = Employee::with('department', 'designation')->where('status', 'active')->orderBy('name')->get();

        $currentYear = date('Y');

        // Eager load balances for the current year to show in the list
        $balances = LeaveBalance::with(['employee', 'leaveType'])
            ->where('year', $currentYear)
            ->get()
            ->groupBy('employee_id');

        return view('personnel.leave-balances.index', compact('employees', 'balances', 'currentYear', 'user', 'roleName', 'employee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year' => 'required|integer|min:2020|max:2050',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $leaveTypes = LeaveType::all();

        if ($leaveTypes->isEmpty()) {
            return redirect()->back()->with('error', 'Please configure Leave Types first in Settings.');
        }

        $initializedCount = 0;

        foreach ($leaveTypes as $type) {
            // Only create if it doesn't already exist for this year
            $exists = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('year', $request->year)
                ->exists();

            if (!$exists) {
                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $type->id,
                    'year' => $request->year,
                    'opening_balance' => $type->total_days_per_year,
                    'used_days' => 0,
                    'remaining_days' => $type->total_days_per_year,
                ]);
                $initializedCount++;
            }
        }

        if ($initializedCount > 0) {
            return redirect()->back()->with('success', "Leave accounts initialized for {$employee->name} ({$request->year}). Added {$initializedCount} types.");
        } else {
            return redirect()->back()->with('error', "Leave accounts for {$employee->name} ({$request->year}) were already initialized.");
        }
    }
}
