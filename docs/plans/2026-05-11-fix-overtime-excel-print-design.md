# Design: Fix Overtime Excel Print Layout

## Problem
The Overtime Excel export is too wide for A4 Portrait printing, causing columns G-I to be cut off.

## Solution
Configure the Excel spreadsheet's internal print settings to force "Fit to Page Width" scaling and set standard A4 Portrait properties.

## Architecture
- **Component**: `App\Exports\OvertimeExport`
- **Mechanism**: Use `Maatwebsite\Excel\Events\AfterSheet` via the `WithEvents` concern to access the PhpSpreadsheet `PageSetup` object.

## Design Details

### 1. Page Setup
- Set `Orientation` to `PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT`.
- Set `PaperSize` to `PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4`.
- Set `FitToWidth` to `1`.
- Set `FitToHeight` to `0` (allow vertical overflow to multiple pages).

### 2. Column Width Adjustments
- **Date**: 18 (from 20)
- **In/Out**: 12 (from 15)
- **Hours**: 10 (from 12)
- **Duty Columns**: 18 (from 20)
- **Remarks**: 25 (from 30) - Enable WrapText.

### 3. Visual Polish
- Hide gridlines (`setShowGridlines(false)`) to match the clean PDF look.
