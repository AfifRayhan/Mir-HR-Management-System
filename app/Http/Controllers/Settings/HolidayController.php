<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Office;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        
        $query = Holiday::with('office');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $holidays = $query->orderBy('from_date', 'asc')->get();
        $offices = Office::all();

        return view('settings.holidays.others', compact('holidays', 'offices', 'user', 'roleName', 'employee'));
    }

    public function store(Request $request)
    {
        if ($request->type === 'Eid Day') {
            $request->merge(['to_date' => $request->from_date]);
        }

        $validated = $request->validate([
            'type' => 'required|string',
            'year' => 'required|numeric',
            'title' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'remarks' => 'nullable|string',
            'all_office' => 'nullable|boolean',
            'office_id' => 'required_without:all_office|nullable|exists:offices,id',
            'is_active' => 'nullable',
        ]);

        $fromDate = new \DateTime($validated['from_date']);
        $toDate = new \DateTime($validated['to_date']);

        $validated['total_days'] = $fromDate->diff($toDate)->days + 1;
        $validated['all_office'] = $request->has('all_office');
        $validated['is_active'] = $request->has('is_active');

        Holiday::create($validated);

        return redirect()->back()->with('success', 'Holiday created successfully.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        if ($request->type === 'Eid Day') {
            $request->merge(['to_date' => $request->from_date]);
        }

        $validated = $request->validate([
            'type' => 'required|string',
            'year' => 'required|numeric',
            'title' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'remarks' => 'nullable|string',
            'all_office' => 'nullable|boolean',
            'office_id' => 'required_without:all_office|nullable|exists:offices,id',
            'is_active' => 'nullable',
        ]);

        $fromDate = new \DateTime($validated['from_date']);
        $toDate = new \DateTime($validated['to_date']);
        $validated['total_days'] = $fromDate->diff($toDate)->days + 1;
        $validated['all_office'] = $request->has('all_office');

        $validated['is_active'] = $request->has('is_active');
        $holiday->update($validated);

        return redirect()->back()->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->back()->with('success', 'Holiday deleted successfully.');
    }
}
