<table>
    <thead>
        <tr>
            <td rowspan="3" colspan="2"></td> {{-- Logo Space (A1:B3) --}}
            <td colspan="5" style="font-size: 18pt; font-weight: bold; text-align: left; vertical-align: bottom;">Mir Telecom Ltd.</td>
        </tr>
        <tr>
            <td colspan="5" style="font-size: 11pt; text-align: left; vertical-align: top;">House-04, Road-21, Gulshan-1, Dhaka-1212</td>
        </tr>
        <tr>
            <td colspan="5"></td>
        </tr>
        <tr>
            <td colspan="7" style="font-size: 14pt; font-weight: bold; text-align: center; background-color: #f2f2f2;">Leave Balance Report - {{ $year }}</td>
        </tr>
        <tr><td colspan="7"></td></tr>
        
        <tr>
            <td colspan="3" style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f2f2f2;">Employee Id:</td>
            <td colspan="4" style="text-align: center; border: 1px solid #000000;">{{ $employee->employee_code }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f2f2f2;">Name:</td>
            <td colspan="4" style="text-align: center; border: 1px solid #000000;">{{ $employee->name }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f2f2f2;">Designation:</td>
            <td colspan="4" style="text-align: center; border: 1px solid #000000;">{{ $employee->designation->name ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f2f2f2;">Department:</td>
            <td colspan="4" style="text-align: center; border: 1px solid #000000;">{{ $employee->department->name ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #f2f2f2;">Date of Joining:</td>
            <td colspan="4" style="text-align: center; border: 1px solid #000000;">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-m-Y') : '' }}</td>
        </tr>
        <tr><td colspan="7"></td></tr>
    </thead>
    
    <thead>
        <tr style="background-color: #007A10; color: #ffffff;">
            <th style="font-weight: bold; text-align: left; border: 1px solid #005a0b;">Leave Type</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Carryable</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Max Carry</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Entitled</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Carry Forward</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Taken</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #005a0b;">Balance</th>
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
                <td style="text-align: left; border: 1px solid #cccccc;">{{ $type->name }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $c_carryable }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ $c_maxCarry > 0 ? number_format($c_maxCarry, 2) : '' }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ number_format($c_entitled, 2) }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ number_format($c_carryForward, 2) }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ number_format($balance->used_days, 2) }}</td>
                <td style="text-align: center; border: 1px solid #cccccc;">{{ number_format($balance->remaining_days, 2) }}</td>
            </tr>
        @endforeach
        <tr style="background-color: #f2f2f2;">
            <td colspan="2" style="font-weight: bold; text-align: right; border: 1px solid #cccccc;">Total:</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #cccccc;">{{ $totalMaxCarry > 0 ? number_format($totalMaxCarry, 2) : '' }}</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #cccccc;">{{ number_format($totalEntitled, 2) }}</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #cccccc;">{{ number_format($totalCarryForward, 2) }}</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #cccccc;">{{ number_format($totalTaken, 2) }}</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #cccccc;">{{ number_format($totalBalance, 2) }}</td>
        </tr>
        
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7"></td></tr>
        <tr><td colspan="7"></td></tr>
        <tr>
            <td colspan="2" style="text-align: center; vertical-align: bottom; border-bottom: 1px solid #000000;"></td>
            <td></td>
            <td colspan="2" style="text-align: center; vertical-align: bottom; border-bottom: 1px solid #000000;"></td>
            <td></td>
            <td style="text-align: center; vertical-align: bottom; border-bottom: 1px solid #000000;"></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; font-weight: bold;">Claimed</td>
            <td></td>
            <td colspan="2" style="text-align: center; font-weight: bold;">Head of HR</td>
            <td></td>
            <td style="text-align: center; font-weight: bold;">CEO</td>
        </tr>
    </tbody>
</table>
