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
use App\Http\Controllers\Settings\OfficeTypeController;
use App\Http\Controllers\Settings\OfficeController;
use App\Http\Controllers\Settings\OfficeTimeController;
use App\Http\Controllers\Settings\HolidayController;
use App\Http\Controllers\Settings\WeeklyHolidayController;
use App\Http\Controllers\Settings\LeaveTypeController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\Personnel\LeaveBalanceController;
use App\Http\Controllers\Personnel\AttendanceController;
use App\Http\Controllers\Settings\NoticeController;
use App\Http\Controllers\Settings\DeviceController;
use App\Http\Controllers\EmployeeAttendanceController;
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

    Route::get('leave-applications', [LeaveApplicationController::class, 'indexHR'])->name('leave-applications.index');
    Route::put('leave-applications/{leaveApplication}/status', [LeaveApplicationController::class, 'updateStatus'])->name('leave-applications.status');

    Route::get('leave-accounts', [LeaveBalanceController::class, 'index'])->name('leave-balances.index');
    Route::post('leave-accounts', [LeaveBalanceController::class, 'store'])->name('leave-balances.store');

    // Attendance routes
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::post('attendances/process', [AttendanceController::class, 'processLogs'])->name('attendances.process');
    Route::get('attendances/adjust', [AttendanceController::class, 'adjust'])->name('attendances.adjust');
    Route::post('attendances/adjust', [AttendanceController::class, 'storeAdjustment'])->name('attendances.store-adjustment');
});

// Employee specific routes
Route::middleware(['auth', 'verified'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('attendance', [EmployeeAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('leave', [LeaveApplicationController::class, 'indexEmployee'])->name('leave.index');
    Route::post('leave', [LeaveApplicationController::class, 'store'])->name('leave.store');
});

// Team Lead specific routes
Route::middleware(['auth', 'verified'])->prefix('team-lead')->name('team-lead.')->group(function () {
    Route::get('leave', [LeaveApplicationController::class, 'indexTeamLeadSelf'])->name('leave.index');
    Route::post('leave', [LeaveApplicationController::class, 'store'])->name('leave.store');

    Route::get('leave-applications', [LeaveApplicationController::class, 'indexTeamLead'])->name('leave-applications.index');
    Route::put('leave-applications/{leaveApplication}/status', [LeaveApplicationController::class, 'updateStatusTeamLead'])->name('leave-applications.status');
});

// Settings management routes
Route::middleware(['auth', 'verified'])->prefix('settings')->name('settings.')->group(function () {
    Route::resource('office-types', OfficeTypeController::class);
    Route::resource('offices', OfficeController::class);
    Route::resource('office-times', OfficeTimeController::class);
    Route::resource('devices', DeviceController::class);
    Route::resource('leave-types', LeaveTypeController::class)->except(['show', 'create', 'edit']);
    Route::resource('notices', NoticeController::class);

    // Holiday configuration routes
    Route::prefix('holidays')->name('holidays.')->group(function () {
        Route::get('weekly', [WeeklyHolidayController::class, 'index'])->name('weekly.index');
        Route::put('weekly', [WeeklyHolidayController::class, 'update'])->name('weekly.update');

        Route::get('others', [HolidayController::class, 'index'])->name('others.index');
        Route::post('others', [HolidayController::class, 'store'])->name('others.store');
        Route::put('others/{holiday}', [HolidayController::class, 'update'])->name('others.update');
        Route::delete('others/{holiday}', [HolidayController::class, 'destroy'])->name('others.destroy');
    });
});

// Device Sync API (Exempt from CSRF in bootstrap/app.php)
Route::post('api/device/sync', [\App\Http\Controllers\Api\DeviceLogController::class, 'sync'])->name('api.device.sync');

require __DIR__ . '/auth.php';
