<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-employee-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="emp-layout">
        @include('partials.employee-sidebar')

        <main class="emp-main">

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

            <!-- Balances Row -->
            <div class="row row-cols-5 g-2 mb-5 flex-nowrap" style="overflow-x: hidden;">
                @forelse($balances as $balance)
                <div class="col" style="flex: 0 0 20%; max-width: 20%; min-width: 20%;">
                    <div class="balance-card h-100 px-2 py-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-bold text-gray-500 text-truncate" style="font-size: 0.65rem; letter-spacing: 0.02em;" title="{{ $balance->leaveType->name }}">{{ $balance->leaveType->name }}</span>
                            <i class="bi bi-calendar-check text-success" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="fw-bold text-gray-800 mb-1" style="font-size: 1.75rem; line-height: 1;">{{ $balance->remaining_days }}</div>
                        <div class="fw-bold text-gray-500" style="font-size: 0.72rem;">{{ __('Days Remaining') }}</div>
                        <div class="mt-2 pt-2 border-top border-gray-200">
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
                <!-- Apply Leave Form -->
                <div class="col-lg-4">
                    <div class="emp-panel">
                        <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-journal-plus me-2 text-success"></i>{{ __('Apply for Leave') }}</h5>
                        <form action="{{ route('employee.leave.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Leave Type') }} <span class="text-danger">*</span></label>
                                    <select name="leave_type_id" id="leave_type_select" class="form-select rounded-3" required>
                                        <option value="">{{ __('Select Type') }}</option>
                                        @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}" data-past-days="{{ $type->allow_past_days }}">{{ $type->name }}</option>
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
                                <label class="form-label small fw-bold text-muted">{{ __('Reason / Remarks') }} <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control rounded-3" rows="3" required placeholder="{{ __('Why are you applying for leave?') }}"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Leave Address') }}</label>
                                <input type="text" name="leave_address" class="form-control rounded-3" placeholder="{{ __('Where will you be during this leave?') }}">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Supporting Documents') }}</label>
                                <input type="file" name="supporting_document" class="form-control rounded-3 shadow-sm" accept=".pdf,image/*,.doc,.docx">
                                <div class="form-text small" style="font-size: 0.75rem;">
                                    {{ __('Optional (PDF, JPG, PNG, DOC, DOCX). Highly recommended for Sick/Parental leave.') }}
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-send-check me-2"></i>{{ __('Submit Application') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- My Applications History -->
                <div class="col-lg-8">
                    <div class="emp-panel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-success"></i>{{ __('My Applications History') }}</h5>
                            <form action="{{ route('employee.leave.index') }}" method="GET" class="d-flex gap-2">
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
                                <button type="submit" class="btn btn-hr-search border-0">{{ __('Search') }}</button>
                                <a href="{{ route('employee.leave.index') }}" class="btn btn-hr-clear">{{ __('Clear') }}</a>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table emp-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Leave Type') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Days') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                        <th>{{ __('Address') }}</th>
                                        <th>{{ __('Doc') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Approved By') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $app)
                                    <tr>
                                        <td><span class="badge {{ match(true) { str_contains(strtolower($app->leaveType->name), 'casual') => 'bg-primary', str_contains(strtolower($app->leaveType->name), 'sick') => 'bg-danger', str_contains(strtolower($app->leaveType->name), 'earn') => 'bg-success', str_contains(strtolower($app->leaveType->name), 'emergency') => 'bg-warning text-dark', default => 'bg-info text-dark' } }} rounded-pill px-2">{{ $app->leaveType->name }}</span></td>
                                        <td>
                                            <div class="small fw-bold">{{ \Carbon\Carbon::parse($app->from_date)->format('d M') }} - {{ \Carbon\Carbon::parse($app->to_date)->format('d M Y') }}</div>
                                            <div class="small text-muted border-top pt-1 mt-1">Applied: {{ $app->created_at->format('d M') }}</div>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $app->total_days }}</span></td>
                                        <td>
                                            <span class="d-inline-block text-truncate small" style="max-width: 200px;" title="{{ $app->reason }}">{{ $app->reason }}</span>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate small text-muted" style="max-width: 150px;" title="{{ $app->leave_address }}">{{ $app->leave_address ?: '--' }}</span>
                                        </td>
                                        <td>
                                            @if($app->supporting_document)
                                            <a href="{{ asset('storage/' . $app->supporting_document) }}" target="_blank" class="badge bg-success text-white text-decoration-none">
                                                <i class="bi bi-file-earmark-medical me-1"></i>{{ __('Doc') }}
                                            </a>
                                            @else
                                            <span class="text-muted small">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($app->status === 'approved')
                                            <span class="badge bg-success-soft text-success"><i class="bi bi-check-circle me-1"></i>{{ __('Approved') }}</span>
                                            @elseif($app->status === 'rejected')
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-danger-soft text-danger"><i class="bi bi-x-circle me-1"></i>{{ __('Rejected') }}</span>
                                                @if($app->remarks)
                                                <button type="button" class="btn btn-link p-0 text-danger" data-bs-toggle="modal" data-bs-target="#viewRemarksModal{{ $app->id }}" title="{{ __('View Remarks') }}">
                                                    <i class="bi bi-info-circle"></i>
                                                </button>

                                                <!-- View Remarks Modal -->
                                                <div class="modal fade" id="viewRemarksModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                                        <div class="modal-content">
                                                            <div class="modal-header py-2">
                                                                <h6 class="modal-title">{{ __('Approval Remarks') }}</h6>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body py-3">
                                                                <p class="mb-0 small text-dark">{{ $app->remarks }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            @else
                                            <span class="badge bg-warning-soft text-warning"><i class="bi bi-hourglass-split me-1"></i>{{ __('Pending') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($app->approver)
                                            <div class="small fw-bold text-gray-800">{{ $app->approver->name ?: __('HR Admin') }}</div>
                                            <div class="small text-muted" style="font-size: 0.7rem;">{{ $app->approved_at?->format('d M, h:i A') }}</div>
                                            @else
                                            <span class="text-muted small">--</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="bi bi-journal-x d-block fs-1 text-gray-300 mb-3"></i>
                                            <span class="text-gray-500">{{ __('No leave history found.') }}</span>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($applications->hasPages())
                        <div class="mt-4 border-top pt-3">
                            {{ $applications->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                        @endif
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

            const leaveTypeSelect = document.getElementById('leave_type_select');
 
             function updateMinDate() {
                 const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                 if (selectedOption && selectedOption.value) {
                     const pastDays = parseInt(selectedOption.dataset.pastDays) || 0;
                     const minDate = new Date();
                     minDate.setDate(minDate.getDate() - pastDays);
                     
                     fromPicker.set('minDate', minDate);
                     toPicker.set('minDate', minDate);
                 } else {
                     // Default to today if no type selected
                     const today = new Date();
                     fromPicker.set('minDate', today);
                     toPicker.set('minDate', today);
                 }
                 
                 // Reset selections if they are now invalid
                 if (fromPicker.selectedDates[0] && fromPicker.selectedDates[0] < fromPicker.config.minDate) {
                     fromPicker.clear();
                 }
                 if (toPicker.selectedDates[0] && toPicker.selectedDates[0] < toPicker.config.minDate) {
                     toPicker.clear();
                 }
                 
                 calculateDays();
             }
 
             const fromPicker = flatpickr('#from_date', {
                 dateFormat: 'Y-m-d',
                 allowInput: false,
                 onChange: function() {
                     const fromDate = fromPicker.selectedDates[0];
                     const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                     const pastDays = selectedOption ? (parseInt(selectedOption.dataset.pastDays) || 0) : 0;
                     const minDate = new Date();
                     minDate.setDate(minDate.getDate() - pastDays);
 
                     // The end date should not be earlier than the start date or the calculated minDate
                     const effectiveMinDateForTo = fromDate && fromDate > minDate ? fromDate : minDate;
                     toPicker.set('minDate', effectiveMinDateForTo);
                     
                     calculateDays();
                 }
             });
 
             const toPicker = flatpickr('#to_date', {
                 dateFormat: 'Y-m-d',
                 allowInput: false,
                 onChange: calculateDays
             });
 
             leaveTypeSelect.addEventListener('change', updateMinDate);
 
             // Initialize with current selection
             updateMinDate();
        });
    </script>
    @endpush
</x-app-layout>