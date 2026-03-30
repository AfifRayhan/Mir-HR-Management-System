<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-holidays.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Edit Office Time (Shift)') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>{{ __('Update Shift Schedule') }}
                        </div>

                        <form action="{{ route('settings.office-times.update', $officeTime) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">{{ __('Shift Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="shift_name" class="form-control rounded-3" value="{{ $officeTime->shift_name }}" placeholder="{{ __('e.g. Regular Day Shift') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Start Time') }} <span class="text-danger">*</span></label>
                                    <input type="time" id="start_time" name="start_time" class="form-control rounded-3 @error('start_time') is-invalid @enderror" value="{{ old('start_time', substr($officeTime->start_time, 0, 5)) }}" required>
                                    <div class="invalid-feedback text-xs rt-error" id="error_start_time"></div>
                                    @error('start_time')
                                    <div class="invalid-feedback text-xs">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label small fw-bold text-muted">{{ __('End Time') }} <span class="text-danger">*</span></label>
                                        <span id="overnight_badge" class="badge bg-soft-warning text-warning rounded-pill px-2 py-1 mb-2 d-none" style="font-size: 0.65rem; border: 1px solid currentColor;">
                                            <i class="bi bi-moon-stars me-1"></i>{{ __('Overnight') }}
                                        </span>
                                    </div>
                                    <input type="time" id="end_time" name="end_time" class="form-control rounded-3 @error('end_time') is-invalid @enderror" value="{{ old('end_time', substr($officeTime->end_time, 0, 5)) }}" required>
                                    <div class="invalid-feedback text-xs rt-error" id="error_end_time"></div>
                                    @error('end_time')
                                    <div class="invalid-feedback text-xs">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Late After') }}</label>
                                    <input type="time" id="late_after" name="late_after" class="form-control rounded-3 @error('late_after') is-invalid @enderror" value="{{ old('late_after', $officeTime->late_after ? substr($officeTime->late_after, 0, 5) : '') }}">
                                    <div class="invalid-feedback text-xs rt-error" id="error_late_after"></div>
                                    @error('late_after')
                                    <div class="invalid-feedback text-xs">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Absent After') }}</label>
                                    <input type="time" id="absent_after" name="absent_after" class="form-control rounded-3 @error('absent_after') is-invalid @enderror" value="{{ old('absent_after', $officeTime->absent_after ? substr($officeTime->absent_after, 0, 5) : '') }}">
                                    <div class="invalid-feedback text-xs rt-error" id="error_absent_after"></div>
                                    @error('absent_after')
                                    <div class="invalid-feedback text-xs">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Lunch Start') }}</label>
                                    <input type="time" id="lunch_start" name="lunch_start" class="form-control rounded-3 @error('lunch_start') is-invalid @enderror" value="{{ old('lunch_start', $officeTime->lunch_start ? substr($officeTime->lunch_start, 0, 5) : '') }}">
                                    <div class="invalid-feedback text-xs rt-error" id="error_lunch_start"></div>
                                    @error('lunch_start')
                                    <div class="invalid-feedback text-xs">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Lunch End') }}</label>
                                    <input type="time" id="lunch_end" name="lunch_end" class="form-control rounded-3 @error('lunch_end') is-invalid @enderror" value="{{ old('lunch_end', $officeTime->lunch_end ? substr($officeTime->lunch_end, 0, 5) : '') }}">
                                    <div class="invalid-feedback text-xs rt-error" id="error_lunch_end"></div>
                                    @error('lunch_end')
                                    <div class="invalid-feedback text-xs">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">{{ __('Remarks') }}</label>
                                    <textarea name="remarks" class="form-control rounded-3" rows="2" maxlength="100" placeholder="{{ __('Optional remarks (max 100 characters)') }}">{{ old('remarks', $officeTime->remarks) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-5 border-top pt-4 d-flex justify-content-between">
                                <a href="{{ route('settings.office-times.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                                    <i class="bi bi-arrow-left me-1"></i> {{ __('Back to List') }}
                                </a>
                                <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                                    <i class="bi bi-save me-2"></i> {{ __('Update Shift') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            const lateAfterInput = document.getElementById('late_after');
            const absentAfterInput = document.getElementById('absent_after');
            const lunchStartInput = document.getElementById('lunch_start');
            const lunchEndInput = document.getElementById('lunch_end');
            const overnightBadge = document.getElementById('overnight_badge');
            
            const inputs = [startTimeInput, endTimeInput, lateAfterInput, absentAfterInput, lunchStartInput, lunchEndInput];
            
            function validateField(input, condition, message) {
                const errorDiv = document.getElementById('error_' + input.id);
                if (input.value && !condition) {
                    input.classList.add('is-invalid');
                    if (errorDiv) {
                        errorDiv.textContent = message;
                        errorDiv.style.display = 'block';
                    }
                    return false;
                } else {
                    input.classList.remove('is-invalid');
                    if (errorDiv) {
                        errorDiv.textContent = '';
                        errorDiv.style.display = 'none';
                    }
                    return true;
                }
            }

            function validateAll() {
                const start = startTimeInput.value;
                const end = endTimeInput.value;
                
                if (!start || !end) {
                    overnightBadge.classList.add('d-none');
                    return true;
                }

                const isOvernight = end < start;
                
                if (isOvernight) {
                    overnightBadge.classList.remove('d-none');
                } else {
                    overnightBadge.classList.add('d-none');
                }

                let allValid = true;

                // Validate Late After
                if (lateAfterInput.value) {
                    const lateVal = lateAfterInput.value;
                    const isValid = isOvernight ? (lateVal >= start || lateVal <= end) : (lateVal >= start && lateVal <= end);
                    if (!validateField(lateAfterInput, isValid, "Must be within shift duration.")) allValid = false;
                }

                // Validate Absent After
                if (absentAfterInput.value) {
                    const absVal = absentAfterInput.value;
                    const isValid = isOvernight ? (absVal >= start || absVal <= end) : (absVal >= start && absVal <= end);
                    if (!validateField(absentAfterInput, isValid, "Must be within shift duration.")) allValid = false;
                    
                    // Specific check: Late After cannot be more than Absent After
                    if (allValid && lateAfterInput.value) {
                        const lateVal = lateAfterInput.value;
                        const isLateOvernight = lateVal < start;
                        const isAbsOvernight = absVal < start;
                        const lateRel = isLateOvernight ? '1' + lateVal : '0' + lateVal;
                        const absRel = isAbsOvernight ? '1' + absVal : '0' + absVal;

                        if (lateRel > absRel) {
                            validateField(lateAfterInput, false, "Cannot be more than Absent After.");
                            allValid = false;
                        }
                    }
                }

                // Validate Lunch Start
                if (lunchStartInput.value) {
                    const lStartVal = lunchStartInput.value;
                    const isValid = isOvernight ? (lStartVal >= start || lStartVal <= end) : (lStartVal >= start && lStartVal <= end);
                    if (!validateField(lunchStartInput, isValid, "Must be within shift duration.")) allValid = false;
                }

                // Validate Lunch End
                if (lunchEndInput.value) {
                    const lEndVal = lunchEndInput.value;
                    const isValid = isOvernight ? (lEndVal >= start || lEndVal <= end) : (lEndVal >= start && lEndVal <= end);
                    if (!validateField(lunchEndInput, isValid, "Must be within shift duration.")) allValid = false;

                    // Specific check: Lunch End must always be after Lunch Start
                    if (allValid && lunchStartInput.value) {
                        const lStartVal = lunchStartInput.value;
                        const isStOvernight = lStartVal < start;
                        const isEnOvernight = lEndVal < start;
                        const stRel = isStOvernight ? '1' + lStartVal : '0' + lStartVal;
                        const enRel = isEnOvernight ? '1' + lEndVal : '0' + lEndVal;

                        if (stRel >= enRel) {
                            validateField(lunchEndInput, false, "Must be after Lunch Start.");
                            allValid = false;
                        }
                    }
                }

                return allValid;
            }

            inputs.forEach(input => {
                input.addEventListener('input', validateAll);
            });

            document.querySelector('form').addEventListener('submit', function(e) {
                if (!validateAll()) {
                    e.preventDefault();
                }
            });
            
            // Initial validation
            validateAll();
        });
    </script>
    @endpush
</x-app-layout>