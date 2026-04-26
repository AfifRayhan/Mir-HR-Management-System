# Walkthrough - Export Progress Bar Implementation

I have implemented a real-time progress bar for the major attendance exports. This allows users to see exactly how much of the report has been generated before the download starts.

## Changes Made

### 1. Backend Infrastructure
- **[NEW] [ExportStatusController.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/app/Http/Controllers/ExportStatusController.php)**: Added a lightweight controller to check the progress of a specific export UUID from the Cache.
- **[MODIFY] [web.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/routes/web.php)**: Registered the `/export/status/{uuid}` route.

### 2. Export Progress Reporting
- **[MODIFY] [MonthlyAttendanceExport.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/app/Exports/MonthlyAttendanceExport.php)**: Now calculates progress based on the number of employees processed in the `view()` method and updates the Cache.
- **[MODIFY] [AttendancesExport.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/app/Exports/AttendancesExport.php)**: Reports progress at key stages (Query started, Query finished, Spreadsheet writing finished).

### 3. Frontend Integration
- **[NEW] [export-helper.js](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/resources/js/export-helper.js)**: A new JavaScript utility that:
    1. Generates a unique UUID for the request.
    2. Opens a SweetAlert2 modal with a Bootstrap progress bar.
    3. Polls the backend every second for status updates.
    4. Automatically closes and triggers the download when ready.
- **[MODIFY] [app.js](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/resources/js/app.js)**: Included the helper globally.

### 4. UI Updates
- **[MODIFY] [index.blade.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/resources/views/personnel/attendance/index.blade.php)**: Updated the Daily Attendance download buttons to use the new progress helper.
- **[MODIFY] [export-monthly-preview.blade.php](file:///c:/Users/afif/Downloads/Projects/Mir-HR-Management-System/resources/views/personnel/attendance/export-monthly-preview.blade.php)**: Updated the Monthly Attendance download menu.

## Verification Results

- **Class Instantiation**: Verified via `php artisan tinker` that both `MonthlyAttendanceExport` and `AttendancesExport` can be instantiated with UUIDs.
- **Route Accessibility**: The status endpoint is correctly registered under the `auth` middleware.
- **Frontend Logic**: The JS helper uses standard `fetch` and `crypto` APIs compatible with modern browsers.

## How to Test
1. Go to **Daily Attendance** or **Monthly Attendance Preview**.
2. Click **Download Excel**.
3. You should see a SweetAlert2 modal popup with a progress bar that updates as the server prepares the file.
4. Once it reaches 100%, the modal will show a success message and the browser's download will trigger.
