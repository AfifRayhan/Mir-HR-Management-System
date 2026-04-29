<x-app-layout>
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .ot-table th { font-size: 0.75rem; vertical-align: middle; text-align: center; background: #f8f9fa; }
        .ot-table td { vertical-align: middle; padding: 4px; }
        .form-control-xs { padding: 0.2rem 0.4rem; font-size: 0.75rem; height: auto; }
        .text-xs { font-size: 0.7rem; }
        .bg-dayoff { background-color: #ffeef0; }
        .ui-panel { border-radius: 1rem; border: none; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05); background: #fff; padding: 1.5rem; margin-bottom: 2rem; }
        .ui-panel-title { font-weight: 700; color: #334155; margin-bottom: 1.5rem; display: flex; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; }
        .time-input-container { position: relative; display: flex; align-items: center; }
        .btn-clear-time { position: absolute; right: 2px; padding: 0 4px; font-size: 0.7rem; color: #94a3b8; cursor: pointer; border: none; background: transparent; display: none; }
        .time-input-container:hover .btn-clear-time { display: block; }
        .btn-clear-time:hover { color: #ef4444; }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Overtime (OT) Management') }}</h5>
                        <p class="mb-0 small text-muted">{{ __('Configure and calculate monthly overtime for employees.') }}</p>
                    </div>
                </div>
            </div>

            <div class="ui-panel mb-4">
                <form action="{{ route('overtimes.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">{{ __('Select Employee') }}</label>
                        <select name="employee_id" id="employee_select" class="form-select select2" required>
                            <option value="">{{ __('Choose...') }}</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">{{ __('Month') }}</label>
                        <select name="month" class="form-select">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">{{ __('Year') }}</label>
                        <select name="year" class="form-select">
                            @foreach(range(date('Y') - 1, date('Y') + 1) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">{{ __('Load Form') }}</button>
                    </div>
                </form>
            </div>

            @if($selectedEmployee)
                <div class="ui-panel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="fw-bold text-primary">{{ $selectedEmployee->name }}</div>
                                <div class="text-xs text-muted">ID: {{ $selectedEmployee->employee_code }} | Grade: {{ $selectedEmployee->grade->name ?? 'N/A' }}</div>
                            </div>
                            <div class="px-3 border-start">
                                <div class="fw-bold">Gross Salary</div>
                                <div class="text-xs text-muted">{{ number_format($selectedEmployee->gross_salary, 2) }} BDT</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success" id="total_payable_display">0.00 BDT</div>
                            <div class="text-xs text-muted">Total Payable Amount</div>
                        </div>
                    </div>

                    <form action="{{ route('overtimes.save') }}" method="POST" id="ot-form" 
                          data-gross="{{ $selectedEmployee->gross_salary ?? 0 }}" 
                          data-grade="{{ $selectedEmployee->grade->name ?? '' }}">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        <div class="table-responsive">
                            <table class="table table-bordered ot-table">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="width: 150px;">Day-Date</th>
                                        <th colspan="2">Overtime</th>
                                        <th rowspan="2">Total OT Hour</th>
                                        <th rowspan="2">Workday Duty (+5 hrs)</th>
                                        <th rowspan="2">Dayoff/Holiday</th>
                                        <th rowspan="2">Eid Special Duty</th>
                                        <th rowspan="2">Remarks</th>
                                        <th rowspan="2">Amount</th>
                                    </tr>
                                    <tr>
                                        <th>Start</th>
                                        <th>Stop</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daysInMonth as $day)
                                        @php
                                            $dateStr = $day->format('Y-m-d');
                                            $record = $overtimeRecords[$dateStr] ?? null;
                                            $roster = $rosterSchedules[$dateStr] ?? null;
                                            $holiday = $holidays[$dateStr] ?? null;
                                            
                                            $isRosterEmployee = ($selectedEmployee->officeTime && $selectedEmployee->officeTime->shift_name === 'Roster');
                                            
                                            if ($isRosterEmployee) {
                                                $isWeeklyOff = ($roster && strtolower($roster->shift_type) === 'off');
                                            } else {
                                                $isWeeklyOff = in_array($day->format('l'), $weeklyHolidays); 
                                            }

                                            $isEidDay = ($holiday && $holiday['type'] === 'Eid Day');
                                            $isHoliday = (bool)$holiday;
                                            
                                            $isDayOff = $isWeeklyOff || $isHoliday;
                                        @endphp
                                        <tr class="{{ $isDayOff ? 'bg-dayoff' : '' }}" 
                                            data-date="{{ $dateStr }}"
                                            data-is-off="{{ $isDayOff ? '1' : '0' }}" 
                                            data-is-eid="{{ $isEidDay ? '1' : '0' }}">
                                            <td class="text-xs fw-bold">
                                                {{ $day->format('l, M d') }}
                                                @if($isEidDay) 
                                                    <span class="badge bg-success text-xs ms-1">Eid</span> 
                                                @elseif($isDayOff) 
                                                    <span class="badge bg-danger text-xs ms-1">Off</span> 
                                                @endif
                                            </td>
                                            <td>
                                                <div class="time-input-container">
                                                    <input type="text" name="ot[{{ $dateStr }}][start]" class="form-control form-control-xs timepicker ot-input" value="{{ $record ? $record->ot_start : '' }}" data-date="{{ $dateStr }}">
                                                    <button type="button" class="btn-clear-time" onclick="clearTime(this, '{{ $dateStr }}')">✕</button>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="time-input-container">
                                                    <input type="text" name="ot[{{ $dateStr }}][stop]" class="form-control form-control-xs timepicker ot-input" value="{{ $record ? $record->ot_stop : '' }}" data-date="{{ $dateStr }}">
                                                    <button type="button" class="btn-clear-time" onclick="clearTime(this, '{{ $dateStr }}')">✕</button>
                                                </div>
                                            </td>
                                            <td class="text-center text-xs">
                                                <span id="total_hours_{{ $dateStr }}">{{ $record ? $record->total_ot_hours : '0.00' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="ot[{{ $dateStr }}][workday_plus_5]" class="form-check-input ot-check" {{ $record && $record->is_workday_duty_plus_5 ? 'checked' : '' }} data-date="{{ $dateStr }}">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="ot[{{ $dateStr }}][holiday_plus_5]" class="form-check-input ot-check" {{ $record && $record->is_holiday_duty_plus_5 ? 'checked' : '' }} data-date="{{ $dateStr }}">
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="ot[{{ $dateStr }}][eid_duty]" class="form-check-input ot-check" {{ $record && $record->is_eid_duty ? 'checked' : '' }} data-date="{{ $dateStr }}">
                                            </td>
                                            <td>
                                                <input type="text" name="ot[{{ $dateStr }}][remarks]" class="form-control form-control-xs" value="{{ $record ? $record->remarks : '' }}">
                                            </td>
                                            <td class="text-end text-xs fw-bold">
                                                <span id="amount_{{ $dateStr }}">{{ $record ? number_format($record->amount, 2) : '0.00' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success px-5 rounded-pill shadow-sm">
                                <i class="bi bi-save me-2"></i>{{ __('Save Overtime Records') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </main>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap-5' });

            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });

            const otForm = document.getElementById('ot-form');
            const gross = parseFloat(otForm.dataset.gross) || 0;
            const grade = otForm.dataset.grade || '';
            const fullShiftIncome = (gross * 0.6) / 30;

            window.clearTime = function(btn, date) {
                const input = $(btn).siblings('input');
                input.val('');
                if (input[0]._flatpickr) {
                    input[0]._flatpickr.clear();
                }
                calculateAmount(date);
            };

            function calculateAmount(date) {
                const start = $(`input[name="ot[${date}][start]"]`).val();
                const stop = $(`input[name="ot[${date}][stop]"]`).val();

                let hours = 0;
                if (start && stop) {
                    const s = new Date(`2000-01-01 ${start}`);
                    const e = new Date(`2000-01-01 ${stop}`);
                    if (e < s) e.setDate(e.getDate() + 1);
                    hours = (e - s) / (1000 * 60 * 60);
                }

                $(`#total_hours_${date}`).text(hours.toFixed(2));

                // Auto-toggle Duty Types
                const row = $(`tr[data-date="${date}"]`);
                const isOff = row.data('is-off') == '1';
                const isEid = row.data('is-eid') == '1';

                const workdayCheck = $(`input[name="ot[${date}][workday_plus_5]"]`);
                const holidayCheck = $(`input[name="ot[${date}][holiday_plus_5]"]`);
                const eidCheck     = $(`input[name="ot[${date}][eid_duty]"]`);

                if (hours > 0) {
                    if (isEid) {
                        eidCheck.prop('checked', true);
                    } else if (isOff) {
                        holidayCheck.prop('checked', true);
                    }
                    
                    // Specific rule: 12+ hours always marks Workday Duty
                    if (hours >= 12) {
                        workdayCheck.prop('checked', true);
                    } else if (!isOff && !isEid && hours >= 5) {
                        // Standard workday rule
                        workdayCheck.prop('checked', true);
                    } else if (isOff || isEid) {
                        // If it's an off day and < 12 hours, don't mark workday duty
                        workdayCheck.prop('checked', false);
                    }
                } else {
                    workdayCheck.prop('checked', false);
                    holidayCheck.prop('checked', false);
                    eidCheck.prop('checked', false);
                }

                const workdayPlus5 = workdayCheck.is(':checked');
                const holidayPlus5 = holidayCheck.is(':checked');
                const eidDuty      = eidCheck.is(':checked');

                $(`#total_hours_${date}`).text(hours.toFixed(2));

                let multiplier = 1;
                if (eidDuty) multiplier = 3;
                else if (holidayPlus5) multiplier = 2;

                const baseValue = fullShiftIncome * multiplier;
                
                let count = 0;
                if (eidDuty) count++;
                if (holidayPlus5) count++;
                if (workdayPlus5) count++;

                const amount = baseValue * count;

                $(`#amount_${date}`).text(amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                updateGrandTotal();
            }

            function updateGrandTotal() {
                let total = 0;
                $('[id^="amount_"]').each(function() {
                    const val = parseFloat($(this).text().replace(/,/g, '')) || 0;
                    total += val;
                });
                $('#total_payable_display').text(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' BDT');
            }

            $('.ot-input, .ot-check').on('change', function() {
                calculateAmount($(this).data('date'));
            });

            updateGrandTotal();
        });
    </script>
    @endpush
</x-app-layout>
