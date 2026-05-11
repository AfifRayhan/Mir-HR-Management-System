<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11pt; color: #333; }
        .header-table { width: 100%; border: none; margin-bottom: 30px; }
        .logo-cell { width: 80px; }
        .address-cell { padding-left: 20px; vertical-align: top; }
        .company-name { font-size: 16pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 10pt; color: #666; margin: 5px 0 0 0; }
        
        .report-title { text-align: right; font-size: 12pt; font-weight: bold; margin-bottom: 10px; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th { background-color: #007A10; color: #ffffff; padding: 10px 5px; text-align: center; font-size: 10pt; border: 1px solid #005a0b; }
        .report-table td { padding: 8px 5px; border: 1px solid #dee2e6; font-size: 9.5pt; vertical-align: middle; text-align: left; }
        .report-table tr:nth-child(even) { background-color: #f8f9fa; }
        
        .status-present { color: #007A10; font-weight: bold; }
        .status-late { color: #ffc107; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-leave { color: #0dcaf0; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
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
                    <img src="{{ $logoData }}" width="60" alt="Logo">
                @endif
            </td>
            <td class="address-cell">
                <div class="company-name">{{ $selectedOffice->name ?? 'The Mir Group' }}</div>
                <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
            </td>
        </tr>
    </table>

    <div class="report-title">ATTENDANCE REPORT - {{ $date }}</div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department/Designation</th>
                <th>In Time</th>
                <th>Out Time</th>
                <th>Working Hours</th>
                <th>Late (H:M:S)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->employee->name }} ({{ $record->employee->employee_code }})</td>
                    <td>{{ $record->employee->department->name ?? 'N/A' }} / {{ $record->employee->designation->name ?? 'N/A' }}</td>
                    <td style="text-align: center;">{{ $record->in_time ? $record->in_time->format('h:i A') : '-' }}</td>
                    <td style="text-align: center;">{{ $record->out_time ? $record->out_time->format('h:i A') : '-' }}</td>
                    <td style="text-align: center;">{{ $record->working_hours }}h</td>
                    <td style="text-align: center;">{{ $record->late_timing }}</td>
                    <td style="text-align: center;">
                        <span class="status-{{ strtolower($record->status) }}">
                            {{ ucfirst($record->status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>




