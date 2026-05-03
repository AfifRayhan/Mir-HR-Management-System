# System-Wide Optimization (Caching & Lazy Loading) Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Implement proactive database query caching for static dropdown tables and lazy loading/query chunking for large exports to optimize memory and CPU usage.

**Architecture:** We will use `Cache::remember` to cache frequently accessed static tables (Departments, Designations, etc.) for 60 minutes in the primary controllers. For memory optimization, we will implement `$query->chunk()` in monthly reports, `$query->lazy()` in PDF/Word exports, and convert Excel exports from `FromCollection` to `FromQuery` to utilize Laravel Excel's native chunking.

**Tech Stack:** Laravel 10+, PHP 8.2, Maatwebsite Excel

---

### Task 1: Query Caching in EmployeeController

**Files:**
- `[ ]` Modify Controller `app/Http/Controllers/Personnel/EmployeeController.php`

**Step 1: Write the failing test**
(No test required for standard controller query optimizations)

**Step 2: Run test to verify it fails**
N/A

**Step 3: Write minimal implementation**
```php
// In app/Http/Controllers/Personnel/EmployeeController.php

// In index() method:
// Change:
// $departments = Department::all();
// $sections = Section::with('department')->get();
// $offices = Office::all();
// $designations = Designation::all();
// To:
$departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => \App\Models\Department::all());
$sections = \Illuminate\Support\Facades\Cache::remember('sections_all', 3600, fn() => \App\Models\Section::with('department')->get());
$offices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => \App\Models\Office::all());
$designations = \Illuminate\Support\Facades\Cache::remember('designations_all', 3600, fn() => \App\Models\Designation::all());

// In create() and edit() methods:
// Also wrap the following with Cache::remember('key', 3600, fn() => ...):
// $grades = Grade::all(); -> 'grades_all'
// $officeTimes = OfficeTime::all(); -> 'office_times_all'
// $roles = Role::orderBy('name')->get(); -> 'roles_all'
// (Ensure you also update the existing department/section/designation/office calls in these methods to use the same Cache logic)
```

**Step 4: Run test to verify it passes**
N/A

**Step 5: Commit**
```bash
git add app/Http/Controllers/Personnel/EmployeeController.php
git commit -m "perf: implement query caching for static employee dropdowns"
```

### Task 2: Query Caching in AttendanceController

**Files:**
- `[ ]` Modify Controller `app/Http/Controllers/Personnel/AttendanceController.php`

**Step 1: Write the failing test**
N/A

**Step 2: Run test to verify it fails**
N/A

**Step 3: Write minimal implementation**
```php
// In app/Http/Controllers/Personnel/AttendanceController.php

// In index(), exportPreview(), exportMonthlyPreview(), exportYearlyPreview() methods:
// Replace DB calls like:
// $departments = Department::all();
// $offices = Office::all();
// $designations = Designation::all();
// With their cached equivalents:
$departments = \Illuminate\Support\Facades\Cache::remember('departments_all', 3600, fn() => \App\Models\Department::all());
$offices = \Illuminate\Support\Facades\Cache::remember('offices_all', 3600, fn() => \App\Models\Office::all());
// (And similarly for designations where applicable)
```

**Step 4: Run test to verify it passes**
N/A

**Step 5: Commit**
```bash
git add app/Http/Controllers/Personnel/AttendanceController.php
git commit -m "perf: implement query caching for static attendance dropdowns"
```

### Task 3: Implement Chunking in MonthlyAttendanceExport

**Files:**
- `[ ]` Modify Export `app/Exports/MonthlyAttendanceExport.php`

**Step 1: Write the failing test**
N/A

**Step 2: Run test to verify it fails**
N/A

**Step 3: Write minimal implementation**
```php
// In app/Exports/MonthlyAttendanceExport.php
// Inside the view() method, replace the bulk get() logic:
// $employees = $query->get();
// ... (and the subsequent bulk attendance/leaves fetching) ...

// With chunking:
$processedData = [];
$holidays = Holiday::where(function($q) use ($month, $year) {
        $q->whereYear('from_date', $year)->whereMonth('from_date', $month)
          ->orWhereYear('to_date', $year)->whereMonth('to_date', $month);
    })->get();

$weeklyHolidays = WeeklyHoliday::all()->groupBy('office_id');

$query->chunk(100, function ($employees) use (&$processedData, $month, $year, $daysInMonth, $holidays, $weeklyHolidays) {
    $employeeIds = $employees->pluck('id');

    $attendance = AttendanceRecord::whereIn('employee_id', $employeeIds)
        ->whereYear('date', $year)
        ->whereMonth('date', $month)
        ->get()
        ->groupBy('employee_id');

    $leaves = LeaveApplication::whereIn('employee_id', $employeeIds)
        ->where('status', 'approved')
        ->where(function($q) use ($month, $year) {
            $q->whereYear('from_date', $year)->whereMonth('from_date', $month)
              ->orWhereYear('to_date', $year)->whereMonth('to_date', $month);
        })->get()
        ->groupBy('employee_id');

    foreach ($employees as $index => $emp) {
        // KEEP EXISTING internal loop logic here:
        // $empAttendance = $attendance->get($emp->id, collect())->keyBy(fn($item) => (int)$item->date->format('d'));
        // ... (all logic through $days[$d] = $status;) ...
        
        // $processedData[] = [ ... ];
    }
});
// (Remove the old $employees = $query->get() and outside loops)
```

**Step 4: Run test to verify it passes**
N/A

**Step 5: Commit**
```bash
git add app/Exports/MonthlyAttendanceExport.php
git commit -m "perf: implement database chunking for monthly attendance exports"
```

### Task 4: Refactor AttendancesExport to use FromQuery

**Files:**
- `[ ]` Modify Export `app/Exports/AttendancesExport.php`

**Step 1: Write the failing test**
N/A

**Step 2: Run test to verify it fails**
N/A

**Step 3: Write minimal implementation**
```php
// In app/Exports/AttendancesExport.php

// 1. Replace FromCollection with FromQuery and add WithCustomChunkSize
// class AttendancesExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithDrawings, WithCustomStartCell, WithCustomChunkSize

// 2. Add chunkSize method:
public function chunkSize(): int
{
    return 500;
}

// 3. Rename collection() to query() and return the query builder instead of $query->get():
public function query()
{
    $date         = $this->request['date'] ?? now()->toDateString();
    // ... existing filters ...
    
    // Replace:
    // $records = $query->get();
    // return $records;
    
    // With:
    return $query; // Return the builder, Excel will automatically chunk it
}
```

**Step 4: Run test to verify it passes**
N/A

**Step 5: Commit**
```bash
git add app/Exports/AttendancesExport.php
git commit -m "perf: use FromQuery for native chunking in attendance exports"
```

### Task 5: Lazy Load AttendanceController PDF/Word Exports

**Files:**
- `[ ]` Modify Controller `app/Http/Controllers/Personnel/AttendanceController.php`

**Step 1: Write the failing test**
N/A

**Step 2: Run test to verify it fails**
N/A

**Step 3: Write minimal implementation**
```php
// In app/Http/Controllers/Personnel/AttendanceController.php

// In exportPdf() and exportWord() methods:
// Change:
// $records = $query->get();

// To:
$records = $query->lazy(); // Returns a LazyCollection instead of fetching all into memory at once
```

**Step 4: Run test to verify it passes**
N/A

**Step 5: Commit**
```bash
git add app/Http/Controllers/Personnel/AttendanceController.php
git commit -m "perf: use lazy loading for attendance PDF and Word exports"
```
