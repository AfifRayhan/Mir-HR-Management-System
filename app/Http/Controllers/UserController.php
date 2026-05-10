<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filters
        if ($request->role_id) {
            $query->where('role_id', $request->role_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Sorting — whitelist allowed columns and directions to prevent SQL injection
        $allowedSortColumns = ['name', 'email', 'created_at'];
        $allowedDirections = ['asc', 'desc'];
        $sortColumn = in_array($request->input('sort'), $allowedSortColumns) ? $request->input('sort') : 'name';
        $sortDirection = in_array(strtolower($request->input('direction', 'asc')), $allowedDirections) ? strtolower($request->input('direction', 'asc')) : 'asc';
        $query->orderBy($sortColumn, $sortDirection);

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('security.users.index', compact('users', 'roles'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id'  => ['nullable', 'exists:roles,id'],
            'status'   => ['required', 'in:active,inactive'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('security.users.index')
            ->with('success', 'User created successfully.');
    }


    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id'  => ['nullable', 'exists:roles,id'],
            'status'   => ['required', 'in:active,inactive'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($user->employee) {
            $user->employee->update(['status' => $validated['status']]);
        }

        return redirect()->route('security.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === (int) Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('security.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
