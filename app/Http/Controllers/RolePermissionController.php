<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Role;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * Show the role-permission management page.
     */
    public function index(Request $request)
    {
        $roles = Role::orderBy('name')->get();
        $selectedRole = null;
        $assignedSlugs = [];

        if ($request->has('role_id')) {
            $selectedRole = Role::find($request->role_id);
            if ($selectedRole) {
                $assignedSlugs = $selectedRole->menuItems()->pluck('slug')->toArray();
            }
        } elseif ($roles->isNotEmpty()) {
            $selectedRole = $roles->first();
            $assignedSlugs = $selectedRole->menuItems()->pluck('slug')->toArray();
        }

        // Get top-level menu items with their children
        $menuItems = MenuItem::whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        return view('security.role-permissions.index', compact(
            'roles',
            'selectedRole',
            'assignedSlugs',
            'menuItems'
        ));
    }

    /**
     * Update the menu item assignments for a role.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'role_id'      => ['required', 'exists:roles,id'],
            'menu_items'   => ['nullable', 'array'],
            'menu_items.*' => ['exists:menu_items,id'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $role->menuItems()->sync($validated['menu_items'] ?? []);

        return redirect()
            ->route('security.role-permissions.index', ['role_id' => $role->id])
            ->with('success', 'Permissions updated successfully for role: ' . $role->name);
    }
}
