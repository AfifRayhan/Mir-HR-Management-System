<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if ($request->boolean('remember')) {
            Cookie::queue('remember_email', $request->email, 60 * 24 * 30); // 30 days
            Cookie::queue('remember_me', 'on', 60 * 24 * 30); // 30 days
        } else {
            Cookie::queue(Cookie::forget('remember_email'));
            Cookie::queue(Cookie::forget('remember_me'));
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user->role && $user->role->name === 'HR Admin') {
            return redirect()->intended(route('hr-dashboard', absolute: false));
        }

        return redirect()->intended(route('employee-dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
