<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-employee-dashboard.css'])
    <style>
        .profile-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 32px;
        }

        .profile-header-avatar {
            width: 80px;
            height: 80px;
            background: #15803d;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            margin-right: 24px;
            box-shadow: 0 4px 12px rgba(21, 128, 61, 0.2);
        }

        .profile-header-info h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .profile-header-info .role-badge {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 10px;
            padding: 16px;
        }

        .info-box label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-box .value {
            font-size: 15px;
            font-weight: 600;
            color: #334155;
        }

        .divider {
            border-top: 1px dashed #e2e8f0;
            margin: 24px 0;
        }

        .status-badge-active {
            background: #dcfce7;
            color: #15803d;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-badge-inactive {
            background: #fee2e2;
            color: #dc2626;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }
    </style>
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
                        {{ strtoupper(substr($employee ? $employee->first_name : $user->name, 0, 1)) }}
                    </div>
                    <div class="profile-header-info">
                        <h1>{{ $employee ? $employee->first_name . ' ' . $employee->last_name : $user->name }}</h1>
                        <div class="role-badge">{{ $roleName }}</div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-box">
                        <label>{{ __('Email Address') }}</label>
                        <div class="value">{{ $user->email }}</div>
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
                        <div class="value">{{ $employee && $employee->reportingManager ? $employee->reportingManager->first_name . ' ' . $employee->reportingManager->last_name : 'N/A' }}</div>
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

                <div class="info-grid" style="grid-template-columns: 1fr;">
                    <div class="info-box">
                        <label>{{ __('Local Address') }}</label>
                        <div class="value">{{ $employee ? $employee->address : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>