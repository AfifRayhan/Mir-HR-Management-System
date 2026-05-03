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

    @php 
        $user = auth()->user();
        $roleName = optional($user->role)->name;
        $isAdmin = $roleName === 'HR Admin' || $roleName === 'Superadmin';
        // isTeamLeadLayout is passed from controller, but let's be safe
        $isTeamLeadLayout = $isTeamLeadLayout ?? false;
    @endphp

    <div class="ui-layout {{ $isAdmin ? '' : ($isTeamLeadLayout ? 'ui-scope-lead' : 'ui-scope-emp') }}">
        @if($isAdmin)
            @include('partials.ui-sidebar')
        @elseif($isTeamLeadLayout)
            @include('partials.team-lead-sidebar')
        @else
            @include('partials.employee-sidebar')
        @endif

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
                        <div class="d-flex align-items-center gap-3">
                            @if($canEdit)
                                {{-- Auto-Fill button --}}
                                <div class="text-center">
                                    <button type="button" id="btn-auto-fill" class="btn btn-outline-primary btn-sm px-3"
                                            data-url="{{ route('overtimes.auto-fill') }}"
                                            data-employee="{{ $selectedEmployee->id }}"
                                            data-month="{{ $month }}"
                                            data-year="{{ $year }}">
                                        <i class="bi bi-magic me-1"></i> Auto-Fill from Attendance
                                    </button>
                                    <div class="text-xs text-muted mt-1">Fills empty rows only · won't overwrite saved data</div>
                                </div>
                            @endif

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
                                                    <input type="text" name="ot[{{ $dateStr }}][start]" class="form-control form-control-xs timepicker ot-input" value="{{ $record ? $record->ot_start : '' }}" data-date="{{ $dateStr }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                    @if($canEdit)
                                                        <button type="button" class="btn-clear-time" onclick="clearTime(this, '{{ $dateStr }}')">✕</button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="time-input-container">
                                                    <input type="text" name="ot[{{ $dateStr }}][stop]" class="form-control form-control-xs timepicker ot-input" value="{{ $record ? $record->ot_stop : '' }}" data-date="{{ $dateStr }}" {{ !$canEdit ? 'readonly' : '' }}>
                                                    @if($canEdit)
                                                        <button type="button" class="btn-clear-time" onclick="clearTime(this, '{{ $dateStr }}')">✕</button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center text-xs">
                                                <span id="total_hours_{{ $dateStr }}">{{ $record ? $record->total_ot_hours : '0.00' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="ot[{{ $dateStr }}][workday_plus_5]" class="form-check-input ot-check" {{ $record && $record->is_workday_duty_plus_5 ? 'checked' : '' }} data-date="{{ $dateStr }}" {{ !$canEdit ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="ot[{{ $dateStr }}][holiday_plus_5]" class="form-check-input ot-check" {{ $record && $record->is_holiday_duty_plus_5 ? 'checked' : '' }} data-date="{{ $dateStr }}" {{ !$canEdit ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="ot[{{ $dateStr }}][eid_duty]" class="form-check-input ot-check" {{ $record && $record->is_eid_duty ? 'checked' : '' }} data-date="{{ $dateStr }}" {{ !$canEdit ? 'disabled' : '' }}>
                                            </td>
                                            <td>
                                                <input type="text" name="ot[{{ $dateStr }}][remarks]" class="form-control form-control-xs" value="{{ $record ? $record->remarks : '' }}" {{ !$canEdit ? 'readonly' : '' }}>
                                            </td>
                                            <td class="text-end text-xs fw-bold">
                                                <span id="amount_{{ $dateStr }}">{{ $record ? number_format($record->amount, 2) : '0.00' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <!-- Row 1: Total hours/Shift -->
                                    <tr class="border-top border-2">
                                        <td class="fw-bold text-xs">Gross Salary:</td>
                                        <td class="text-end text-xs" id="footer_gross_salary">0.00</td>
                                        <td class="text-end fw-bold text-xs">Total hours/Shift</td>
                                        <td class="text-center fw-bold text-xs" id="summary_hourly_ot_hours">0.00</td>
                                        <td class="text-center fw-bold text-xs" id="summary_workday_count">0</td>
                                        <td class="text-center fw-bold text-xs" id="summary_holiday_count">0</td>
                                        <td class="text-center fw-bold text-xs" id="summary_eid_count">0</td>
                                        <td></td>
                                    </tr>
                                    <!-- Row 2: Rate -->
                                    <tr>
                                        <td class="fw-bold text-xs">Basic Salary</td>
                                        <td class="text-end text-xs" id="footer_basic_salary">0.00</td>
                                        <td class="text-end text-muted small text-xs">Rate per hour/Shift/ Eid Special</td>
                                        <td class="text-center text-muted small text-xs" id="summary_hourly_rate">0.00</td>
                                        <td class="text-center text-muted small text-xs" id="summary_workday_rate">0.00</td>
                                        <td class="text-center text-muted small text-xs" id="summary_holiday_rate">0.00</td>
                                        <td class="text-center text-muted small text-xs" id="summary_eid_rate">0.00</td>
                                        <td></td>
                                    </tr>
                                    <!-- Row 3: Multiplying Factor -->
                                    <tr>
                                        <td class="fw-bold text-xs">Per Day</td>
                                        <td class="text-end text-xs" id="footer_per_day">0.00</td>
                                        <td class="text-end text-muted small text-xs">Multiplying Factor</td>
                                        <td class="text-center text-muted small text-xs">1</td>
                                        <td class="text-center text-muted small text-xs">2</td>
                                        <td class="text-center text-muted small text-xs">2</td>
                                        <td class="text-center text-muted small text-xs">3</td>
                                        <td></td>
                                    </tr>
                                    <!-- Row 4: Sub-Total -->
                                    <tr>
                                        <td class="fw-bold text-xs">Per Hour</td>
                                        <td class="text-end text-xs" id="footer_per_hour">0.00</td>
                                        <td class="text-end fw-bold text-xs">Sub-Total</td>
                                        <td class="text-center fw-bold text-success text-xs" id="summary_hourly_subtotal">0.00</td>
                                        <td class="text-center fw-bold text-success text-xs" id="summary_workday_subtotal">0.00</td>
                                        <td class="text-center fw-bold text-success text-xs" id="summary_holiday_subtotal">0.00</td>
                                        <td class="text-center fw-bold text-success text-xs" id="summary_eid_subtotal">0.00</td>
                                        <td></td>
                                    </tr>
                                    <!-- Row 5: Grand Total -->
                                    <tr class="table-dark">
                                        <td colspan="7" class="text-end fw-bold">Total Payable Amount</td>
                                        <td colspan="2" class="text-end fw-bold" id="summary_grand_total">0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-3">
                            {{-- Download dropdown --}}
                            <div class="dropdown">
                                <button class="btn btn-outline-success px-4 rounded-pill shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-download me-1"></i> Download Report
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><h6 class="dropdown-header text-xs text-uppercase tracking-wider">Export Form</h6></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('overtimes.export', ['employee_id' => $selectedEmployee->id, 'month' => $month, 'year' => $year, 'format' => 'pdf']) }}"><i class="bi bi-file-pdf text-danger me-2"></i> PDF Document</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('overtimes.export', ['employee_id' => $selectedEmployee->id, 'month' => $month, 'year' => $year, 'format' => 'excel']) }}"><i class="bi bi-file-excel text-success me-2"></i> Excel Spreadsheet</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('overtimes.export', ['employee_id' => $selectedEmployee->id, 'month' => $month, 'year' => $year, 'format' => 'csv']) }}"><i class="bi bi-file-text text-info me-2"></i> CSV Format</a></li>
                                    <li><a class="dropdown-item py-2" href="{{ route('overtimes.export', ['employee_id' => $selectedEmployee->id, 'month' => $month, 'year' => $year, 'format' => 'word']) }}"><i class="bi bi-file-word text-primary me-2"></i> Word Document</a></li>
                                </ul>
                            </div>

                            @if($canEdit)
                                <button type="submit" class="btn btn-success px-5 rounded-pill shadow-sm">
                                    <i class="bi bi-save me-2"></i>{{ __('Save Overtime Records') }}
                                </button>
                            @endif
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
            const perHourRate    = Number("{{ $perHourRate ?? 0 }}");
            const gross          = parseFloat(otForm.dataset.gross) || 0;
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
                        holidayCheck.prop('checked', false);
                    } else if (isOff) {
                        holidayCheck.prop('checked', true);
                        eidCheck.prop('checked', false);
                    }
                    
                    // Workday Duty checkbox toggles when crossing 5 hours mark (Workdays only)
                    // OR when crossing 12 hours mark (Off/Eid days)
                    if (hours > 12 || (hours > 5 && !isOff && !isEid)) {
                        workdayCheck.prop('checked', true);
                    } else {
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

                // Two-tier formula mirrors PHP calculateAmount() exactly:
                //   ≤ 5 hrs → hours × perHourRate × multiplier
                //   > 5 hrs → one full shift × multiplier
                // Tier 1: Floor hours, no multipliers
                if (hours <= 5) {
                    const amount = Math.floor(hours) * perHourRate;
                    $(`#amount_${date}`).text(amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                } else {
                    let units = 0;
                    
                    if (eidCheck.is(':checked')) {
                        units = 3;
                    } else if (holidayCheck.is(':checked')) {
                        units = 2;
                    }

                    if (workdayCheck.is(':checked')) {
                        if (eidCheck.is(':checked')) {
                            units += 3;
                        } else if (holidayCheck.is(':checked')) {
                            units += 2;
                        } else {
                            units += 2;
                            if (hours > 12) units += 1;
                        }
                    }
                    
                    const amount = fullShiftIncome * units;
                    $(`#amount_${date}`).text(amount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }
                updateGrandTotal();
            }

            function updateGrandTotal() {
                let total = 0;
                let totalRawHours = 0;
                let hourlyOTHours = 0;
                let hourlyUnits = 0;
                let workdayUnits = 0;
                let holidayUnits = 0;
                let eidUnits = 0;
                
                let workdayCount = 0;
                let holidayCount = 0;
                let eidBaseCount = 0;
                let eidBonusCount = 0;

                // Total Payable logic (from individual row amounts)
                $('[id^="amount_"]').each(function() {
                    const val = parseFloat($(this).text().replace(/,/g, '')) || 0;
                    total += val;
                });

                // Raw hours and category breakdown logic
                $('[id^="total_hours_"]').each(function() {
                    const hours = parseFloat($(this).text()) || 0;
                    if (hours <= 0) return;

                    const row = $(this).closest('tr');
                    const isWorkdayChecked = row.find('.ot-check[name*="workday_plus_5"]').is(':checked');
                    const isHolidayChecked = row.find('.ot-check[name*="holiday_plus_5"]').is(':checked');
                    const isEidChecked = row.find('.ot-check[name*="eid_duty"]').is(':checked');

                    if (!isWorkdayChecked && !isHolidayChecked && !isEidChecked) {
                        // Hourly OT Category (Floor hours for BOTH count and payment)
                        const floorHours = Math.floor(hours);
                        hourlyOTHours += floorHours;
                        hourlyUnits += floorHours;
                    }
                });

                $('.ot-check[name*="[workday_plus_5]"]:checked').each(function() {
                    const row = $(this).closest('tr');
                    const hours = parseFloat(row.find('span[id^="total_hours_"]').text()) || 0;
                    const isOff = row.data('is-off') == '1';
                    const isEid = row.data('is-eid') == '1';
                    
                    if (isEid) {
                        eidBonusCount++;
                        eidUnits += 3;
                    } else if (isOff) {
                        workdayUnits += 2;
                        workdayCount++;
                    } else {
                        workdayUnits += 2;
                        workdayCount++;
                        if (hours > 12) workdayUnits += 1;
                    }
                });
                
                $('.ot-check[name*="[holiday_plus_5]"]:checked').each(function() { 
                    holidayUnits += 2; 
                    holidayCount++;
                });

                $('.ot-check[name*="[eid_duty]"]:checked').each(function() { 
                    eidUnits += 3; 
                    eidBaseCount++;
                });

                // Update UI Labels
                const basic = gross * 0.6;
                const perDay = fullShiftIncome;
                
                $('#footer_gross_salary').text(gross.toLocaleString());
                $('#footer_basic_salary').text(basic.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#footer_per_day').text(perDay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#footer_per_hour').text(perHourRate.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                $('#total_payable_display').text(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' BDT');
                $('#summary_grand_total').text(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                $('#summary_hourly_ot_hours').text(hourlyOTHours.toFixed(2));
                $('#summary_workday_count').text(workdayCount);
                $('#summary_holiday_count').text(holidayCount);
                
                let eidCountDisplay = eidBaseCount + eidBonusCount;
                if (eidBaseCount > 0 && eidBonusCount > 0) {
                    eidCountDisplay = `${eidBaseCount}+${eidBonusCount}`;
                } else if (eidBonusCount > 0 && eidBaseCount === 0) {
                    eidCountDisplay = eidBonusCount;
                }
                $('#summary_eid_count').text(eidCountDisplay);

                $('#summary_hourly_rate').text(perHourRate.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_workday_rate').text(fullShiftIncome.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_holiday_rate').text(fullShiftIncome.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_eid_rate').text(fullShiftIncome.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                $('#summary_hourly_subtotal').text((perHourRate * hourlyUnits).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_workday_subtotal').text((fullShiftIncome * workdayUnits).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_holiday_subtotal').text((fullShiftIncome * holidayUnits).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#summary_eid_subtotal').text((fullShiftIncome * eidUnits).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }

            $('.ot-input, .ot-check').on('change', function() {
                calculateAmount($(this).data('date'));
            });

            updateGrandTotal();
        });
    </script>

    @if($canEdit)
    <script>
        $(function () {
            // ── Auto-Fill from Attendance ──────────────────────────────────────────────
            $('#btn-auto-fill').on('click', function () {
                const btn = $(this);

                Swal.fire({
                    title: 'Auto-Fill from Attendance?',
                    text: 'This will fill empty rows with suggested overtime based on attendance logs. Saved data will not be overwritten.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, fill it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#10b981',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Loading...');

                        $.getJSON(btn.data('url'), {
                            employee_id: btn.data('employee'),
                            month:       btn.data('month'),
                            year:        btn.data('year'),
                        })
                        .done(function (data) {
                            const suggestions = data.suggestions || {};
                            let filled = 0;

                            $.each(suggestions, function (date, info) {
                                const startInput = $(`input[name="ot[${date}][start]"]`);
                                const stopInput  = $(`input[name="ot[${date}][stop]"]`);

                                // Non-destructive: only fill if both inputs are currently empty
                                if (startInput.length && !startInput.val() && !stopInput.val()) {
                                    if (startInput[0]._flatpickr) {
                                        startInput[0]._flatpickr.setDate(info.ot_start, true, 'H:i');
                                    } else {
                                        startInput.val(info.ot_start);
                                    }
                                    if (stopInput[0]._flatpickr) {
                                        stopInput[0]._flatpickr.setDate(info.ot_stop, true, 'H:i');
                                    } else {
                                        stopInput.val(info.ot_stop);
                                    }

                                    // Auto-check boxes for Off-days and Eid
                                    const tr = $(`tr[data-date="${date}"]`);
                                    const isOff = tr.data('is-off') === 1;
                                    const isEid = tr.data('is-eid') === 1;

                                    if (isEid) {
                                        $(`input[name="ot[${date}][eid_duty]"]`).prop('checked', true);
                                    } else if (isOff) {
                                        $(`input[name="ot[${date}][holiday_plus_5]"]`).prop('checked', true);
                                    }

                                    // Trigger existing calculateAmount() for this date via the change event
                                    startInput.trigger('change');
                                    filled++;
                                }
                            });

                            if (filled === 0) {
                                Swal.fire({
                                    title: 'No New Entries',
                                    text: 'All rows are already filled or attendance logs show no overtime for the remaining days.',
                                    icon: 'info'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Success!',
                                    text: `Auto-filled ${filled} day(s). Please review the times, then click "Save Overtime Records" to commit the changes.`,
                                    icon: 'success'
                                });
                            }
                    })
                    .fail(function () {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to load attendance data. Please try again.',
                            icon: 'error'
                        });
                    })
                    .always(function () {
                        btn.prop('disabled', false).html('<i class="bi bi-magic me-1"></i> Auto-Fill from Attendance');
                    });
                }
            });
        });
    });
    </script>
    @endif
    @endpush
</x-app-layout>
