<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-holidays.css'])
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Weekly Holiday Configuration') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="ui-panel">
                        <div class="ui-panel-title d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calendar-week me-2"></i>{{ __('Configure Weekly Holidays') }}</span>
                            <div style="min-width: 250px;">
                                <form action="{{ route('settings.holidays.weekly.index') }}" method="GET" id="officeFilterForm">
                                    <select name="office_id" class="form-select form-select-sm rounded-pill shadow-none" onchange="document.getElementById('officeFilterForm').submit()">
                                        <option value="">{{ __('System Default') }}</option>
                                        @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ $officeId == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        </div>

                        <div class="ui-panel-subtitle mb-4">
                            @if($officeId)
                                {{ __('Configuring holidays for:') }} <strong>{{ $offices->find($officeId)->name }}</strong>. 
                            @else
                                {{ __('Configuring system-wide default holidays.') }}
                            @endif
                            <br>
                            {{ __('Please select the days of the week that should be considered as recurring weekly holidays.') }}
                        </div>

                        <form action="{{ route('settings.holidays.weekly.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="office_id" value="{{ $officeId }}">

                            <div class="row g-4">
                                @foreach($weeklyHolidays as $holiday)
                                <div class="col-md-4 col-sm-6">
                                    <div class="info-group d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="value">{{ __($holiday->day_name) }}</span>
                                        </div>
                                        <div class="form-check form-switch custom-switch">
                                            <input class="form-check-input" type="checkbox" name="holidays[]"
                                                value="{{ $holiday->day_name }}" id="day_{{ $holiday->id }}"
                                                {{ $holiday->is_holiday ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="mt-5 border-top pt-4 text-end">
                                <button type="submit" class="btn btn-primary px-5 shadow-sm py-2 rounded-pill">
                                    <i class="bi bi-save me-2"></i> {{ __('Save Configuration') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>



