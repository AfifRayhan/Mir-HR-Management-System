<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-holidays.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .rt-error { display: none; font-size: 0.75rem; color: #dc3545; margin-top: 0.25rem; }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">{{ __('Office Time (Shift) Management') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Add New Shift Panel -->
                <div class="col-lg-4">
                    <div class="ui-panel">
                        <div class="ui-panel-title mb-4">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>{{ __('Add New Shift') }}
                        </div>

                        <form action="{{ route('settings.office-times.store') }}" method="POST" class="shift-form" id="createShiftForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-8">
                                    <label class="form-label small fw-bold text-muted">{{ __('Shift Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="shift_name" class="form-control rounded-3" placeholder="{{ __('e.g. Regular Day Shift') }}" value="{{ old('shift_name') }}" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-muted">{{ __('Short Name') }}</label>
                                    <input type="text" name="short_name" class="form-control rounded-3" placeholder="{{ __('e.g. GS') }}" value="{{ old('short_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Start Time') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="start_time" class="form-control rounded-3 time-picker start-time" value="{{ old('start_time') }}" placeholder="09:00" required>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label small fw-bold text-muted">{{ __('End Time') }} <span class="text-danger">*</span></label>
                                        <span class="badge bg-soft-warning text-warning rounded-pill px-2 py-1 mb-2 overnight-badge d-none" style="font-size: 0.65rem; border: 1px solid currentColor;">
                                            <i class="bi bi-moon-stars me-1"></i>{{ __('Overnight') }}
                                        </span>
                                    </div>
                                    <input type="text" name="end_time" class="form-control rounded-3 time-picker end-time" value="{{ old('end_time') }}" placeholder="17:00" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Late After') }}</label>
                                    <input type="text" name="late_after" class="form-control rounded-3 time-picker late-after" value="{{ old('late_after') }}" placeholder="Optional">
                                    <div class="rt-error error-late-after"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Absent After') }}</label>
                                    <input type="text" name="absent_after" class="form-control rounded-3 time-picker absent-after" value="{{ old('absent_after') }}" placeholder="Optional">
                                    <div class="rt-error error-absent-after"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Lunch Start') }}</label>
                                    <input type="text" name="lunch_start" class="form-control rounded-3 time-picker lunch-start" value="{{ old('lunch_start') }}" placeholder="Optional">
                                    <div class="rt-error error-lunch-start"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Lunch End') }}</label>
                                    <input type="text" name="lunch_end" class="form-control rounded-3 time-picker lunch-end" value="{{ old('lunch_end') }}" placeholder="Optional">
                                    <div class="rt-error error-lunch-end"></div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">{{ __('Remarks') }}</label>
                                    <textarea name="remarks" class="form-control rounded-3" rows="2" maxlength="100" placeholder="Max 100 characters">{{ old('remarks') }}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm mt-4">
                                <i class="bi bi-save me-2"></i> {{ __('Create Shift') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Shift Schedule List -->
                <div class="col-lg-8">
                    <div class="ui-panel p-0 overflow-hidden">
                        <div class="ui-panel-title p-4 border-bottom">
                            <i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Shift Schedule List') }}
                        </div>

                        <div class="table-responsive">
                            <table class="table ui-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">{{ __('Shift Name') }}</th>
                                        <th>{{ __('Shift Timing') }}</th>
                                        <th>{{ __('Thresholds') }}</th>
                                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($officeTimes as $time)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="fw-bold text-primary">{{ $time->shift_name }}</div>
                                                @if($time->short_name)
                                                    <span class="badge bg-primary-soft text-primary rounded-pill border border-primary-subtle" style="font-size: 0.7rem;">{{ $time->short_name }}</span>
                                                @endif
                                            </div>
                                            @if($time->remarks)
                                                <div class="small text-muted text-truncate" style="max-width: 150px;">{{ $time->remarks }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-success-soft text-success rounded-pill px-2 py-1 border border-success-subtle small">
                                                    {{ \Carbon\Carbon::parse($time->start_time)->format('h:i A') }}
                                                </span>
                                                <i class="bi bi-arrow-right text-muted small"></i>
                                                <span class="badge bg-secondary-soft text-secondary rounded-pill px-2 py-1 border border-secondary-subtle small">
                                                    {{ \Carbon\Carbon::parse($time->end_time)->format('h:i A') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <div class="small text-warning">
                                                    <i class="bi bi-clock me-1"></i>Late: {{ $time->late_after ? \Carbon\Carbon::parse($time->late_after)->format('h:i A') : '--' }}
                                                </div>
                                                <div class="small text-danger">
                                                    <i class="bi bi-person-x me-1"></i>Abs: {{ $time->absent_after ? \Carbon\Carbon::parse($time->absent_after)->format('h:i A') : '--' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editShiftModal{{ $time->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('settings.office-times.destroy', $time) }}" method="POST" data-confirm data-confirm-message="{{ __('Are you sure you want to delete this shift?') }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editShiftModal{{ $time->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                    <h5 class="modal-title fw-bold text-primary">{{ __('Edit Shift Schedule') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('settings.office-times.update', $time) }}" method="POST" class="shift-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="row g-3">
                                                            <div class="col-8">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Shift Name') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="shift_name" class="form-control rounded-3" value="{{ old('shift_name', $time->shift_name) }}" required>
                                                            </div>
                                                            <div class="col-4">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Short Name') }}</label>
                                                                <input type="text" name="short_name" class="form-control rounded-3" value="{{ old('short_name', $time->short_name) }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Start Time') }} <span class="text-danger">*</span></label>
                                                                <input type="text" name="start_time" class="form-control rounded-3 time-picker start-time" value="{{ old('start_time', $time->start_time) }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <label class="form-label small fw-bold text-muted">{{ __('End Time') }} <span class="text-danger">*</span></label>
                                                                    <span class="badge bg-soft-warning text-warning rounded-pill px-2 py-1 mb-2 overnight-badge d-none" style="font-size: 0.65rem; border: 1px solid currentColor;">
                                                                        <i class="bi bi-moon-stars me-1"></i>{{ __('Overnight') }}
                                                                    </span>
                                                                </div>
                                                                <input type="text" name="end_time" class="form-control rounded-3 time-picker end-time" value="{{ old('end_time', $time->end_time) }}" required>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Late After') }}</label>
                                                                <input type="text" name="late_after" class="form-control rounded-3 time-picker late-after" value="{{ old('late_after', $time->late_after) }}">
                                                                <div class="rt-error error-late-after"></div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Absent After') }}</label>
                                                                <input type="text" name="absent_after" class="form-control rounded-3 time-picker absent-after" value="{{ old('absent_after', $time->absent_after) }}">
                                                                <div class="rt-error error-absent-after"></div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Lunch Start') }}</label>
                                                                <input type="text" name="lunch_start" class="form-control rounded-3 time-picker lunch-start" value="{{ old('lunch_start', $time->lunch_start) }}">
                                                                <div class="rt-error error-lunch-start"></div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Lunch End') }}</label>
                                                                <input type="text" name="lunch_end" class="form-control rounded-3 time-picker lunch-end" value="{{ old('lunch_end', $time->lunch_end) }}">
                                                                <div class="rt-error error-lunch-end"></div>
                                                            </div>

                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Remarks') }}</label>
                                                                <textarea name="remarks" class="form-control rounded-3" rows="2" maxlength="100">{{ old('remarks', $time->remarks) }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-4 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Update Shift') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-clock-history d-block mb-3 fs-1 opacity-50"></i>
                                                {{ __('No shift schedules found.') }}
                                            </div>
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
            const timeCfg = {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                altInput: true,
                altFormat: 'h:i K',
                allowInput: false,
                onClose: function(selectedDates, dateStr, instance) {
                    instance.element.dispatchEvent(new Event('input', { bubbles: true }));
                }
            };

            flatpickr('.time-picker', timeCfg);

            function validateForm(form) {
                const startInput = form.querySelector('.start-time');
                const endInput   = form.querySelector('.end-time');
                const lateInput  = form.querySelector('.late-after');
                const absInput   = form.querySelector('.absent-after');
                const lStartInput= form.querySelector('.lunch-start');
                const lEndInput  = form.querySelector('.lunch-end');
                const badge      = form.querySelector('.overnight-badge');

                const start = startInput.value;
                const end   = endInput.value;

                if (!start || !end) {
                    badge?.classList.add('d-none');
                    return true;
                }

                const isOvernight = end < start;
                isOvernight ? badge?.classList.remove('d-none') : badge?.classList.add('d-none');

                let isValid = true;

                const checkTime = (input, errorClass, label) => {
                    const val = input.value;
                    const errorDiv = form.querySelector('.' + errorClass);
                    if (!val) {
                        input.classList.remove('is-invalid');
                        if (errorDiv) errorDiv.style.display = 'none';
                        return true;
                    }
                    
                    const inRange = isOvernight ? (val >= start || val <= end) : (val >= start && val <= end);
                    if (!inRange) {
                        input.classList.add('is-invalid');
                        if (errorDiv) { errorDiv.textContent = 'Must be within shift.'; errorDiv.style.display = 'block'; }
                        return false;
                    }
                    input.classList.remove('is-invalid');
                    if (errorDiv) errorDiv.style.display = 'none';
                    return true;
                };

                if (!checkTime(lateInput, 'error-late-after')) isValid = false;
                if (!checkTime(absInput, 'error-absent-after')) isValid = false;
                if (!checkTime(lStartInput, 'error-lunch-start')) isValid = false;
                if (!checkTime(lEndInput, 'error-lunch-end', 'Lunch End')) isValid = false;

                // Threshold cross-validation
                if (isValid && lateInput.value && absInput.value) {
                    const lateVal = lateInput.value;
                    const absVal  = absInput.value;
                    const lO = lateVal < start;
                    const aO = absVal < start;
                    if ((lO ? '1' : '0') + lateVal > (aO ? '1' : '0') + absVal) {
                        lateInput.classList.add('is-invalid');
                        const err = form.querySelector('.error-late-after');
                        if (err) { err.textContent = 'Cannot be after Absent threshold.'; err.style.display = 'block'; }
                        isValid = false;
                    }
                }

                // Lunch cross-validation
                if (isValid && lStartInput.value && lEndInput.value) {
                    const sV = lStartInput.value;
                    const eV = lEndInput.value;
                    const sO = sV < start;
                    const eO = eV < start;
                    if ((sO ? '1' : '0') + sV >= (eO ? '1' : '0') + eV) {
                        lEndInput.classList.add('is-invalid');
                        const err = form.querySelector('.error-lunch-end');
                        if (err) { err.textContent = 'Must be after Lunch Start.'; err.style.display = 'block'; }
                        isValid = false;
                    }
                }

                return isValid;
            }

            document.querySelectorAll('.shift-form').forEach(form => {
                form.addEventListener('input', () => validateForm(form));
                form.addEventListener('submit', (e) => {
                    if (!validateForm(form)) e.preventDefault();
                });
                // Initial validation to show badges/errors
                validateForm(form);
            });
        });
    </script>
    @endpush
</x-app-layout>



