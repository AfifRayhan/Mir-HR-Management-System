<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">

            <div class="row mb-4 align-items-center">
                <div class="col-12">
                    <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-success"></i>{{ __('Manual Leave') }}</h4>
                    <p class="text-muted mb-0">{{ __('Manually record an approved leave for any employee') }}</p>
                </div>
            </div>


            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 px-4 py-3 small shadow-sm mb-4" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="row g-4">
                <!-- Manual Leave Form -->
                <div class="col-lg-5">
                    <div class="hr-panel">
                        <h5 class="fw-bold mb-4 border-bottom pb-2">
                            <i class="bi bi-journal-plus me-2 text-success"></i>{{ __('Record Manual Leave') }}
                        </h5>

                        <div class="alert alert-info border-0 rounded-3 small mb-4 py-2 px-3"
                             style="background-color:#ddf1ddff; color:#007a10;">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ __('Leave recorded here is immediately marked as Approved and balance is deducted.') }}
                        </div>

                        <form action="{{ route('personnel.leave.manual.store') }}" method="POST" enctype="multipart/form-data" id="manualLeaveForm">
                            @csrf

                            {{-- Employee selector --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted" for="employee_id">
                                    {{ __('Employee') }} <span class="text-danger">*</span>
                                </label>
                                <select name="employee_id" id="employee_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('Select Employee') }}</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        data-office="{{ $emp->office_id }}"
                                        {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                        @if($emp->employee_code) ({{ $emp->employee_code }}) @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Leave Type --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted" for="leave_type_id">
                                    {{ __('Leave Type') }} <span class="text-danger">*</span>
                                </label>
                                <select name="leave_type_id" id="leave_type_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('Select Type') }}</option>
                                    @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Dates --}}
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('From Date') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="from_date" id="from_date" class="form-control rounded-3"
                                           value="{{ old('from_date') }}" placeholder="Select date" readonly required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('To Date') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="to_date" id="to_date" class="form-control rounded-3"
                                           value="{{ old('to_date') }}" placeholder="Select date" readonly required>
                                </div>
                            </div>

                            {{-- Live days counter --}}
                            <div id="leave_days_display" class="mb-3 d-none" 
                                 data-holidays="{{ json_encode($weeklyHolidayDays) }}"
                                 data-national-holidays="{{ json_encode($nationalHolidayDates) }}">
                                <div class="alert alert-info py-2 px-3 rounded-pill d-flex align-items-center justify-content-between mb-0 shadow-sm border-0" style="background-color: #c8e6c9ff; color: #007a10;">
                                    <span class="small fw-bold text-success"><i class="bi bi-calendar-event me-2"></i>{{ __('Total Days') }}:</span>
                                    <span id="total_days_count" class="badge bg-success rounded-pill">0</span>
                                </div>
                            </div>

                            {{-- Reason --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Reason / Remarks') }} <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control rounded-3" rows="3" required
                                          placeholder="{{ __('Why is this leave being recorded?') }}">{{ old('reason') }}</textarea>
                            </div>

                            {{-- Leave Address --}}
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Leave Address') }}</label>
                                <input type="text" name="leave_address" class="form-control rounded-3"
                                       value="{{ old('leave_address') }}"
                                       placeholder="{{ __('Where will the employee be during this leave?') }}">
                            </div>

                            {{-- Supporting Document --}}
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Supporting Document') }}</label>
                                <input type="file" name="supporting_document" class="form-control rounded-3 shadow-sm"
                                       accept=".pdf,image/*,.doc,.docx">
                                <div class="form-text small" style="font-size:0.75rem;">
                                    {{ __('Optional (PDF, JPG, PNG, DOC, DOCX).') }}
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-check2-circle me-2"></i>{{ __('Record & Approve Leave') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Recent manual leaves (all approved leaves) -->
                <div class="col-lg-7">
                    <div class="hr-panel p-0 overflow-hidden">
                        <div class="px-4 py-3 border-bottom">
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-clock-history me-2 text-success"></i>{{ __('Recent Manual Leaves') }}
                            </h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table hr-table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Employee') }}</th>
                                        <th>{{ __('Leave Type') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Days') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                        <th class="pe-4">{{ __('Recorded By') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $recentManual = \App\Models\LeaveApplication::with(['employee', 'leaveType', 'approver'])
                                            ->where('status', 'approved')
                                            ->whereNotNull('approved_by')
                                            ->orderBy('created_at', 'desc')
                                            ->limit(20)
                                            ->get();
                                    @endphp
                                    @forelse($recentManual as $app)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="emp-avatar-sm me-3">
                                                    {{ strtoupper(substr($app->employee->name ?? '?', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-gray-800 small">{{ $app->employee->name ?? '--' }}</div>
                                                    <div class="text-muted" style="font-size:0.7rem;">{{ $app->employee->employee_code ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ match(true) { str_contains(strtolower($app->leaveType->name), 'casual') => 'bg-primary', str_contains(strtolower($app->leaveType->name), 'sick') => 'bg-danger', str_contains(strtolower($app->leaveType->name), 'earn') => 'bg-success', str_contains(strtolower($app->leaveType->name), 'emergency') => 'bg-warning text-dark', default => 'bg-info text-dark' } }} rounded-pill px-3">{{ $app->leaveType->name ?? '--' }}</span>
                                        </td>
                                        <td>
                                            <div class="small fw-bold text-gray-700">{{ \Carbon\Carbon::parse($app->from_date)->format('d M Y') }}</div>
                                            <div class="small text-muted">{{ __('to') }} {{ \Carbon\Carbon::parse($app->to_date)->format('d M Y') }}</div>
                                        </td>
                                        <td><span class="badge bg-secondary rounded-pill">{{ $app->total_days }}</span></td>
                                        <td>
                                            <span class="d-inline-block text-truncate small text-muted" style="max-width:160px;"
                                                  title="{{ $app->reason }}">{{ $app->reason }}</span>
                                        </td>
                                        <td class="pe-4">
                                            @if($app->approver)
                                            <div class="small fw-bold text-dark">{{ $app->approver->name }}</div>
                                            <div class="text-muted" style="font-size:0.7rem;">{{ $app->approved_at?->format('d M, h:i A') }}</div>
                                            @else
                                            <span class="text-muted small">--</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="bi bi-inbox d-block mb-3 fs-1 text-gray-300"></i>
                                            <span class="text-gray-500">{{ __('No approved leave records found.') }}</span>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSelect = document.getElementById('employee_id');
        const daysDisplay    = document.getElementById('leave_days_display');
        const daysCount      = document.getElementById('total_days_count');

        function calculateDays() {
            const employeeId = employeeSelect.value;
            const fromDate = fromPicker.selectedDates[0];
            const toDate   = toPicker.selectedDates[0];

            if (employeeId && fromDate && toDate && toDate >= fromDate) {
                const fromStr = fromPicker.formatDate(fromDate, 'Y-m-d');
                const toStr   = fromPicker.formatDate(toDate, 'Y-m-d');

                fetch(`/api/check-working-days?employee_id=${employeeId}&from_date=${fromStr}&to_date=${toStr}`)
                    .then(r => r.json())
                    .then(data => {
                        daysCount.textContent = data.total_days;
                        daysDisplay.classList.remove('d-none');
                    })
                    .catch(err => {
                        console.error('Error calculating days:', err);
                        daysDisplay.classList.add('d-none');
                    });
            } else {
                daysDisplay.classList.add('d-none');
            }
        }

        const fromPicker = flatpickr('#from_date', {
            dateFormat: 'Y-m-d',
            allowInput: false,
            onChange: function() {
                toPicker.set('minDate', fromPicker.selectedDates[0] || null);
                calculateDays();
            }
        });

        const toPicker = flatpickr('#to_date', {
            dateFormat: 'Y-m-d',
            allowInput: false,
            onChange: calculateDays
        });

        employeeSelect.addEventListener('change', calculateDays);
    });
    </script>
    @endpush
</x-app-layout>
