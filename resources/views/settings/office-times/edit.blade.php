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
                                    <input type="time" name="start_time" class="form-control rounded-3" value="{{ substr($officeTime->start_time, 0, 5) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('End Time') }} <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" class="form-control rounded-3" value="{{ substr($officeTime->end_time, 0, 5) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Late After') }}</label>
                                    <input type="time" name="late_after" class="form-control rounded-3" value="{{ $officeTime->late_after ? substr($officeTime->late_after, 0, 5) : '' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Absent After') }}</label>
                                    <input type="time" name="absent_after" class="form-control rounded-3" value="{{ $officeTime->absent_after ? substr($officeTime->absent_after, 0, 5) : '' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Lunch Start') }}</label>
                                    <input type="time" name="lunch_start" class="form-control rounded-3" value="{{ $officeTime->lunch_start ? substr($officeTime->lunch_start, 0, 5) : '' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Lunch End') }}</label>
                                    <input type="time" name="lunch_end" class="form-control rounded-3" value="{{ $officeTime->lunch_end ? substr($officeTime->lunch_end, 0, 5) : '' }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted">{{ __('Remarks') }}</label>
                                    <textarea name="remarks" class="form-control rounded-3" rows="2" maxlength="100" placeholder="{{ __('Optional remarks (max 100 characters)') }}">{{ $officeTime->remarks }}</textarea>
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
</x-app-layout>