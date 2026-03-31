<?php

namespace App\Http\Controllers\TeamLead;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SupervisorRemark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorRemarkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $remarks = SupervisorRemark::where('supervisor_id', $employee->id)
            ->with('employee')
            ->latest()
            ->paginate(10);

        return view('team-lead.remarks.index', compact('remarks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $supervisor = Employee::where('user_id', Auth::id())->first();
        if (!$supervisor) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // Get direct reports
        $directReports = Employee::where('reporting_manager_id', $supervisor->id)
            ->where('status', 'active')
            ->get();

        $defaultExpiry = now()->addMonth()->format('Y-m-d\TH:i');

        return view('team-lead.remarks.create', compact('directReports', 'defaultExpiry'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $supervisor = Employee::where('user_id', Auth::id())->first();
        
        foreach ($request->employee_ids as $employeeId) {
            SupervisorRemark::create([
                'supervisor_id' => $supervisor->id,
                'employee_id' => $employeeId,
                'title' => $request->title,
                'message' => $request->message,
                'expires_at' => $request->expires_at,
            ]);
        }

        return redirect()->route('team-lead.remarks.index')
            ->with('success', 'Remarks sent successfully to selected employees.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupervisorRemark $remark)
    {
        // Ensure the supervisor owns this remark
        $supervisor = Employee::where('user_id', Auth::id())->first();
        if ($remark->supervisor_id !== $supervisor->id) {
            abort(403);
        }

        $remark->delete();

        return redirect()->route('team-lead.remarks.index')
            ->with('success', 'Remark deleted successfully.');
    }
}
