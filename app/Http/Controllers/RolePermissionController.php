<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RolePermissionController extends Controller
{
    /**
     * Show the role-permission management page.
     */
    public function index(Request $request)
    {
        $roles = Role::orderBy('name')->get();
        
        $users = User::with(['role', 'employee'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $treeData = $this->resolveTreeData($request);

        return view('security.role-permissions.index', compact(
            'roles',
            'users',
        ) + $treeData);
    }

    /**
     * Render only the navigation access tree.
     */
    public function tree(Request $request)
    {
        return view('security.role-permissions.partials.tree', $this->resolveTreeData($request));
    }

    /**
     * Update the menu item assignments for a role.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'manage_by'    => ['nullable', 'in:role,user'],
            'role_id'      => ['nullable', 'exists:roles,id'],
            'user_id'      => ['nullable', 'exists:users,id'],
            'menu_items'   => ['nullable', 'array'],
            'menu_items.*' => ['exists:menu_items,id'],
        ]);

        $manageBy = $validated['manage_by'] ?? ($request->filled('user_id') ? 'user' : 'role');

        if ($manageBy === 'user') {
            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
            ]);
            $user = \App\Models\User::findOrFail($validated['user_id']);
            $user->menuItems()->sync($validated['menu_items'] ?? []);
            
            return redirect()
                ->route('security.role-permissions.index', ['manage_by' => 'user', 'user_id' => $user->id])
                ->with('success', 'Permissions updated successfully for user: ' . $user->name);
        }

        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $role->menuItems()->sync($validated['menu_items'] ?? []);

        return redirect()
            ->route('security.role-permissions.index', ['manage_by' => 'role', 'role_id' => $role->id])
            ->with('success', 'Permissions updated successfully for role: ' . $role->name);
    }

    private function resolveTreeData(Request $request): array
    {
        $selectedRole = null;
        $selectedUser = null;
        $assignedSlugs = [];
        $manageBy = $request->query('manage_by');

        if (!in_array($manageBy, ['role', 'user'], true)) {
            $manageBy = $request->filled('user_id') ? 'user' : 'role';
        }

        if ($manageBy === 'user' && $request->filled('user_id')) {
            $selectedUser = User::with(['role.menuItems', 'menuItems'])->find($request->user_id);
            if ($selectedUser) {
                $userSlugs = $selectedUser->menuItems->pluck('slug')->toArray();
                $roleSlugs = $selectedUser->role ? $selectedUser->role->menuItems->pluck('slug')->toArray() : [];
                $assignedSlugs = array_unique(array_merge($userSlugs, $roleSlugs));
            }
        } elseif ($manageBy === 'role' && $request->filled('role_id')) {
            $selectedRole = Role::with('menuItems')->find($request->role_id);
            if ($selectedRole) {
                $assignedSlugs = $selectedRole->menuItems->pluck('slug')->toArray();
            }
        }

        $menuItems = Cache::rememberForever('role_permissions.top_level_menu_items', function () {
            return MenuItem::whereNull('parent_id')
                ->with('children')
                ->orderBy('sort_order')
                ->get();
        });

        return compact('selectedRole', 'selectedUser', 'manageBy', 'assignedSlugs', 'menuItems');
    }
}
