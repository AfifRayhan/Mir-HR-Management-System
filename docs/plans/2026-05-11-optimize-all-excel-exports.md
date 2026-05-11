# Global Excel Print Optimization Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Apply standard print layout configurations (A4, Fit to Width) across all remaining Excel export classes to ensure professional and complete printed reports.

**Architecture:** Update each class in `app/Exports/` to include or enhance `PageSetup` settings within the `AfterSheet` event.

**Tech Stack:** PHP, Laravel, Maatwebsite/Excel.

---

### Task 1: Update Monthly and Yearly Attendance Exports

**Files:**
- `[ ]` app/Exports/MonthlyAttendanceExport.php
- `[ ]` app/Exports/YearlyAttendanceExport.php

**Step 1: Apply Fit to Width in MonthlyAttendanceExport**
```php
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
```

**Step 2: Apply Fit to Width in YearlyAttendanceExport**
```php
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
```

---

### Task 2: Update Employee and Attendance Listings

**Files:**
- `[ ]` app/Exports/AttendancesExport.php
- `[ ]` app/Exports/EmployeeLogExport.php
- `[ ]` app/Exports/EmployeesExport.php
- `[ ]` app/Exports/LeaveBalanceExport.php

**Step 1: Update AttendancesExport**
```php
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
```

**Step 2: Update EmployeeLogExport**
```php
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
```

**Step 3: Update EmployeesExport**
```php
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
```

**Step 4: Update LeaveBalanceExport**
```php
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
```

---

### Task 3: Enhance Roster Exports

**Files:**
- `[ ]` app/Exports/PersonalRosterExport.php
- `[ ]` app/Exports/RosterExport.php

**Step 1: Update PersonalRosterExport**
- Add `use Maatwebsite\Excel\Concerns\WithEvents;`
- Add `use Maatwebsite\Excel\Events\AfterSheet;`
- Add `use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;`
- Implement `registerEvents()` with Landscape, A4, and Fit to Width.

**Step 2: Update RosterExport**
- Add `use Maatwebsite\Excel\Concerns\WithEvents;`
- Add `use Maatwebsite\Excel\Events\AfterSheet;`
- Add `use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;`
- Implement `registerEvents()` with Landscape, A4, and Fit to Width.

---

### Task 4: Final Verification
- **Step 1**: Use `grep` to ensure all `app/Exports` files now contain `setFitToWidth(1)`.
- **Step 2**: Verify orientation choices against PDF templates where applicable.
