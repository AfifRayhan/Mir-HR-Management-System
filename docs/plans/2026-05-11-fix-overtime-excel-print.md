# Overtime Excel Print Layout Implementation Plan

> **For Antigravity:** REQUIRED WORKFLOW: Use `.agent/workflows/execute-plan.md` to execute this plan in single-flow mode.

**Goal:** Ensure the Overtime Excel report prints correctly on A4 Portrait by applying "Fit to Width" scaling and optimizing column widths.

**Architecture:** Modify `App\Exports\OvertimeExport` to utilize PhpSpreadsheet's `PageSetup` through the `AfterSheet` event.

**Tech Stack:** PHP, Laravel, Maatwebsite/Excel (PhpSpreadsheet).

---

### Task 1: Update OvertimeExport Configuration

**Files:**
- `[ ]` Modify `app/Exports/OvertimeExport.php`

**Step 1: Update Column Widths**
Modify the `columnWidths` method to be more compact.

```php
    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 12,
            'C' => 12,
            'D' => 10,
            'E' => 18,
            'F' => 18,
            'G' => 18,
            'H' => 25,
            'I' => 15,
        ];
    }
```

**Step 2: Enable Wrap Text for Remarks**
Update the `styles` method to ensure the Remarks column wraps text.

```php
        // ... existing styles ...
        $sheet->getStyle('H10:H' . ($lastRow-5))->getAlignment()->setWrapText(true);
```

**Step 3: Configure Page Setup in registerEvents**
Update the `registerEvents` method to set Portrait orientation, A4 paper size, and Fit to Width.

```php
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getRowDimension(1)->setRowHeight(40);
                $sheet->getRowDimension(2)->setRowHeight(40);
                $sheet->setShowGridlines(false);
                
                // Configure Page Setup for Printing
                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
            },
        ];
    }
```

**Step 4: Verify against PDF Reference**
Check that the orientation matches `resources/views/personnel/overtimes/export.blade.php`.

**Step 5: Commit**

```bash
git add app/Exports/OvertimeExport.php
git commit -m "fix: optimize overtime excel print layout and scaling"
```
