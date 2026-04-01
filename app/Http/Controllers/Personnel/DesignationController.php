<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();

        $designations = Designation::orderBy('priority', 'asc')->get();
        return view('personnel.designations.index', compact('designations', 'user', 'roleName', 'employee'));
    }

    public function create()
    {
        return view('personnel.designations.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'short_name' => 'nullable|string|max:50',
            'priority'   => 'nullable|integer',
        ]);

        if ($request->input('insert_mode') == '1' && isset($validated['priority'])) {
            Designation::where('priority', '>=', $validated['priority'])->increment('priority');
        }

        Designation::create($validated);
        return redirect()->route('personnel.designations.index')->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        return view('personnel.designations.form', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'short_name' => 'nullable|string|max:50',
            'priority'   => 'nullable|integer',
        ]);

        $designation->update($validated);
        return redirect()->route('personnel.designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $priority = $designation->priority;
        $designation->delete();

        if ($priority !== null) {
            Designation::where('priority', '>', $priority)->decrement('priority');
        }

        return redirect()->route('personnel.designations.index')->with('success', 'Designation deleted successfully.');
    }
}
