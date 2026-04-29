# Overtime (OT) System Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Implement a monthly overtime management system where HR admins can select an employee, view a calendar-based form for a specific month, and record overtime hours with automated rate calculations based on employee grade, hours worked, and holiday types.

**Architecture:** A new `Overtime` model will track daily OT entries. A dedicated `OvertimeController` will handle the monthly view logic, providing a grid-style form (similar to the provided screenshot) that fetches employee details and existing records for the selected month.

**Tech Stack:** Laravel (PHP), Blade Templates, Bootstrap 5, Vanilla JavaScript, Flatpickr.

---

### Task 1: Database Migration and Model

**Files:**
- `[ ]` Create migration `database/migrations/2026_04_28_000000_create_overtimes_table.php`
- `[ ]` Create model `app/Models/Overtime.php`
- `[ ]` Update `app/Models/Employee.php` to include relationship

**Step 1: Create the migration**
```php
// database/migrations/2026_04_28_000000_create_overtimes_table.php
Schema::create('overtimes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
    $table->date('date');
    $table->time('ot_start')->nullable();
    $table->time('ot_stop')->nullable();
    $table->decimal('total_ot_hours', 5, 2)->default(0);
    $table->boolean('is_workday_duty_plus_5')->default(false);
    $table->boolean('is_holiday_duty_plus_5')->default(false);
    $table->boolean('is_eid_duty')->default(false);
    $table->decimal('amount', 12, 2)->default(0);
    $table->text('remarks')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->unique(['employee_id', 'date']);
});
```

**Step 2: Create the Model**
```php
// app/Models/Overtime.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model {
    protected $fillable = [
        'employee_id', 'date', 'ot_start', 'ot_stop', 'total_ot_hours',
        'is_workday_duty_plus_5', 'is_holiday_duty_plus_5', 'is_eid_duty',
        'amount', 'remarks', 'created_by'
    ];
}
```

---

### Task 2: Routes and Navigation

**Files:**
- `[ ]` Update `routes/web.php`
- `[ ]` Update `database/seeders/MenuItemSeeder.php`

**Step 1: Add Routes**
```php
// routes/web.php
Route::middleware(['auth', 'verified'])->prefix('personnel')->name('personnel.')->group(function () {
    // ... existing routes
    Route::get('overtimes', [App\Http\Controllers\Personnel\OvertimeController::class, 'index'])->name('overtimes.index');
    Route::post('overtimes/save', [App\Http\Controllers\Personnel\OvertimeController::class, 'save'])->name('overtimes.save');
});
```

**Step 2: Update Menu Seeder**
Add "Overtime" under the "Personnel" menu.

---

### Task 3: Overtime Controller Implementation

**Files:**
- `[ ]` Create `app/Http/Controllers/Personnel/OvertimeController.php`

**Logic Highlights:**
- `index()`: Fetch employees, handle month/year selection, and load existing `Overtime` records for the grid.
- `save()`: Bulk update/create daily entries for the selected month.
- Calculation logic:
    - Below 5 hours: Grade-based (Management: 115, Technician: 35, Driver: 30, Peon/Cleaner: 25).
    - >= 5 hours Workday: `(gross * 0.6 / 30) * 1`.
    - Holiday (> 5 hrs): `(gross * 0.6 / 30) * 2`.
    - Eid Day: `(gross * 0.6 / 30) * 3`.

---

### Task 4: Monthly Form Blade View

**Files:**
- `[ ]` Create `resources/views/personnel/overtimes/index.blade.php`

**UI Features:**
- Employee selection (Select2).
- Month/Year selection.
- A table matching the Excel design:
    - Row for each day of the month.
    - Inputs for OT Start/Stop.
    - Checkboxes for "Workday > 5", "Holiday > 5", "Eid Special".
    - Client-side JS for live calculation of hours and amounts.

---

### Task 5: Verification

**Step 1: Automated Test**
Create a feature test to verify calculation logic for different grades and scenarios.

**Step 2: Manual Check**
1. Log in as HR Admin.
2. Go to Personnel > Overtime.
3. Select an employee (e.g., a Technician).
4. Fill in 4 hours of OT -> verify amount is 4 * 35 = 140.
5. Check "Eid Special" -> verify amount is 3 * full shift.
