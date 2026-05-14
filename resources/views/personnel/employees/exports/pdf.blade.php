<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee List</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 5.5pt; color: #333; line-height: 1.0; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; border-collapse: collapse; }
        .logo { width: 65px; height: auto; }
        .company-name { font-size: 16pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 10pt; color: #555; margin: 5px 0 0 0; }
        
        .report-title { text-align: right; font-size: 13pt; font-weight: bold; margin-bottom: 10px; border-bottom: 2px solid #007A10; padding-bottom: 5px; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .report-table th { background-color: #007A10; color: #ffffff; padding: 4px 2px; text-align: center; font-size: 6.5pt; border: 1px solid #005a0b; }
        .report-table td { 
            padding: 2px 1px; 
            border: 1px solid #dee2e6; 
            font-size: 5.5pt; 
            vertical-align: middle; 
            text-align: left; 
            word-wrap: break-word; 
            word-break: break-word;
            overflow: hidden;
        }
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
            <tr>
                @foreach($selectedColumns as $key)
                    @php
                        $width = match($key) {
                            'employee_code' => '35px',
                            'name' => '80px',
                            'email' => '80px',
                            'personal_email' => '80px',
                            'phone', 'contact_no' => '60px',
                            'blood_group' => '20px',
                            'gender', 'religion', 'marital_status' => '30px',
                            'joining_date' => '50px',
                            'department' => '60px',
                            'section' => '60px',
                            'designation' => '60px',
                            'office' => '60px',
                            'status' => '30px',
                            'father_name', 'mother_name', 'spouse_name' => '70px',
                            'present_address', 'permanent_address' => '120px',
                            default => '45px'
                        };
                    @endphp
                    <th style="width: {{ $width }};">{{ $allColumns[$key] }}</th>
                @endforeach
            </tr>
        <tbody>
            @php
                $currentOfficeId = null;
                $currentDeptId = null;
            @endphp
            @foreach($employees as $employee)
                @if($currentOfficeId !== $employee->office_id)
                    <tr style="background-color: #000000; color: #ffffff;">
                        <td colspan="{{ count($selectedColumns) }}" style="font-weight: bold; padding: 4px 8px; text-transform: uppercase;">
                            Office: {{ $employee->office->name ?? 'N/A' }}
                        </td>
                    </tr>
                    @php $currentOfficeId = $employee->office_id; $currentDeptId = null; @endphp
                @endif

                @if($currentDeptId !== $employee->department_id)
                    <tr style="background-color: #f1f5f9; color: #334155;">
                        <td colspan="{{ count($selectedColumns) }}" style="font-weight: bold; padding: 4px 15px;">
                            Department: {{ $employee->department->name ?? 'N/A' }}
                        </td>
                    </tr>
                    @php $currentDeptId = $employee->department_id; @endphp
                @endif

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




