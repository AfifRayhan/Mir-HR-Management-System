<x-app-layout>
    @push('styles')
    <style>
        .profile-card {
            background: var(--ui-card-bg, #fff);
            border-radius: var(--ui-radius-lg);
            padding: 2.5rem;
            box-shadow: var(--ui-shadow);
            border: 1px solid rgba(226, 232, 240, 0.5);
            max-width: 1200px;
            margin: 0 auto;
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 1.75rem;
            margin-bottom: 2.5rem;
            padding-bottom: 2.5rem;
            border-bottom: 1px solid var(--ui-border);
        }
        .profile-header-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ui-primary) 0%, var(--ui-primary-dark) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            font-weight: 800;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .profile-header-info h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--ui-text);
            letter-spacing: -0.02em;
        }
        .role-badge {
            background: var(--ui-primary-soft);
            color: var(--ui-primary-dark);
            padding: 0.4rem 1.25rem;
            border-radius: 9999px;
            font-size: 0.95rem;
            font-weight: 700;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .section-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--ui-text);
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-title i {
            color: var(--ui-primary);
            font-size: 1.5rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .info-box {
            background: var(--ui-bg);
            padding: 1.25rem 1.5rem;
            border-radius: var(--ui-radius-md);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .info-box:hover {
            transform: translateY(-3px);
            box-shadow: var(--ui-shadow);
            border-color: var(--ui-primary-soft);
        }
        .info-box label {
            font-size: 0.8rem;
            color: var(--ui-text-light);
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .info-box .value {
            font-weight: 600;
            color: var(--ui-text);
            font-size: 1.05rem;
            word-break: break-word;
            line-height: 1.4;
        }
        .status-badge-active {
            background: var(--ui-success-soft);
            color: var(--ui-success);
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-block;
        }
        .status-badge-inactive {
            background: var(--ui-danger-soft);
            color: var(--ui-danger);
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-block;
        }
        .divider {
            height: 1px;
            background: var(--ui-border);
            margin: 3rem 0;
            opacity: 0.6;
        }
        .experience-item {
            background: var(--ui-bg);
            border: 1px solid var(--ui-border);
            border-left: 4px solid var(--ui-primary);
            border-radius: var(--ui-radius-md);
            padding: 1.75rem;
            margin-bottom: 1.25rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .experience-item:hover {
            transform: translateX(6px);
            box-shadow: var(--ui-shadow);
        }
        .ui-table-container {
            border: 1px solid var(--ui-border);
            border-radius: var(--ui-radius-md);
            overflow: hidden;
        }
        .ui-table th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: var(--ui-text-light);
            background: var(--ui-bg);
            padding: 1.25rem 1rem;
            border-bottom: 1px solid var(--ui-border);
            font-weight: 700;
        }
        .ui-table td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            color: var(--ui-text);
            font-weight: 500;
            border-bottom: 1px solid var(--ui-border);
        }
        .ui-table tbody tr:last-child td {
            border-bottom: none;
        }
        .ui-table tbody tr {
            transition: background-color 0.2s;
        }
        .ui-table tbody tr:hover {
            background-color: rgba(248, 250, 252, 0.8);
        }
    </style>
    @endpush

    @php 
        $isTeamLeadRole = optional(auth()->user()->role)->name === 'Team Lead';
        $isReportingManager = \App\Models\Employee::where('reporting_manager_id', $employee?->id ?? 0)->exists();
        $isTeamLeadLayout = $isTeamLeadRole || $isReportingManager;
    @endphp

    <div class="ui-layout {{ $isTeamLeadLayout ? 'ui-scope-lead' : 'ui-scope-emp' }}">
        @if($isTeamLeadLayout)
        @include('partials.team-lead-sidebar')
        @else
        @include('partials.employee-sidebar')
        @endif

        <main class="ui-main">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-header-avatar">
                        {{ strtoupper(substr($employee ? $employee->name : $user->name, 0, 1)) }}
                    </div>
                    <div class="profile-header-info">
                        <h1>{{ ($employee ? $employee->name : $user->name) }}</h1>
                        <div class="role-badge"><i class="bi bi-person-badge me-2"></i>{{ 'Employee ID: ' . ($employee ? $employee->employee_code : ($user->employee_id ?? 'N/A')) }}</div>
                    </div>
                </div>

                <div class="section-title">
                    <i class="bi bi-person-lines-fill"></i> {{ __('Basic Information') }}
                </div>
                
                <div class="info-grid">
                    <div class="info-box">
                        <label>{{ __('Account Email') }}</label>
                        <div class="value">{{ $user->email }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Corporate Email') }}</label>
                        <div class="value">{{ $employee && $employee->email ? $employee->email : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Personal Email') }}</label>
                        <div class="value">{{ $employee && $employee->personal_email ? $employee->personal_email : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Phone Number') }}</label>
                        <div class="value">{{ $employee && $employee->phone ? $employee->phone : 'N/A' }}</div>
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
                        <div class="value">{{ $employee && $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Discontinuation Date') }}</label>
                        <div class="value">{{ $employee && $employee->discontinuation_date ? \Carbon\Carbon::parse($employee->discontinuation_date)->format('d M Y') : 'N/A' }}</div>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="section-title">
                    <i class="bi bi-briefcase-fill"></i> {{ __('Employment Details') }}
                </div>

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
                    <div class="info-box">
                        <label>{{ __('Office Time (Shift)') }}</label>
                        <div class="value">{{ $employee && $employee->officeTime ? $employee->officeTime->shift_name . ' (' . \Carbon\Carbon::parse($employee->officeTime->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($employee->officeTime->end_time)->format('h:i A') . ')' : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Date of Birth') }}</label>
                        <div class="value">{{ $employee && $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-box">
                        <label>{{ __('Account Status') }}</label>
                        <div class="value">
                            <span class="{{ $user->status === 'active' ? 'status-badge-active' : 'status-badge-inactive' }}">
                                <i class="bi {{ $user->status === 'active' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i> {{ ucfirst($user->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>
                <div class="section-title">
                    <i class="bi bi-telephone-plus-fill"></i> {{ __('Emergency Contact') }}
                </div>
                
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
                    <div class="info-box" style="grid-column: 1 / -1;">
                        <label>{{ __('Contact Address') }}</label>
                        <div class="value">{{ $employee && $employee->emergency_contact_address ? $employee->emergency_contact_address : 'N/A' }}</div>
                    </div>
                </div>

                <div class="divider"></div>
                <div class="section-title">
                    <i class="bi bi-geo-alt-fill"></i> {{ __('Addresses') }}
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
                    <div class="info-box">
                        <label>{{ __('Office Address') }}</label>
                        <div class="value">{{ $employee && $employee->office ? $employee->office->address : 'N/A' }}</div>
                    </div>
                </div>
                
                @if($employee && $employee->discontinuation_reason)
                <div class="divider"></div>
                <div class="info-box border-danger">
                    <label class="text-danger">{{ __('Discontinuation Reason') }}</label>
                    <div class="value">{{ $employee->discontinuation_reason }}</div>
                </div>
                @endif

                @if($employee && $employee->experiences && $employee->experiences->count() > 0)
                <div class="divider"></div>
                <div class="section-title">
                    <i class="bi bi-award-fill"></i> {{ __('Work Experience') }}
                </div>
                <div class="experience-list mt-4">
                    @foreach($employee->experiences as $exp)
                    <div class="experience-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="text-lg font-bold mb-0 text-dark">{{ $exp->organization }}</h4>
                            <span class="badge bg-light text-dark border"><i class="bi bi-calendar3 me-1"></i> {{ $exp->date_from }} to {{ $exp->date_to }}</span>
                        </div>
                        <div class="text-md font-bold text-success mb-3">{{ $exp->designation }} @if($exp->department) <span class="text-muted fw-normal ms-2">| {{ $exp->department }}</span> @endif</div>
                        @if($exp->responsibilities)
                        <div class="mt-2 text-sm text-gray-600 bg-light p-3 rounded">
                            <strong class="d-block mb-1">{{ __('Responsibilities:') }}</strong>
                            <p class="mb-0 lh-base">{{ $exp->responsibilities }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                @if($employee && $employee->qualifications && $employee->qualifications->count() > 0)
                <div class="divider"></div>
                <div class="section-title">
                    <i class="bi bi-mortarboard-fill"></i> {{ __('Academic Qualifications') }}
                </div>
                <div class="ui-table-container mt-4">
                    <div class="table-responsive">
                        <table class="table ui-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Qualification') }}</th>
                                    <th>{{ __('Institution') }}</th>
                                    <th>{{ __('Level') }}</th>
                                    <th>{{ __('Major/Group') }}</th>
                                    <th>{{ __('Year') }}</th>
                                    <th>{{ __('Result') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employee->qualifications as $qual)
                                <tr>
                                    <td class="fw-bold">{{ $qual->qualification }}</td>
                                    <td>{{ $qual->institution ?? ($qual->board_university ?? 'N/A') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $qual->level ?? 'N/A' }}</span></td>
                                    <td>{{ $qual->group_major ?? 'N/A' }}</td>
                                    <td>{{ $qual->passing_year ?? 'N/A' }}</td>
                                    <td class="fw-bold text-primary">{{ $qual->result ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>
</x-app-layout>



