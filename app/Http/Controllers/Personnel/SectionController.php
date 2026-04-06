<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();

        $query = Section::with('department');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->input('sort') === 'name') {
            $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
            $query->orderBy('name', $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        $sections = $query->get();
        $departments = Department::all();
        return view('personnel.sections.index', compact('sections', 'departments', 'user', 'roleName', 'employee'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('personnel.sections.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string',
        ]);

        Section::create($validated);
        return redirect()->route('personnel.sections.index')->with('success', 'Section created successfully.');
    }

    public function edit(Section $section)
    {
        $departments = Department::all();
        return view('personnel.sections.edit', compact('section', 'departments'));
    }

    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string',
        ]);

        $section->update($validated);
        return redirect()->route('personnel.sections.index')->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section)
    {
        $section->delete();
        return redirect()->route('personnel.sections.index')->with('success', 'Section deleted successfully.');
    }
}
