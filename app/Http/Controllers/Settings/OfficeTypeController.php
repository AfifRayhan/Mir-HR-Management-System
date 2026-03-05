<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OfficeType;
use App\Models\Employee;
use Illuminate\Http\Request;

class OfficeTypeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = Employee::where('user_id', $user->id)->first();
        $officeTypes = OfficeType::orderBy('order_number')->get();

        return view('settings.office-types.index', compact('officeTypes', 'user', 'roleName', 'employee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_number' => 'required|integer',
        ]);

        OfficeType::create($validated);
        return redirect()->back()->with('success', 'Office Type created successfully.');
    }

    public function update(Request $request, OfficeType $officeType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_number' => 'required|integer',
        ]);

        $officeType->update($validated);
        return redirect()->back()->with('success', 'Office Type updated successfully.');
    }

    public function destroy(OfficeType $officeType)
    {
        if ($officeType->offices()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete Office Type that has offices assigned to it.');
        }

        $officeType->delete();
        return redirect()->back()->with('success', 'Office Type deleted successfully.');
    }
}
