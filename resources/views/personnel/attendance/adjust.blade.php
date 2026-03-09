<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manual Attendance Adjustment') }}
        </h2>
    </x-slot>

    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
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
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Select Employee') }}</label>
                                    <select name="employee_id" class="form-select select2" required>
                                        <option value="">{{ __('-- Choose Employee --') }}</option>
                                        @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">
                                            {{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Date') }}</label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('In Time') }}</label>
                                        <input type="datetime-local" name="in_time" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Out Time') }}</label>
                                        <input type="datetime-local" name="out_time" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('Reason') }}</label>
                                    <textarea name="reason" class="form-control" rows="3" required placeholder="e.g. Device failure, Field work, etc."></textarea>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <a href="{{ route('personnel.attendances.index') }}" class="btn btn-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
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
</x-app-layout>