<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::orderBy('priority', 'desc')->get();
        return view('personnel.designations.index', compact('designations'));
    }

    public function create()
    {
        return view('personnel.designations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'short_name' => 'nullable|string|max:50',
            'priority'   => 'nullable|integer',
        ]);

        Designation::create($validated);
        return redirect()->route('personnel.designations.index')->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        return view('personnel.designations.edit', compact('designation'));
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
        $designation->delete();
        return redirect()->route('personnel.designations.index')->with('success', 'Designation deleted successfully.');
    }
}
