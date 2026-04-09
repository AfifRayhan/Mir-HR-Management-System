<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['users', 'menuItems'])->orderBy('name')->paginate(15);

        return view('security.roles.index', compact('roles'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Role::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('security.roles.index')
            ->with('success', 'Role created successfully.');
    }


    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $role->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('security.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete a role that has users assigned to it.');
        }

        $role->menuItems()->detach();
        $role->delete();

        return redirect()->route('security.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
