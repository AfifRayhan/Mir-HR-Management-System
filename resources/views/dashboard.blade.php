<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $user = auth()->user();
        $roleName = optional($user->role)->name ?? 'Unassigned';
    @endphp

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div>
                            <h5 class="card-title mb-1">
                                {{ __('Welcome,') }} {{ $user->name }}
                            </h5>
                            <p class="card-text mb-1 text-muted small">
                                {{ $user->email }}
                            </p>
                            <p class="card-text mb-0 small">
                                {{ __('Employee ID:') }}
                                <strong>{{ $user->employee_id ?? 'N/A' }}</strong>
                            </p>
                        </div>
                        <div class="mt-3 mt-md-0 text-md-end">
                            <span class="badge bg-primary">
                                {{ $roleName }}
                            </span>
                            <span class="badge {{ $user->status === 'inactive' ? 'bg-secondary' : 'bg-success' }} ms-1">
                                {{ ucfirst($user->status ?? 'active') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">{{ __('Your role') }}</h6>
                        <p class="card-text mb-1">
                            {{ __('This account is configured as:') }}
                        </p>
                        <p class="card-text fw-semibold">
                            {{ $roleName }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">{{ __('Quick links') }}</h6>
                        <ul class="list-unstyled mb-0 small">
                            <li>{{ __('• View and update your profile (coming soon)') }}</li>
                            <li>{{ __('• Check attendance and leave (coming soon)') }}</li>
                            <li>{{ __('• Team / HR tools based on your role') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">{{ __('System status') }}</h6>
                        <p class="card-text small mb-1">
                            {{ __('You are successfully logged in to the HR management system.') }}
                        </p>
                        <p class="card-text small text-muted mb-0">
                            {{ __('Additional HR modules (employees, teams, approvals) will appear here as they are implemented.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
