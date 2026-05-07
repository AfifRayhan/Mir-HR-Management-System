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
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        // For initialization form (full list of active employees)
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $leaveTypes = LeaveType::orderBy('sort_order')->get();
        $currentYear = date('Y');

        // Base query for the paginated list
        $query = Employee::with('department', 'designation')->where('status', 'active');

        // Search functionality
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Sorting functionality
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        
        $allowedSorts = ['name', 'employee_code', 'department_id'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Pagination
        $paginatedEmployees = $query->paginate(10)->withQueryString();
        $employeeIds = $paginatedEmployees->pluck('id');

        // Eager load balances for the current year for visible employees
        $balances = LeaveBalance::with(['employee', 'leaveType'])
            ->where('year', $currentYear)
            ->whereIn('employee_id', $employeeIds)
            ->get()
            ->groupBy('employee_id');

        return view('personnel.leave-balances.index', compact(
            'employees', 'paginatedEmployees', 'balances', 'leaveTypes', 'currentYear', 
            'user', 'roleName', 'employee', 'search', 'sort', 'direction'
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

        $employee = Employee::findOrFail($request->employee_id);

        $initialized = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('year', $request->year)
            ->pluck('leave_type_id');

        $leaveTypes = LeaveType::all();
        $allocations = [];
        foreach ($leaveTypes as $type) {
            $allocations[$type->id] = $this->getAllocatedDays($employee, $type);
        }

        return response()->json([
            'initialized' => $initialized,
            'allocations' => $allocations,
        ]);
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
            $openingBalance = $this->getAllocatedDays($employee, $type);


            $exists = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('year', $request->year)
                ->exists();

            if (!$exists) {
                if ($type->carry_forward) {
                    $previousBalance = LeaveBalance::where('employee_id', $employee->id)
                        ->where('leave_type_id', $type->id)
                        ->where('year', $request->year - 1)
                        ->first();

                    if ($previousBalance && $previousBalance->remaining_days > 0) {
                        $openingBalance += $previousBalance->remaining_days;
                    }
                }

                LeaveBalance::create([
                    'employee_id'     => $employee->id,
                    'leave_type_id'   => $type->id,
                    'year'            => $request->year,
                    'opening_balance' => $openingBalance,
                    'used_days'       => 0,
                    'remaining_days'  => $openingBalance,
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

    private function getAllocatedDays($employee, $leaveType)
    {
        $nameStr = strtolower($leaveType->name);

        if ($employee->employee_type === 'Probation') {
            if (str_contains($nameStr, 'casual')) {
                return 4;
            } elseif (str_contains($nameStr, 'sick')) {
                return 4;
            } elseif (str_contains($nameStr, 'emergency')) {
                return 2;
            } elseif (str_contains($nameStr, 'earn')) {
                return 0;
            }
        } else {
            if (str_contains($nameStr, 'earn')) {
                if (!$employee->joining_date) {
                    return 0;
                }
                $joinDate       = Carbon::parse($employee->joining_date);
                $daysSinceJoin  = $joinDate->diffInDays(now());
                $yearsOfService = $joinDate->diffInYears(now());

                // Accrued EL: 1 day per 18 working days, capped at 30
                $earnLeave = (int) min(30, max(0, floor($daysSinceJoin / 18)));

                // BEL bonus folded in: +10 for employees with >= 1 year service
                if ($yearsOfService >= 1) {
                    $earnLeave += 10;
                }

                return $earnLeave;
            }
        }
        
        return $leaveType->total_days_per_year;
    }
    public function updateBulk(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'year' => 'required|integer',
            'balances' => 'required|array',
            'balances.*.opening_balance' => 'required|numeric|min:0',
            'balances.*.used_days' => 'required|numeric|min:0',
            'balances.*.remaining_days' => 'required|numeric|min:0',
        ]);

        foreach ($request->balances as $balanceId => $data) {
            $balance = LeaveBalance::where('id', $balanceId)
                ->where('employee_id', $request->employee_id)
                ->where('year', $request->year)
                ->first();
            
            if ($balance) {
                $balance->opening_balance = $data['opening_balance'];
                $balance->used_days = $data['used_days'];
                $balance->remaining_days = $data['remaining_days'];
                $balance->save();
            }
        }

        return redirect()->back()->with('success', 'Leave balances updated successfully.');
    }
}
