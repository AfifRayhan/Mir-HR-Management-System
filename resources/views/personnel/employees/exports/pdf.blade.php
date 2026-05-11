<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee List</title>
    <style>
        @page {
            size: A3 landscape;
            margin: 10mm;
        }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5pt; color: #333; line-height: 1.2; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; border-collapse: collapse; }
        .logo { width: 65px; height: auto; }
        .company-name { font-size: 16pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 10pt; color: #555; margin: 5px 0 0 0; }
        
        .report-title { text-align: right; font-size: 13pt; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #007A10; padding-bottom: 5px; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: auto; }
        .report-table th { background-color: #007A10; color: #ffffff; padding: 10px 5px; text-align: center; font-size: 9pt; border: 1px solid #005a0b; }
        .report-table td { padding: 8px 5px; border: 1px solid #dee2e6; font-size: 9pt; vertical-align: middle; text-align: left; }
        .report-table tr:nth-child(even) { background-color: #f8f9fa; }
        
        .status-active { color: #007A10; font-weight: bold; }
        .status-inactive { color: #6c757d; font-weight: bold; }
        .status-terminated { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 80px;">
                @php
                    $logoPath = public_path('images/MIRORIGINAL.jpeg');
                    if (isset($selectedOffice) && $selectedOffice->logo) {
                        $officeLogo = storage_path('app/public/' . $selectedOffice->logo);
                        if (file_exists($officeLogo)) {
                            $logoPath = $officeLogo;
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
                <div class="company-name">Mir Telecom Ltd.</div>
                <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div class="report-title">EMPLOYEE LISTING REPORT</div>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                @foreach($selectedColumns as $key)
                    <th>{{ $allColumns[$key] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
                <tr>
                    @foreach($selectedColumns as $key)
                        <td>
                            @if($key === 'status')
                                <span class="status-{{ strtolower($employee->status) }}">
                                    {{ \App\Exports\EmployeesExport::getColumnValue($employee, $key) }}
                                </span>
                            @else
                                {{ \App\Exports\EmployeesExport::getColumnValue($employee, $key) }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>




