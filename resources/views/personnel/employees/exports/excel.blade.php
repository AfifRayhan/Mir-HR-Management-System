<table>
    <thead>
        <tr>
            <th colspan="{{ count($selectedColumns) + 1 }}" style="font-weight: bold; font-size: 16pt; text-align: center;">{{ $selectedOffice->name ?? 'Mir Telecom Ltd.' }}</th>
        </tr>
        <tr>
            <th colspan="{{ count($selectedColumns) + 1 }}" style="text-align: center;">House-04, Road-21, Gulshan-1, Dhaka-1212</th>
        </tr>
        <tr>
            <th colspan="{{ count($selectedColumns) + 1 }}" style="font-weight: bold; font-size: 12pt; text-align: right;">EMPLOYEE LISTING REPORT</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #007A10; color: #ffffff; border: 1px solid #000000;">#</th>
            @foreach($selectedColumns as $key)
                <th style="font-weight: bold; background-color: #007A10; color: #ffffff; border: 1px solid #000000;">{{ $allColumns[$key] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @php
            $currentOfficeId = null;
            $currentDeptId = null;
        @endphp
        @foreach($employees as $index => $employee)
            @if($currentOfficeId !== $employee->office_id)
                <tr style="background-color: #000000; color: #ffffff;">
                    <td colspan="{{ count($selectedColumns) + 1 }}" style="font-weight: bold; border: 1px solid #000000;">
                        Office: {{ $employee->office->name ?? 'N/A' }}
                    </td>
                </tr>
                @php $currentOfficeId = $employee->office_id; $currentDeptId = null; @endphp
            @endif

            @if($currentDeptId !== $employee->department_id)
                <tr style="background-color: #f1f5f9; color: #334155;">
                    <td colspan="{{ count($selectedColumns) + 1 }}" style="font-weight: bold; border: 1px solid #000000; padding-left: 15px;">
                        Department: {{ $employee->department->name ?? 'N/A' }}
                    </td>
                </tr>
                @php $currentDeptId = $employee->department_id; @endphp
            @endif

            <tr>
                <td style="text-align: center; border: 1px solid #dee2e6;">{{ $index + 1 }}</td>
                @foreach($selectedColumns as $key)
                    <td style="border: 1px solid #dee2e6;">
                        {{ \App\Exports\EmployeesExport::getColumnValue($employee, $key) }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
