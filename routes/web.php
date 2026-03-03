<?php

use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('/auth/login');
});

Route::get('/hr-dashboard', [HrDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('hr-dashboard');

Route::get('/employee-dashboard', [EmployeeDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('employee-dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Security management routes (protected by menu-based permission check)
Route::middleware(['auth', 'permission:security'])->prefix('security')->name('security.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('roles', RoleController::class)->except(['show']);

    Route::get('role-permissions', [RolePermissionController::class, 'index'])->name('role-permissions.index');
    Route::put('role-permissions', [RolePermissionController::class, 'update'])->name('role-permissions.update');
});

require __DIR__ . '/auth.php';
