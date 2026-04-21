<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .balance-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1rem 1.15rem;
            transition: transform 0.2s;
        }

        .balance-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
    @endpush

    <div class="hr-layout">
        @include('partials.team-lead-sidebar')

        <main class="hr-main">

            <div class="row mb-4 align-items-center">
                <div class="col-12">
                    <h4 class="fw-bold mb-1">{{ __('My Leave Space') }}</h4>
                    <p class="text-muted mb-0">{{ __('Apply for leave and check your balances') }}</p>
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

            {{-- Balance Cards --}}
            <div class="row g-3 mb-5 flex-nowrap" style="overflow-x: auto;">
                @forelse($balances as $balance)
                <div class="col">
                    <div class="balance-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 0.03em;">{{ $balance->leaveType->name }}</span>
                            <i class="bi bi-calendar-check text-success" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="fw-bold text-dark mb-1" style="font-size: 1.75rem; line-height: 1;">{{ $balance->remaining_days }}</div>
                        <div class="fw-bold text-muted" style="font-size: 0.72rem;">{{ __('Days Remaining') }}</div>
                        <div class="mt-2 pt-2 border-top">
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                <span>{{ __('Used:') }} <span class="fw-bold">{{ $balance->used_days }}</span></span>
                                <span>{{ __('Total:') }} <span class="fw-bold">{{ $balance->opening_balance }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm rounded-4">
                        <i class="bi bi-info-circle-fill me-2"></i>{{ __('You do not have any leave balances recorded yet.') }}
                    </div>
                </div>
                @endforelse
            </div>

            <div class="row g-4">
                {{-- Apply Leave Form --}}
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-journal-plus me-2 text-success"></i>{{ __('Apply for Leave') }}</h5>
                        <form action="{{ route('team-lead.leave.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Leave Type') }} <span class="text-danger">*</span></label>
                                <select name="leave_type_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('Select Type') }}</option>
                                    @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('From Date') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="from_date" id="from_date" class="form-control rounded-3" placeholder="Select date" readonly required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('To Date') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="to_date" id="to_date" class="form-control rounded-3" placeholder="Select date" readonly required>
                                </div>
                            </div>

                            <div id="leave_days_display" class="mb-3 d-none" 
                                 data-employee-id="{{ $employee->id }}">
                                <div class="alert alert-info py-2 px-3 rounded-pill d-flex align-items-center justify-content-between mb-0 shadow-sm border-0" style="background-color: #c8e6c9ff; color: #007a10;">
                                    <span class="small fw-bold text-success"><i class="bi bi-calendar-event me-2"></i>{{ __('Total Days') }}:</span>
                                    <span id="total_days_count" class="badge bg-success rounded-pill">0</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Reason') }} <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control rounded-3" rows="3" required placeholder="{{ __('Why are you applying for leave?') }}"></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Emergency Contact / Leave Address') }}</label>
                                <textarea name="leave_address" class="form-control rounded-3" rows="2" placeholder="{{ __('Optional...') }}"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-send-check me-2"></i>{{ __('Submit Application') }}
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Applications History --}}
                <div class="col-lg-8">
                    <div class="hr-panel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-success"></i>{{ __('My Applications History') }}</h5>
                            <form action="{{ route('team-lead.leave.index') }}" method="GET" class="d-flex gap-2">
                                <select name="month" class="form-select form-select-sm rounded-3">
                                    <option value="">{{ __('All Months') }}</option>
                                    @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                        {{ date('M', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                    @endfor
                                </select>
                                <select name="year" class="form-select form-select-sm rounded-3">
                                    <option value="">{{ __('All Years') }}</option>
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                    @endfor
                                </select>
                                <button type="submit" class="btn btn-sm btn-success rounded-3 px-3">{{ __('Filter') }}</button>
                                @if(request()->hasAny(['month', 'year']))
                                <a href="{{ route('team-lead.leave.index') }}" class="btn btn-sm btn-light rounded-3">{{ __('Clear') }}</a>
                                @endif
                            </form>
                        </div>
                        <div class="table-responsive">
                            <table class="table hr-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Leave Type') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Days') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $app)
                                    <tr>
                                        <td><span class="badge {{ match(true) { str_contains(strtolower($app->leaveType->name), 'casual') => 'bg-primary', str_contains(strtolower($app->leaveType->name), 'sick') => 'bg-danger', str_contains(strtolower($app->leaveType->name), 'earn') => 'bg-success', str_contains(strtolower($app->leaveType->name), 'emergency') => 'bg-warning text-dark', default => 'bg-info text-dark' } }} rounded-pill px-2">{{ $app->leaveType->name }}</span></td>
                                        <td>
                                            <div class="small fw-bold">{{ \Carbon\Carbon::parse($app->from_date)->format('d M') }} - {{ \Carbon\Carbon::parse($app->to_date)->format('d M Y') }}</div>
                                            <div class="small text-muted">Applied: {{ $app->created_at->format('d M') }}</div>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $app->total_days }}</span></td>
                                        <td><span class="d-inline-block text-truncate small" style="max-width:200px;" title="{{ $app->reason }}">{{ $app->reason }}</span></td>
                                        <td>
                                            @if($app->status === 'approved')
                                            <span class="badge bg-success-soft text-success"><i class="bi bi-check-circle me-1"></i>{{ __('Approved') }}</span>
                                            @elseif($app->status === 'rejected')
                                            <span class="badge bg-danger-soft text-danger"><i class="bi bi-x-circle me-1"></i>{{ __('Rejected') }}</span>
                                            @else
                                            <span class="badge bg-warning-soft text-warning"><i class="bi bi-hourglass-split me-1"></i>{{ __('Pending') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="bi bi-journal-x d-block fs-1 opacity-50 mb-3"></i>
                                            <span class="text-muted">{{ __('No leave history found.') }}</span>
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
        document.addEventListener('DOMContentLoaded', function() {
            const daysDisplay = document.getElementById('leave_days_display');
            const daysCount = document.getElementById('total_days_count');

            function calculateDays() {
                const fromDate = fromPicker.selectedDates[0];
                const toDate   = toPicker.selectedDates[0];

                if (fromDate && toDate && toDate >= fromDate) {
                    const employeeId = daysDisplay.dataset.employeeId;
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

            calculateDays();
        });
    </script>
    @endpush
</x-app-layout>