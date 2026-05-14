<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9pt; color: #333; line-height: 1.3; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; border-collapse: collapse; }
        .logo { width: 70px; height: auto; }
        .company-name { font-size: 18pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 11pt; color: #555; margin: 5px 0 0 0; }
        
        .report-title { text-align: right; font-size: 14pt; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #007A10; padding-bottom: 5px; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th { background-color: #007A10; color: #ffffff; padding: 12px 8px; text-align: center; font-size: 10pt; border: 1px solid #005a0b; }
        .report-table td { padding: 10px 8px; border: 1px solid #dee2e6; font-size: 10pt; vertical-align: middle; text-align: left; }
        .report-table tr:nth-child(even) { background-color: #f9f9f9; }
        
        .status-present { color: #007A10; font-weight: bold; }
        .status-late { color: #d97706; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-leave { color: #2563eb; font-weight: bold; }
        
        .text-center { text-align: center !important; }

        .office-header {
            background-color: #000;
            color: #fff;
            font-weight: bold;
            text-align: left !important;
            padding: 4px 10px !important;
            font-size: 8pt;
            text-transform: uppercase;
        }
        .dept-header {
            background-color: #fff;
            border-top: 1px solid #000 !important;
            border-bottom: 1px solid #000 !important;
            font-weight: bold;
            text-align: left !important;
            padding: 4px 20px !important;
            font-size: 8pt;
            color: #000;
        }
        tbody { page-break-inside: avoid; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 80px;">
                @php
                    $logoPath = public_path('images/MIRORIGINAL.jpeg');
                    if (isset($selectedOffice) && $selectedOffice->logo) {
                        $officeLogo = $selectedOffice->logo;
                        $resolvedLogoPath = \Illuminate\Support\Str::startsWith($officeLogo, 'images/')
                            ? public_path($officeLogo)
                            : storage_path('app/public/' . $officeLogo);

                        if (file_exists($resolvedLogoPath)) {
                            $logoPath = $resolvedLogoPath;
                        }
                    }
                    
                    $logoData = '';
                    if (file_exists($logoPath)) {
                        $mimeType = mime_content_type($logoPath);
                        $logoData = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($logoPath));
                    }
                @endphp
                @if($logoData)
                    <img src="{{ $logoData }}" class="logo">
                @endif
            </td>
            <td>
                <div class="company-name">{{ $selectedOffice->name ?? 'The Mir Group' }}</div>
                <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div class="report-title">DAILY ATTENDANCE REPORT - {{ $date }}</div>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 250px;">Employee</th>
                <th style="width: 250px;">Designation</th>
                <th style="width: 120px;">In Time</th>
                <th style="width: 120px;">Out Time</th>
                <th style="width: 120px;">Working Hours</th>
                <th style="width: 120px;">Late (H:M:S)</th>
                <th style="width: 120px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedRecords = $records->groupBy(fn($r) => $r->employee->office->name ?? 'Unassigned');
            @endphp

            @foreach($groupedRecords as $officeName => $officeRecords)
                <tr style="page-break-inside: avoid;">
                    <td colspan="7" class="office-header">Office: {{ $officeName }}</td>
                </tr>
                @php
                    $deptGrouped = $officeRecords->groupBy(fn($r) => $r->employee->department->name ?? 'Unassigned');
                @endphp
                @foreach($deptGrouped as $deptName => $deptRecords)
                    <tr style="page-break-inside: avoid;">
                        <td colspan="7" class="dept-header">Department: {{ $deptName }} ({{ $deptRecords->count() }})</td>
                    </tr>
                    @foreach($deptRecords as $record)
                        <tr>
                            <td style="padding: 4px 8px;">
                                <strong>{{ $record->employee->name }}</strong><br>
                                <small style="color: #666; font-size: 7pt;">Code: {{ $record->employee->employee_code }}</small>
                            </td>
                            <td style="padding: 4px 8px;">
                                {{ $record->employee->designation->name ?? 'N/A' }}
                            </td>
                            <td class="text-center" style="padding: 4px 8px;">{{ $record->in_time ? $record->in_time->format('h:i A') : '-' }}</td>
                            <td class="text-center" style="padding: 4px 8px;">{{ $record->out_time ? $record->out_time->format('h:i A') : '-' }}</td>
                            <td class="text-center" style="padding: 4px 8px;">{{ $record->working_hours }}h</td>
                            <td class="text-center" style="padding: 4px 8px;">{{ $record->late_timing }}</td>
                            <td class="text-center" style="padding: 4px 8px;">
                                <span class="status-{{ strtolower($record->status) }}">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>




