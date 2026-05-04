<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Balance Report</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        .header-table { width: 100%; border: none; margin-bottom: 20px; border-collapse: collapse; }
        .logo { width: 70px; height: auto; }
        .company-name { font-size: 18pt; font-weight: bold; color: #000; margin: 0; }
        .company-address { font-size: 11pt; color: #555; margin: 5px 0 0 0; }
        
        .report-title-container { text-align: center; margin-bottom: 30px; }
        .report-title { font-size: 16pt; font-weight: bold; text-decoration: underline; color: #000; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 4px 0; border: none; font-size: 10pt; font-weight: bold; }
        .info-label { width: 150px; }
        .info-separator { width: 10px; }
        .year-label { text-align: right; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 50px; }
        .report-table th { border: 1px solid #000; padding: 10px 8px; text-align: center; font-size: 10pt; font-weight: bold; }
        .report-table td { border: 1px solid #000; padding: 8px 6px; font-size: 10pt; text-align: center; }
        .report-table .text-left { text-align: left; }
        
        .signature-table { width: 100%; margin-top: 80px; border-collapse: collapse; }
        .signature-table td { text-align: center; vertical-align: bottom; width: 25%; font-weight: bold; font-size: 10pt; }
        .signature-line { border-top: 1px solid #000; width: 80%; margin: 0 auto; margin-bottom: 5px; }

        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 80px;">
                @if(isset($params['format']) && $params['format'] === 'pdf')
                    @php
                        $logoPath = public_path('images/Mirtel Group Logo .png');
                        $logoData = '';
                        if (file_exists($logoPath)) {
                            $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
                        }
                    @endphp
                    @if($logoData)
                        <img src="{{ $logoData }}" class="logo">
                    @endif
                @endif
            </td>
            <td>
                <div class="company-name">Mir Telecom Ltd.</div>
                <div class="company-address">House-04, Road-21, Gulshan-1, Dhaka-1212</div>
            </td>
        </tr>
    </table>

    <div class="report-title-container">
        <span class="report-title">Leave Balance</span>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Employee Id</td>
            <td class="info-separator">:</td>
            <td>{{ $employee->employee_code }}</td>
            <td class="year-label">YEAR: {{ $year }}</td>
        </tr>
        <tr>
            <td class="info-label">Name</td>
            <td class="info-separator">:</td>
            <td colspan="2">{{ $employee->name }}</td>
        </tr>
        <tr>
            <td class="info-label">Designation</td>
            <td class="info-separator">:</td>
            <td colspan="2">{{ $employee->designation->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Department</td>
            <td class="info-separator">:</td>
            <td colspan="2">{{ $employee->department->name ?? '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Date of Joining</td>
            <td class="info-separator">:</td>
            <td colspan="2">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-m-Y') : '' }}</td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th class="text-left">Leave Type</th>
                <th>Carryable</th>
                <th>Max Carry</th>
                <th>Entitled</th>
                <th>Carry Forward</th>
                <th>Taken</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMaxCarry = 0;
                $totalEntitled = 0;
                $totalCarryForward = 0;
                $totalTaken = 0;
                $totalBalance = 0;
            @endphp
            @foreach($leaveBalances as $balance)
                @php
                    $type = $balance->leaveType;
                    
                    $c_carryable = $type->carry_forward ? 'Yes' : 'No';
                    $c_maxCarry = $type->carry_forward ? $type->max_carry_forward : 0;
                    
                    if ($type->carry_forward) {
                        $c_carryForward = max(0, $balance->opening_balance - $type->total_days_per_year);
                        $c_entitled = $balance->opening_balance - $c_carryForward;
                    } else {
                        $c_carryForward = 0;
                        $c_entitled = $balance->opening_balance;
                    }

                    $totalMaxCarry += $c_maxCarry;
                    $totalEntitled += $c_entitled;
                    $totalCarryForward += $c_carryForward;
                    $totalTaken += $balance->used_days;
                    $totalBalance += $balance->remaining_days;
                @endphp
                <tr>
                    <td class="text-left">{{ $type->name }}</td>
                    <td>{{ $c_carryable }}</td>
                    <td>{{ $c_maxCarry > 0 ? number_format($c_maxCarry, 2) : '' }}</td>
                    <td>{{ number_format($c_entitled, 2) }}</td>
                    <td>{{ number_format($c_carryForward, 2) }}</td>
                    <td>{{ number_format($balance->used_days, 2) }}</td>
                    <td>{{ number_format($balance->remaining_days, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2"></td>
                <td style="font-weight: bold;">{{ $totalMaxCarry > 0 ? number_format($totalMaxCarry, 2) : '' }}</td>
                <td style="font-weight: bold;">{{ number_format($totalEntitled, 2) }}</td>
                <td style="font-weight: bold;">{{ number_format($totalCarryForward, 2) }}</td>
                <td style="font-weight: bold;">{{ number_format($totalTaken, 2) }}</td>
                <td style="font-weight: bold;">{{ number_format($totalBalance, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                Claimed
            </td>
            <td>
                <div class="signature-line"></div>
                Claimed
            </td>
            <td>
                <div class="signature-line"></div>
                Head of HR
            </td>
            <td>
                <div class="signature-line"></div>
                CEO
            </td>
        </tr>
    </table>
</body>
</html>




