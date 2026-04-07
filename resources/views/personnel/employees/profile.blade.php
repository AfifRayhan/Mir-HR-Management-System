<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-employee-dashboard.css'])
    @endpush

    @php 
        $isTeamLeadRole = optional(auth()->user()->role)->name === 'Team Lead';
        $isReportingManager = \App\Models\Employee::where('reporting_manager_id', $employee?->id ?? 0)->exists();
        $isTeamLeadLayout = $isTeamLeadRole || $isReportingManager;
    @endphp

    <div class="{{ $isTeamLeadLayout ? 'hr-layout' : 'emp-layout' }}">
        @if($isTeamLeadLayout)
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
                        <h1>{{ ($employee ? $employee->name : $user->name) }}</h1>
                        <div class="role-badge">{{ 'Employee ID: ' . ($employee ? $employee->employee_code : ($user->employee_id ?? 'N/A')) }}</div>
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
                        <label>{{ __('Spouse Name') }}</label>
                        <div class="value">{{ $employee && $employee->spouse_name ? $employee->spouse_name : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Gender') }}</label>
                        <div class="value">{{ $employee && $employee->gender ? $employee->gender : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Religion') }}</label>
                        <div class="value">{{ $employee && $employee->religion ? $employee->religion : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Marital Status') }}</label>
                        <div class="value">{{ $employee && $employee->marital_status ? $employee->marital_status : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('National ID (NID)') }}</label>
                        <div class="value">{{ $employee && $employee->national_id ? $employee->national_id : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('TIN') }}</label>
                        <div class="value">{{ $employee && $employee->tin ? $employee->tin : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Nationality') }}</label>
                        <div class="value">{{ $employee && $employee->nationality ? $employee->nationality : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Joining Date') }}</label>
                        <div class="value">{{ $employee ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Discontinuation Date') }}</label>
                        <div class="value">{{ $employee && $employee->discontinuation_date ? \Carbon\Carbon::parse($employee->discontinuation_date)->format('d M Y') : 'N/A' }}</div>
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
                    <div class="info-box">
                        <label>{{ __('Number of Children') }}</label>
                        <div class="value">{{ $employee ? ($employee->no_of_children ?? 0) : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Alternate Contact') }}</label>
                        <div class="value">{{ $employee && $employee->contact_no ? $employee->contact_no : 'N/A' }}</div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <label>{{ __('Office Time (Shift)') }}</label>
                        <div class="value">{{ $employee && $employee->officeTime ? $employee->officeTime->shift_name . ' (' . \Carbon\Carbon::parse($employee->officeTime->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($employee->officeTime->end_time)->format('H:i') . ')' : 'N/A' }}</div>
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

                <div class="divider"></div>
                <h3 class="mb-4 text-lg font-bold">{{ __('Emergency Contact') }}</h3>
                <div class="info-grid">
                    <div class="info-box">
                        <label>{{ __('Contact Name') }}</label>
                        <div class="value">{{ $employee && $employee->emergency_contact_name ? $employee->emergency_contact_name : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Relation') }}</label>
                        <div class="value">{{ $employee && $employee->emergency_contact_relation ? $employee->emergency_contact_relation : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Contact Number') }}</label>
                        <div class="value">{{ $employee && $employee->emergency_contact_no ? $employee->emergency_contact_no : 'N/A' }}</div>
                    </div>
                    <div class="info-box" style="grid-column: span 2;">
                        <label>{{ __('Contact Address') }}</label>
                        <div class="value">{{ $employee && $employee->emergency_contact_address ? $employee->emergency_contact_address : 'N/A' }}</div>
                    </div>
                </div>

                <div class="info-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                    <div class="info-box">
                        <label>{{ __('Present Address') }}</label>
                        <div class="value">{{ $employee && $employee->present_address ? $employee->present_address : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Permanent Address') }}</label>
                        <div class="value">{{ $employee && $employee->permanent_address ? $employee->permanent_address : 'N/A' }}</div>
                    </div>
                    <div class="info-box" style="grid-column: span 3;">
                        <label>{{ __('Office Address') }}</label>
                        <div class="value">{{ $employee && $employee->office ? $employee->office->address : 'N/A' }}</div>
                    </div>
                </div>
                @if($employee && $employee->discontinuation_reason)
                <div class="divider"></div>
                <div class="info-box">
                    <label>{{ __('Discontinuation Reason') }}</label>
                    <div class="value">{{ $employee->discontinuation_reason }}</div>
                </div>
                @endif
            </div>
        </main>
    </div>
</x-app-layout>