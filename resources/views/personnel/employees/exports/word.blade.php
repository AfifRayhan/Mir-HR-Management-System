<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Report</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11pt; color: #333; }
        .header-table { width: 100%; border: none; margin-bottom: 30px; }
        .logo-cell { width: 80px; }
        .address-cell { padding-left: 20px; vertical-align: top; }
        .company-name { font-size: 16pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 10pt; color: #666; margin: 5px 0 0 0; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .report-table th { background-color: #007A10; color: #ffffff; padding: 10px 5px; text-align: left; font-size: 10pt; border: 1px solid #005a0b; }
        .report-table td { padding: 8px 5px; border: 1px solid #dee2e6; font-size: 9.5pt; vertical-align: middle; }
        .report-table tr:nth-child(even) { background-color: #f8f9fa; }
        
        .status-active { color: #007A10; font-weight: bold; }
        .status-inactive { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                {{-- Base64 encode image for Word to ensure it shows up --}}
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
                            @php $val = \App\Exports\EmployeesExport::getColumnValue($employee, $key); @endphp
                            @if($key === 'status')
                                <span class="{{ strtolower($val) === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ $val }}
                                </span>
                            @else
                                {{ $val }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>




