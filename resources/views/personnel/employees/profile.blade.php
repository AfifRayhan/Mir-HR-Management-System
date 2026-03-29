<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-employee-dashboard.css'])
    @endpush

    <div class="emp-layout">
        @if(optional(auth()->user()->role)->name === 'Team Lead')
        @include('partials.team-lead-sidebar')
        @else
        @include('partials.employee-sidebar')
        @endif

        <main class="emp-main">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-header-avatar">
                        {{ strtoupper(substr($employee ? $employee->name : $user->name, 0, 1)) }}
                    </div>
                    <div class="profile-header-info">
                        <h1>{{ $employee ? $employee->name : $user->name }}</h1>
                        <div class="role-badge">{{ $roleName }}</div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <label>{{ __('Account Email') }}</label>
                        <div class="value">{{ $user->email }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Personal Email') }}</label>
                        <div class="value">{{ $employee ? $employee->email : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Phone Number') }}</label>
                        <div class="value">{{ $employee ? $employee->phone : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Department') }}</label>
                        <div class="value">{{ $employee && $employee->department ? $employee->department->name : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Blood Group') }}</label>
                        <div class="value">{{ $employee && $employee->blood_group ? $employee->blood_group : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Father\'s Name') }}</label>
                        <div class="value">{{ $employee && $employee->father_name ? $employee->father_name : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Mother\'s Name') }}</label>
                        <div class="value">{{ $employee && $employee->mother_name ? $employee->mother_name : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Joining Date') }}</label>
                        <div class="value">{{ $employee ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : 'N/A' }}</div>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="info-grid">
                    <div class="info-box">
                        <label>{{ __('Section') }}</label>
                        <div class="value">{{ $employee && $employee->section ? $employee->section->name : 'N/A' }}</div>
                    </div>
                    @if(optional(auth()->user()->role)->name === 'HR Admin' || auth()->id() === $user->id)
                    <div class="info-box">
                        <label>{{ __('Gross Salary') }}</label>
                        <div class="value">
                            @if($employee && $employee->gross_salary)
                                {{ number_format($employee->gross_salary, 2) }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="info-box">
                        <label>{{ __('Designation') }}</label>
                        <div class="value">{{ $employee && $employee->designation ? $employee->designation->name : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Grade') }}</label>
                        <div class="value">{{ $employee && $employee->grade ? $employee->grade->name : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Reporting Manager') }}</label>
                        <div class="value">{{ $employee && $employee->reportingManager ? $employee->reportingManager->name : 'N/A' }}</div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <label>{{ __('Office Time (Shift)') }}</label>
                        <div class="value">{{ $employee && $employee->officeTime ? $employee->officeTime->shift_name . ' (' . \Carbon\Carbon::parse($employee->officeTime->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($employee->officeTime->end_time)->format('H:i') . ')' : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Employee ID') }}</label>
                        <div class="value">{{ $employee ? $employee->employee_code : ($user->employee_id ?? 'N/A') }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Date of Birth') }}</label>
                        <div class="value">{{ $employee && $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Account Status') }}</label>
                        <div class="value">
                            <span class="{{ $user->status === 'active' ? 'status-badge-active' : 'status-badge-inactive' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="info-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="info-box">
                        <label>{{ __('Local Address') }}</label>
                        <div class="value">{{ $employee ? $employee->address : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Office Address') }}</label>
                        <div class="value">{{ $employee && $employee->office ? $employee->office->address : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>