<?php

use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Personnel\EmployeeController;
use App\Http\Controllers\Personnel\DepartmentController;
use App\Http\Controllers\Personnel\SectionController;
use App\Http\Controllers\Personnel\DesignationController;
use App\Http\Controllers\Personnel\GradeController;
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

Route::get('/employee-profile', [EmployeeDashboardController::class, 'profile'])
    ->middleware(['auth', 'verified'])
    ->name('employee-profile');

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

// Personnel management routes
Route::middleware(['auth', 'verified'])->prefix('personnel')->name('personnel.')->group(function () {
    Route::resource('employees', EmployeeController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('sections', SectionController::class);
    Route::resource('designations', DesignationController::class);
    Route::resource('grades', GradeController::class);
});

// Settings management routes
Route::middleware(['auth', 'verified'])->prefix('settings')->name('settings.')->group(function () {
    Route::resource('office-types', \App\Http\Controllers\Settings\OfficeTypeController::class);
    Route::resource('offices', \App\Http\Controllers\Settings\OfficeController::class);
    Route::resource('office-times', \App\Http\Controllers\Settings\OfficeTimeController::class);

    // Holiday configuration routes
    Route::prefix('holidays')->name('holidays.')->group(function () {
        Route::get('weekly', [\App\Http\Controllers\Settings\WeeklyHolidayController::class, 'index'])->name('weekly.index');
        Route::put('weekly', [\App\Http\Controllers\Settings\WeeklyHolidayController::class, 'update'])->name('weekly.update');

        Route::get('others', [\App\Http\Controllers\Settings\HolidayController::class, 'index'])->name('others.index');
        Route::post('others', [\App\Http\Controllers\Settings\HolidayController::class, 'store'])->name('others.store');
        Route::delete('others/{holiday}', [\App\Http\Controllers\Settings\HolidayController::class, 'destroy'])->name('others.destroy');
    });
});

require __DIR__ . '/auth.php';
