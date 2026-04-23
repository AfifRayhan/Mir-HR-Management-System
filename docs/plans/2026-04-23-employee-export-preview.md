# Employee Export Preview Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Replace 3 separate export buttons with a single "Preview & Export" page where users select columns, sort, preview data in a paginated table, then download as Excel/CSV/PDF.

**Architecture:** Add a column definition registry to `EmployeesExport`, a new `exportPreview` controller method, and a new Blade view. The preview page uses GET params for column selection, sort, and filters. Download links pass all current params to existing export endpoints.

**Tech Stack:** Laravel Blade, Bootstrap 5, Maatwebsite/Excel, DomPDF

---

### Task 1: Column Configuration in EmployeesExport

**Files:**
- `[ ]` Modify `app/Exports/EmployeesExport.php`

**What to do:**

Add two static methods and update constructor/headings/map to support dynamic columns.

Add this static method that returns all available columns as `key => label`:

```php
public static function getColumnDefinitions(): array
{
    return [
        'employee_code' => 'Employee Code',
        'name' => 'Full Name',
        'email' => 'Corporate Email',
        'personal_email' => 'Personal Email',
        'phone' => 'Phone',
        'blood_group' => 'Blood Group',
        'father_name' => 'Father Name',
        'mother_name' => 'Mother Name',
        'spouse_name' => 'Spouse Name',
        'gender' => 'Gender',
        'religion' => 'Religion',
        'marital_status' => 'Marital Status',
        'national_id' => 'National ID',
        'tin' => 'TIN',
        'nationality' => 'Nationality',
        'no_of_children' => 'No. of Children',
        'contact_no' => 'Contact No',
        'emergency_contact_name' => 'Emergency Contact Name',
        'emergency_contact_relation' => 'Emergency Contact Relation',
        'emergency_contact_no' => 'Emergency Contact No',
        'emergency_contact_address' => 'Emergency Contact Address',
        'date_of_birth' => 'Date of Birth',
        'joining_date' => 'Joining Date',
        'discontinuation_date' => 'Discontinuation Date',
        'discontinuation_reason' => 'Discontinuation Reason',
        'present_address' => 'Present Address',
        'permanent_address' => 'Permanent Address',
        'department' => 'Department',
        'section' => 'Section',
        'designation' => 'Designation',
        'grade' => 'Grade',
        'office' => 'Office',
        'office_time' => 'Office Time',
        'gross_salary' => 'Gross Salary',
        'status' => 'Status',
    ];
}
```

Add a static method that extracts a column value from an Employee model:

```php
public static function getColumnValue($employee, string $key)
{
    return match($key) {
        'employee_code' => $employee->employee_code,
        'name' => $employee->name,
        'email' => $employee->email,
        'personal_email' => $employee->personal_email,
        'phone' => $employee->phone,
        'blood_group' => $employee->blood_group,
        'father_name' => $employee->father_name,
        'mother_name' => $employee->mother_name,
        'spouse_name' => $employee->spouse_name,
        'gender' => $employee->gender,
        'religion' => $employee->religion,
        'marital_status' => $employee->marital_status,
        'national_id' => $employee->national_id,
        'tin' => $employee->tin,
        'nationality' => $employee->nationality,
        'no_of_children' => $employee->no_of_children,
        'contact_no' => $employee->contact_no,
        'emergency_contact_name' => $employee->emergency_contact_name,
        'emergency_contact_relation' => $employee->emergency_contact_relation,
        'emergency_contact_no' => $employee->emergency_contact_no,
        'emergency_contact_address' => $employee->emergency_contact_address,
        'date_of_birth' => $employee->date_of_birth,
        'joining_date' => $employee->joining_date,
        'discontinuation_date' => $employee->discontinuation_date,
        'discontinuation_reason' => $employee->discontinuation_reason,
        'present_address' => $employee->present_address,
        'permanent_address' => $employee->permanent_address,
        'department' => $employee->department->name ?? 'N/A',
        'section' => $employee->section->name ?? 'N/A',
        'designation' => $employee->designation->name ?? 'N/A',
        'grade' => $employee->grade->name ?? 'N/A',
        'office' => $employee->office->name ?? 'N/A',
        'office_time' => $employee->officeTime->shift_name ?? 'N/A',
        'gross_salary' => $employee->gross_salary,
        'status' => ucfirst($employee->status),
        default => '',
    };
}
```

