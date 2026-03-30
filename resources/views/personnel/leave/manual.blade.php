<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">

            <div class="row mb-4 align-items-center">
                <div class="col-12">
                    <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>{{ __('Manual Leave') }}</h4>
                    <p class="text-muted mb-0">{{ __('Manually record an approved leave for any employee') }}</p>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-2 small shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 px-4 py-3 small shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

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
                            <i class="bi bi-journal-plus me-2 text-primary"></i>{{ __('Record Manual Leave') }}
                        </h5>

                        <div class="alert alert-info border-0 rounded-3 small mb-4 py-2 px-3"
                             style="background-color:#e3f2fd; color:#0d47a1;">
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
                                        {{ $type->name }} ({{ $type->total_days_per_year }} days/yr)
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Dates --}}
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('From Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="from_date" id="from_date" class="form-control rounded-3"
                                           value="{{ old('from_date') }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('To Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="to_date" id="to_date" class="form-control rounded-3"
                                           value="{{ old('to_date') }}" required>
                                </div>
                            </div>

                            {{-- Live days counter --}}
                            <div id="leave_days_display" class="mb-3 d-none">
                                <div class="alert alert-info py-2 px-3 rounded-pill d-flex align-items-center justify-content-between mb-0 shadow-sm border-0"
                                     style="background-color:#e3f2fd; color:#0d47a1;">
                                    <span class="small fw-bold">
                                        <i class="bi bi-calendar-event me-2"></i>{{ __('Working Days') }}:
                                    </span>
                                    <span id="total_days_count" class="badge bg-primary rounded-pill">0</span>
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

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
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
                                <i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Recent Manual Leaves') }}
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
                                            <span class="badge bg-info text-dark rounded-pill px-3">{{ $app->leaveType->name ?? '--' }}</span>
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
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const employeeSelect = document.getElementById('employee_id');
        const fromDateInput  = document.getElementById('from_date');
        const toDateInput    = document.getElementById('to_date');
        const daysDisplay    = document.getElementById('leave_days_display');
        const daysCount      = document.getElementById('total_days_count');

        // Weekly holiday days fetched per employee office
        let weeklyHolidays = [];

        function fetchHolidaysAndRecalculate() {
            const officeId = employeeSelect.options[employeeSelect.selectedIndex]?.dataset?.office;
            if (!officeId) {
                weeklyHolidays = [];
                calculateDays();
                return;
            }

            fetch(`/api/weekly-holidays?office_id=${officeId}`)
                .then(r => r.json())
                .then(data => {
                    weeklyHolidays = data.holiday_days || [];
                    calculateDays();
                })
                .catch(() => {
                    weeklyHolidays = [];
                    calculateDays();
                });
        }

        function calculateDays() {
            const fromDate = fromDateInput.value;
            const toDate   = toDateInput.value;

            if (fromDate && toDate) {
                const start = new Date(fromDate);
                const end   = new Date(toDate);

                if (end >= start) {
                    let totalDays = 0;
                    let current   = new Date(start);

                    while (current <= end) {
                        const dayName = current.toLocaleDateString('en-US', { weekday: 'long' });
                        if (!weeklyHolidays.includes(dayName)) {
                            totalDays++;
                        }
                        current.setDate(current.getDate() + 1);
                    }

                    daysCount.textContent = totalDays;
                    daysDisplay.classList.remove('d-none');
                } else {
                    daysDisplay.classList.add('d-none');
                }
            } else {
                daysDisplay.classList.add('d-none');
            }
        }

        employeeSelect.addEventListener('change', fetchHolidaysAndRecalculate);
        fromDateInput.addEventListener('change', calculateDays);
        toDateInput.addEventListener('change', calculateDays);

        // Re-trigger if old values are present (validation failure redirect)
        if (employeeSelect.value) {
            fetchHolidaysAndRecalculate();
        }
    });
    </script>
    @endpush
</x-app-layout>
