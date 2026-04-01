<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manual Attendance Adjustment') }}
        </h2>
    </x-slot>

    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-0">{{ __('Record Adjustment') }}</h4>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="hr-panel">
                            <form action="{{ route('personnel.attendances.store-adjustment') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="form-label font-bold text-gray-700">{{ __('Select Employee') }}</label>
                                    <select name="employee_id" class="form-select select2 rounded-3 @error('employee_id') is-invalid @enderror" required>
                                        <option value="">{{ __('-- Choose Employee --') }}</option>
                                        @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }} ({{ $emp->employee_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label font-bold text-gray-700">{{ __('Date') }}</label>
                                    <input type="text" id="adj_date" name="date" class="form-control rounded-3 @error('date') is-invalid @enderror" value="{{ old('date', date('Y-m-d')) }}" placeholder="Select date" readonly required>
                                    @error('date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label font-bold text-gray-700">{{ __('In Time') }}</label>
                                        <input type="text" id="in_time" name="in_time" class="form-control rounded-3" placeholder="Select in time" readonly required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label font-bold text-gray-700">{{ __('Out Time') }}</label>
                                        <input type="text" id="out_time" name="out_time" class="form-control rounded-3" placeholder="Select out time (optional)">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label font-bold text-gray-700">{{ __('Reason') }}</label>
                                    <textarea name="reason" class="form-control rounded-3 @error('reason') is-invalid @enderror" rows="3" required placeholder="e.g. Device failure, Field work, etc.">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
                                    <a href="{{ route('personnel.attendances.index') }}" class="btn btn-light rounded-pill px-4">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-save me-2"></i>{{ __('Save Adjustment') }}
                                    </button>
                                </div>
                            </form>
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