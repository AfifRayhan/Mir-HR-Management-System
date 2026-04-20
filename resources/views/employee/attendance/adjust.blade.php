<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-employee-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    @php 
        $isTeamLeadRole = optional(auth()->user()->role)->name === 'Team Lead'; 
        $employeeRecord = \App\Models\Employee::where('user_id', auth()->id())->first();
        $isReportingManager = \App\Models\Employee::where('reporting_manager_id', $employeeRecord?->id ?? 0)->exists();
        $isTeamLeadLayout = $isTeamLeadRole || $isReportingManager;
    @endphp

    <div class="{{ $isTeamLeadLayout ? 'hr-layout' : 'emp-layout' }}">
        @if($isTeamLeadLayout)
            @include('partials.team-lead-sidebar')
        @else
            @include('partials.employee-sidebar')
        @endif

        <main class="{{ $isTeamLeadLayout ? 'hr-main' : 'emp-main' }}">
            <div class="row mb-4 align-items-center">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">{{ __('Request Attendance Adjustment') }}</h4>
                        <p class="text-muted mb-0 small">{{ __('Submit a request to adjust your absent record') }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="hr-panel">
                        <form action="{{ route('employee.attendance.store-adjustment') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label font-bold text-gray-700">{{ __('Date') }}</label>
                                <input type="text" id="adj_date" name="date" class="form-control rounded-3 @error('date') is-invalid @enderror" value="{{ old('date', $date) }}" readonly required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label font-bold text-gray-700">{{ __('In Time') }}</label>
                                    <input type="text" id="in_time" name="in_time" class="form-control rounded-3 @error('in_time') is-invalid @enderror" placeholder="Select in time" readonly required value="{{ old('in_time') }}">
                                    @error('in_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-bold text-gray-700">{{ __('Out Time') }}</label>
                                    <input type="text" id="out_time" name="out_time" class="form-control rounded-3 @error('out_time') is-invalid @enderror" placeholder="Select out time (optional)" value="{{ old('out_time') }}">
                                    @error('out_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-bold text-gray-700">{{ __('Reason') }} <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control rounded-3 @error('reason') is-invalid @enderror" rows="3" required placeholder="Provide a justification for the adjustment..." maxlength="50">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
                                <a href="{{ route('employee.attendance.index') }}" class="btn btn-light rounded-pill px-4">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-send me-2"></i>{{ __('Submit Request') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('#adj_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
            });

            flatpickr('#in_time', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                altInput: true,
                altFormat: 'h:i K',
                allowInput: false,
            });

            flatpickr('#out_time', {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                altInput: true,
                altFormat: 'h:i K',
                allowInput: false,
            });
        });
    </script>
    @endpush
</x-app-layout>
