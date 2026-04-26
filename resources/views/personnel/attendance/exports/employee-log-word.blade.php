<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Attendance Log</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11pt; color: #333; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; }
        .logo-cell { width: 80px; }
        .address-cell { padding-left: 20px; vertical-align: top; }
        .company-name { font-size: 16pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 10pt; color: #666; margin: 5px 0 0 0; }
        
        .report-title { text-align: right; font-size: 14pt; font-weight: bold; margin-bottom: 5px; color: #007A10; }
        .period-text { text-align: right; font-size: 10pt; color: #666; margin-bottom: 20px; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; width: 150px; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th { background-color: #007A10; color: #ffffff; padding: 10px 5px; text-align: center; font-size: 10pt; border: 1px solid #005a0b; }
        .report-table td { padding: 8px 5px; border: 1px solid #dee2e6; font-size: 10pt; vertical-align: middle; text-align: center; }
        
        .status-present { color: #007A10; font-weight: bold; }
        .status-late { color: #d97706; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @php
                    $logoPath = public_path('images/Mirtel Group Logo .png');
                    $logoData = '';
                    if (file_exists($logoPath)) {
                        $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                    }
                @endphp
                @if($logoData)
                    <img src="{{ $logoData }}" width="60" alt="Logo">
                @endif
            </td>
            <td class="address-cell">
                <div class="company-name">Mir Telecom Ltd.</div>
                <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
            </td>
        </tr>
    </table>

    <div class="report-title">EMPLOYEE ATTENDANCE LOG</div>
    <div class="period-text">{{ $fromDate }} to {{ $toDate }}</div>

    <table class="info-table">
        <tr>
            <td class="label">Employee Name:</td>
            <td>{{ $employee->name }} ({{ $employee->employee_code }})</td>
        </tr>
        <tr>
            <td class="label">Department / Designation:</td>
            <td>{{ $employee->department->name ?? 'N/A' }} / {{ $employee->designation->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Day</th>
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
