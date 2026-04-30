# Driver Roster Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Implement a "Driver Roster" management module identical to the existing "Roster" module, migrating existing driver shifts and employees.

**Architecture:** Create new controllers (`DriverRosterController`, `DriverRosterTimeController`) with independent routes to manage driver rosters cleanly. Reuse the existing `roster.index` and `roster.times.index` Blade views by passing `$pageTitle` and `$routePrefix` to keep the views DRY. Update seeders to migrate driver shifts and assign the specified employees to a new 'Drivers' roster group.

**Tech Stack:** Laravel, Blade, MySQL

## User Review Required

- Is making `Driver Roster` a top-level menu item with identical structure to `Roster` acceptable?
- Removing DDS and DNS from `OfficeTimeSeeder` means any existing `OfficeTime` assignment using those ID/shifts in other features might break if not migrated. However, they are being added to `RosterTime` so roster scheduling works. Are there other dependencies on these `OfficeTime` records?

## Proposed Changes

### Task 1: Update Views to be DRY

**Files:**
- `[ ]` Modify `resources/views/roster/index.blade.php`
- `[ ]` Modify `resources/views/roster/times/index.blade.php`
- `[ ]` Modify `app/Http/Controllers/RosterController.php`
- `[ ]` Modify `app/Http/Controllers/Roster/RosterTimeController.php`

**Step 1: Modify Views**

Replace hardcoded `roster.` routes with `{{ $routePrefix }}`.
Example: `route('roster.index')` becomes `route($routePrefix . 'index')`.
Change hardcoded titles to use `{{ $pageTitle ?? 'Roster' }}`.

**Step 2: Update Existing Controllers**

Pass `$routePrefix = 'roster.'` and `$pageTitle = 'Roster'` in both `RosterController` and `RosterTimeController`.

### Task 2: Create Driver Roster Controllers

**Files:**
- `[ ]` Create `app/Http/Controllers/DriverRosterController.php`
- `[ ]` Create `app/Http/Controllers/Roster/DriverRosterTimeController.php`

**Step 1: Create `DriverRosterController`**

Duplicate `RosterController` logic but set:
```php
const GROUP_MAP = [
    'drivers' => 'Drivers',
];
```
Pass `$routePrefix = 'driver-roster.'` and `$pageTitle = 'Driver Roster'` to the view.

**Step 2: Create `DriverRosterTimeController`**

Duplicate `RosterTimeController` logic but set:
```php
const GROUP_MAP = [
    'drivers' => 'Drivers',
];
```
Pass `$routePrefix = 'driver-roster.'` and `$pageTitle = 'Driver Roster'` to the view.

### Task 3: Register Routes & Update Sidebar

**Files:**
- `[ ]` Modify `routes/web.php`
- `[ ]` Modify `database/seeders/MenuItemSeeder.php`

**Step 1: Add Routes**
```php
// Driver Roster management routes
Route::middleware(['auth', 'verified'])->prefix('driver-roster')->name('driver-roster.')->group(function () {
    Route::get('/', [\App\Http\Controllers\DriverRosterController::class, 'index'])->name('index');
    Route::post('/save', [\App\Http\Controllers\DriverRosterController::class, 'save'])->name('save');
    Route::get('/import-previous', [\App\Http\Controllers\DriverRosterController::class, 'importPrevious'])->name('import-previous');
    Route::get('/employees', [\App\Http\Controllers\DriverRosterController::class, 'employees'])->name('employees');
    Route::get('/export', [\App\Http\Controllers\DriverRosterController::class, 'export'])->name('export');

    Route::resource('times', \App\Http\Controllers\Roster\DriverRosterTimeController::class);
});
```

**Step 2: Add Menu Items**
In `MenuItemSeeder.php`, add `Driver Roster` top-level menu item and child items. Grant access to `hr_admin`.

### Task 4: Update Seeders

**Files:**
- `[ ]` Modify `database/seeders/OfficeTimeSeeder.php`
- `[ ]` Create `database/seeders/DriverRosterTimeSeeder.php`
- `[ ]` Create `database/seeders/DriverRosterGroupSeeder.php`
- `[ ]` Modify `database/seeders/DatabaseSeeder.php`

**Step 1: Remove from `OfficeTimeSeeder`**
Remove `Driver Day Shift` and `Driver Night Shift` from `OfficeTimeSeeder.php`.

**Step 2: Create `DriverRosterTimeSeeder`**
Seed `RosterTime` with `group_slug = 'drivers'` and shifts DDS, DNS, and Off Day.

**Step 3: Create `DriverRosterGroupSeeder`**
Assign employees `['15012032', '15122040', '25122090']` to `roster_group = 'Drivers'` and `office_time_id` to the general Roster shift ID.

**Step 4: Update `DatabaseSeeder`**
Call the two new seeders in `DatabaseSeeder.php`.

## Verification Plan

### Automated Tests
- Run tests if available, otherwise verify through UI.

### Manual Verification
- Access the HR Dashboard and confirm `Driver Roster` appears in the sidebar.
- Open `Driver Roster -> Manage Roster` and confirm the `Drivers` group is active and shifts `Driver Day Shift` and `Driver Night Shift` are available.
- Verify employees `15012032, 15122040, 25122090` appear in the Driver Roster.
- Save a sample schedule and verify it persists.
- Export the schedule and verify Excel download.
