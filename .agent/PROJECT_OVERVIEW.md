# Project Overview - Mir HR Management System

## Purpose
A comprehensive Human Resource Management System (HRMS) designed for tracking employee attendance, managing leave applications, and maintaining official internal communications.

## Key Modules

### 1. Employee Management
- **Models**: `Employee`, `Department`, `Designation`, `Section`, `Office`, `Grade`.
- **Logic**: Handles employee onboarding, status tracking (active/probation/terminated), and reporting hierarchies.

### 2. Attendance System
- **Models**: `Attendance` (raw device logs), `AttendanceRecord` (processed data), `Device`, `Machine`.
- **Service**: `AttendanceService` processes raw logs into daily records, calculates late seconds, and identifies working hours.
- **Adjustments**: `ManualAttendanceAdjustment` allows authorized users to correct mistakes.

### 3. Leave Management
- **Models**: `LeaveApplication`, `LeaveType`, `LeaveBalance`.
- **Logic**: Tracks available leave days per year, handles multi-level approvals (Reporting Manager -> Department Head -> HR), and excludes holidays from duration calculations.

### 4. Notifications & Communication
- **Service**: `NotificationService` handles internal alerts via `Notification` model and `Notice` system for global announcements.

## Technical Architecture

### Backend
- **Framework**: Laravel 12.
- **Patterns**: 
  - **Service Layer**: Complex logic is encapsulated in `app/Services/` (e.g., `AttendanceService`).
  - **Eloquent**: Heavy use of relationships (`belongsTo`, `hasMany`, `morphTo` for notifications).
  - **Migrations**: Database schema managed through Laravel migrations.

### Frontend
- **Interactivity**: Standard **Blade templates** with **Vanilla JavaScript** (and Alpine.js for minor UI state). Axios for API communication.
- **Styling**: **Bootstrap 5** with custom components for a modern, professional UI (Glassmorphism, dark mode support).
- **Assests**: Orchestrated by **Vite**.

## Frontend Interaction Patterns

The project has a built-in interaction bridge in `app.blade.php` using **SweetAlert2**.

### 1. Global Confirmation System
Avoid writing manual JavaScript for confirmations. Use data attributes:
- **Forms**: Add `data-confirm="true"` to any form. Use `data-confirm-message`, `data-confirm-title`, and `data-confirm-type` (warning/error/info) to customize.
- **Links/Buttons**: Add `data-confirm-click="true"` and `data-confirm-message` to links for direct actions.

### 2. Auto-Toasts
The layout automatically displays SweetAlert2 toasts for the following session flash keys:
- `session('success')`
- `session('error')`
- `session('warning')`
- `session('info')`

### 3. Asset Stacking
Inject module-specific assets into the layout using:
- `@push('styles')`
- `@push('scripts')`

## Environment Notes
- **PHP**: Always use `php.exe` for CLI operations.
- **Database**: Supports MySQL and SQLite (for testing).

## Common Development Tasks
- **New Feature**: Migration -> Model -> Service method -> Controller -> Blade View -> JS logic (if needed).
- **UI Update**: Modify Blade templates or associated CSS/JS assets.
- **Logic Change**: Check corresponding Service class first.
