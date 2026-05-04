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
        $users = \App\Models\User::orderBy('name')->get();
        $selectedRole = null;
        $selectedUser = null;
        $assignedSlugs = [];

        if ($request->has('user_id')) {
            $selectedUser = \App\Models\User::find($request->user_id);
            if ($selectedUser) {
                // Get individual overrides
                $userSlugs = $selectedUser->menuItems()->pluck('slug')->toArray();
                
                // Get role-based slugs
                $roleSlugs = $selectedUser->role ? $selectedUser->role->menuItems()->pluck('slug')->toArray() : [];
                
                // Show BOTH in the tree so HR Admin can see what the user currently has
                $assignedSlugs = array_unique(array_merge($userSlugs, $roleSlugs));
                
                // Store only individual overrides for the view to distinguish if needed
                // but for the checkboxes, array_merge is what we want
            }
        } elseif ($request->has('role_id')) {
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
            'users',
            'selectedRole',
            'selectedUser',
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
            'role_id'      => ['nullable', 'exists:roles,id'],
            'user_id'      => ['nullable', 'exists:users,id'],
            'menu_items'   => ['nullable', 'array'],
            'menu_items.*' => ['exists:menu_items,id'],
        ]);

        if ($request->filled('user_id')) {
            $user = \App\Models\User::findOrFail($validated['user_id']);
            $user->menuItems()->sync($validated['menu_items'] ?? []);
            
            return redirect()
                ->route('security.role-permissions.index', ['user_id' => $user->id])
                ->with('success', 'Permissions updated successfully for user: ' . $user->name);
        }

        $role = Role::findOrFail($validated['role_id']);
        $role->menuItems()->sync($validated['menu_items'] ?? []);

        return redirect()
            ->route('security.role-permissions.index', ['role_id' => $role->id])
            ->with('success', 'Permissions updated successfully for role: ' . $role->name);
    }
}