Default columns constant (used when user hasn't selected any):

```php
public const DEFAULT_COLUMNS = [
    'employee_code', 'name', 'department', 'designation',
    'office', 'contact_no', 'joining_date', 'status',
];
```

Update the constructor to accept a `$columns` parameter:

```php
protected $columns;

public function __construct($request, $columns = null)
{
    $this->request = $request;
    $allDefs = array_keys(self::getColumnDefinitions());
    $this->columns = $columns
        ? array_intersect($columns, $allDefs)
        : $allDefs;
}
```

Update `headings()` to use selected columns:

```php
public function headings(): array
{
    $defs = self::getColumnDefinitions();
    return array_map(fn($key) => $defs[$key], $this->columns);
}
```

Update `map()` to use selected columns:

```php
public function map($employee): array
{
    return array_map(fn($key) => self::getColumnValue($employee, $key), $this->columns);
}
```

Update `styles()` — replace hardcoded `AI1` with dynamic last column letter:

```php
public function styles(Worksheet $sheet)
{
    $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->columns));
    $range = "A1:{$lastCol}1";

    $sheet->getStyle($range)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF4F46E5'],
        ],
    ]);
    $sheet->freezePane('A2');
    $sheet->getStyle($range)->getBorders()->getAllBorders()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    $sheet->getStyle($range)->getBorders()->getAllBorders()
        ->getColor()->setArgb('FFD1D5DB');

    return [];
}
```

**Verify:** `php.exe -l app/Exports/EmployeesExport.php`

---

### Task 2: Routes & Controller

**Files:**
- `[ ]` Modify `routes/web.php` — add preview route, keep export routes
- `[ ]` Modify `app/Http/Controllers/Personnel/EmployeeController.php` — add `exportPreview`, update 3 export methods

**Routes — add this line alongside the existing export routes:**

```php
Route::get('employees/export/preview', [EmployeeController::class, 'exportPreview'])->name('employees.export.preview');
```

**Controller — new `exportPreview` method:**

```php
public function exportPreview(Request $request)
{
    $allColumns = \App\Exports\EmployeesExport::getColumnDefinitions();
    $selectedColumns = $request->input('columns', \App\Exports\EmployeesExport::DEFAULT_COLUMNS);

    // Validate columns
    $selectedColumns = array_intersect($selectedColumns, array_keys($allColumns));
    if (empty($selectedColumns)) {
        $selectedColumns = \App\Exports\EmployeesExport::DEFAULT_COLUMNS;
    }

    $query = Employee::with(['department', 'section', 'designation', 'grade', 'office', 'officeTime', 'user']);

    // Reuse same filters as index
    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('employee_code', 'like', '%' . $request->search . '%');
        });
    }
    if ($request->department_id) $query->where('department_id', $request->department_id);
    if ($request->office_id) $query->where('office_id', $request->office_id);
    if ($request->designation_id) $query->where('designation_id', $request->designation_id);
    if ($request->section_id) $query->where('section_id', $request->section_id);
    if ($request->status) $query->where('status', $request->status);

    // Sorting
    $sortColumn = $request->input('sort', 'created_at');
    $sortDirection = $request->input('direction', 'desc');
    if ($sortColumn === 'employee_code') {
        $query->orderByRaw('LENGTH(employee_code) ' . $sortDirection)
              ->orderBy('employee_code', $sortDirection);
    } else {
        $query->orderBy($sortColumn, $sortDirection);
    }

    $employees = $query->paginate(25)->withQueryString();

    return view('personnel.employees.export-preview', compact(
        'employees', 'allColumns', 'selectedColumns', 'sortColumn', 'sortDirection'
    ));
}
```

**Update existing export methods to pass `columns` param:**

```php
public function exportExcel(Request $request)
{
    $columns = $request->input('columns', null);
    return Excel::download(new EmployeesExport($request->all(), $columns), 'employees_' . date('Y-m-d_H-i-s') . '.xlsx');
}

public function exportCsv(Request $request)
{
    $columns = $request->input('columns', null);
    return Excel::download(new EmployeesExport($request->all(), $columns), 'employees_' . date('Y-m-d_H-i-s') . '.csv', \Maatwebsite\Excel\Excel::CSV);
}

public function exportPdf(Request $request)
{
    ini_set('memory_limit', '1024M');
    set_time_limit(300);
    $columns = $request->input('columns', null);
    return Excel::download(new EmployeesExport($request->all(), $columns), 'employees_' . date('Y-m-d_H-i-s') . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
}
```

**Verify:** `php.exe -l app/Http/Controllers/Personnel/EmployeeController.php`

---

### Task 3: Preview Blade Template

**Files:**
- `[ ]` Create `resources/views/personnel/employees/export-preview.blade.php`

Create the full Blade view. Layout: same `x-app-layout` with `hr-layout` + `hr-sidebar` as the index page. The page has three sections:

1. **Header** — title + back button
2. **Column selector panel** — collapsible panel with all 35 checkboxes in a 4-column grid, Select All / Deselect All buttons, Sort dropdowns, and Apply button. All wrapped in a `<form>` that GETs to the same preview URL, carrying over filter params as hidden inputs.
3. **Preview table** — `hr-panel` with `hr-table` showing only selected columns, paginated at 25/page
4. **Download bar** — sticky bottom bar with dropdown button for Excel/CSV/PDF. Download links include all current query params (filters + columns + sort).

Key UI details:
- Column checkboxes use `name="columns[]"` and `value="{{ $key }}"`.
- Hidden inputs carry over `search`, `department_id`, `office_id`, `designation_id`, `section_id`, `status` from the original filters.
- Sort dropdown options: only direct Employee model columns that make sense to sort by (`employee_code`, `name`, `email`, `joining_date`, `date_of_birth`, `gross_salary`, `status`, `created_at`).
- Download links are built with a JS helper that serializes current column selection + filters into URL params, pointing to the 3 export routes.
- The column selector panel is collapsible via Bootstrap collapse.
- Use existing CSS classes: `hr-panel`, `hr-table`, `filter-bar` for visual consistency.

**Verify:** `php.exe -l resources/views/personnel/employees/export-preview.blade.php` (won't work for Blade but visual check in browser)

---

### Task 4: Update Index Page

**Files:**
- `[ ]` Modify `resources/views/personnel/employees/index.blade.php`

Replace the 3 export buttons (lines 17-25) with a single "Preview & Export" button:

```blade
<a href="{{ route('personnel.employees.export.preview', request()->query()) }}" class="btn btn-sm btn-outline-primary d-flex align-items-center">
    <i class="bi bi-eye me-2"></i>{{ __('Preview & Export') }}
</a>
```

---

### Task 5: Cleanup & Cache Clear

- `[ ]` Delete `test-pdf.php` from project root
- `[ ]` Run `php.exe artisan route:clear`
- `[ ]` Run `php.exe artisan view:clear`
- `[ ]` Verify in browser: navigate to employees index, click "Preview & Export", toggle columns, preview, download

---

### Task 6: Manual Verification

Open browser to `http://127.0.0.1:8081/personnel/employees`:
1. Confirm only one "Preview & Export" button visible (no Excel/CSV/PDF buttons)
2. Click it — preview page loads with default 8 columns selected
3. Toggle columns on/off, click Apply — table updates correctly
4. Change sort — table re-sorts
5. Download Excel — file contains only selected columns with styling
6. Download CSV — file contains only selected columns
7. Download PDF — file downloads (may take ~30s)
