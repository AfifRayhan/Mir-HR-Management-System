<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Attendance Log</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; border-collapse: collapse; }
        .logo { width: 70px; height: auto; }
        .company-name { font-size: 18pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 11pt; color: #555; margin: 5px 0 0 0; }
        
        .report-title { text-align: right; font-size: 14pt; font-weight: bold; margin-bottom: 5px; border-bottom: 2px solid #007A10; padding-bottom: 5px; color: #007A10; }
        .period-text { text-align: right; font-size: 10pt; color: #666; margin-bottom: 15px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; border: none; }
        .label { font-weight: bold; width: 120px; color: #555; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th { background-color: #007A10; color: #ffffff; padding: 10px 8px; text-align: center; font-size: 9pt; border: 1px solid #005a0b; }
        .report-table td { padding: 8px 6px; border: 1px solid #dee2e6; font-size: 9pt; vertical-align: middle; text-align: center; }
        .report-table tr:nth-child(even) { background-color: #f9f9f9; }
        
        .status-present { color: #007A10; font-weight: bold; }
        .status-late { color: #d97706; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-leave { color: #2563eb; font-weight: bold; }
        .status-holiday { color: #6b7280; font-weight: bold; }
        
        .text-left { text-align: left !important; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 80px;">
                @php
                    $logoPath = public_path('images/MIRORIGINAL.jpeg');
                    if (isset($employee) && $employee->office && $employee->office->logo) {
                        $officeLogo = $employee->office->logo;
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
                <div class="company-name">{{ $employee->office->name ?? 'Unassigned Office' }}</div>
                <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div class="report-title">EMPLOYEE ATTENDANCE LOG</div>
                <div class="period-text">{{ $fromDate }} to {{ $toDate }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="label">Employee Name:</td>
            <td><strong>{{ $employee->name }}</strong> ({{ $employee->employee_code }})</td>
            <td class="label">Department:</td>
            <td>{{ $employee->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Designation:</td>
            <td>{{ $employee->designation->name ?? 'N/A' }}</td>
            <td class="label">Office:</td>
            <td>{{ $employee->office->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 15%;">Day</th>
                <th style="width: 15%;">In Time</th>
                <th style="width: 15%;">Out Time</th>
                <th style="width: 15%;">Working Hours</th>
                <th style="width: 10%;">Late</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    <td>{{ $record->date->format('Y-m-d') }}</td>
                    <td>{{ $record->date->format('l') }}</td>
                    <td>{{ $record->in_time ? $record->in_time->format('h:i A') : '-' }}</td>
                    <td>{{ $record->out_time ? $record->out_time->format('h:i A') : '-' }}</td>
                    <td>{{ $record->working_hours ?: '-' }}h</td>
                    <td>{{ $record->late_timing ?: '-' }}</td>
                    <td>
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




