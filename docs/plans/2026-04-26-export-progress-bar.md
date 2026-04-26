# Export Progress Bar Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Implement a real-time progress bar for all major downloads (Attendance and Employee exports) using a polling-based approach with Cache and SweetAlert2.

**Architecture:** We will use a unique UUID for each export request. The export classes will update a progress value in the `Cache` during processing. The frontend will use SweetAlert2 to display a progress bar and poll a new API endpoint to retrieve and display the percentage.

**Tech Stack:** Laravel (Cache), JavaScript (Polling/Fetch), SweetAlert2.

---

### Task 1: Infrastructure and Routes

**Files:**
- `[ ]` Create `app/Http/Controllers/ExportStatusController.php`
- `[ ]` Register routes in `routes/web.php`

**Step 1: Create the controller**
```php
// app/Http/Controllers/ExportStatusController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExportStatusController extends Controller
{
    public function check($uuid)
    {
        $progress = Cache::get("export_progress_{$uuid}", 0);
        return response()->json(['progress' => (int)$progress]);
    }
}
```

**Step 2: Register routes**
```php
// routes/web.php
Route::get('/export/status/{uuid}', [App\Http\Controllers\ExportStatusController::class, 'check'])->name('export.status');
```

---

### Task 2: MonthlyAttendanceExport Progress Tracking

**Files:**
- `[ ]` Modify `app/Exports/MonthlyAttendanceExport.php`
- `[ ]` Modify `app/Http/Controllers/Personnel/AttendanceController.php`

**Step 1: Update Export Constructor and Progress Reporting**
Modify `app/Exports/MonthlyAttendanceExport.php`:
Add `$uuid` to constructor and property.
In `view()` method, calculate and update progress in Cache.

**Step 2: Update Controller to generate/pass UUID**
Modify `app/Http/Controllers/Personnel/AttendanceController.php`:
Update `exportMonthlyExcel` and other monthly export methods to accept `export_uuid`.

---

### Task 3: AttendancesExport (Daily) Progress Tracking

**Files:**
- `[ ]` Modify `app/Exports/AttendancesExport.php`

**Step 1: Add progress reporting to `collection()` and `registerEvents()`**
Modify `app/Exports/AttendancesExport.php`:
Add `$uuid` to constructor and property.
In `collection()`, set progress to 50% after query.
In `registerEvents()` AfterSheet, set progress to 100%.

---

### Task 4: Frontend Polling Logic

**Files:**
- `[ ]` Create `resources/js/export-helper.js`
- `[ ]` Include in `resources/js/app.js`

**Step 1: Create the helper**
Create `resources/js/export-helper.js` with `window.downloadWithProgress` function.

**Step 2: Update app.js**
Import or include the helper.

---

### Task 5: Integration in UI

**Files:**
- `[ ]` Modify `resources/views/personnel/attendance/index.blade.php`
- `[ ]` Modify `resources/views/personnel/attendance/export-monthly-preview.blade.php`

**Step 1: Update download buttons**
Change direct links or form submits to use `downloadWithProgress()`.
