<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\OfficeType;
use App\Models\Employee;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();

        $offices = Office::with('type')->orderBy('order_number')->get();
        $officeTypes = OfficeType::orderBy('order_number')->get();

        return view('settings.offices.index', compact('offices', 'officeTypes', 'user', 'roleName', 'employee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'office_type_id' => 'required|exists:office_types,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'order_number' => 'required|integer',
        ]);

        Office::create($validated);
        return redirect()->back()->with('success', 'Office created successfully.');
    }

    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'office_type_id' => 'required|exists:office_types,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'order_number' => 'required|integer',
        ]);

        $office->update($validated);
        return redirect()->back()->with('success', 'Office updated successfully.');
    }

    public function destroy(Office $office)
    {
        // Add checks if needed (e.g. if employees are assigned to this office)
        $office->delete();
        return redirect()->back()->with('success', 'Office deleted successfully.');
    }
}
