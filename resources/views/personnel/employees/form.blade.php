<x-app-layout>
    @push('styles')
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="row mb-5">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ isset($employee) ? __('Edit Employee') : __('Add New Employee') }}</h5>
                        <p class="mb-0 text-gray-500">{{ isset($employee) ? __('Update employee profile and associations') : __('Create a new employee profile in the system') }}</p>
                    </div>
                    <a href="{{ route('personnel.employees.index') }}" class="btn btn-outline-secondary rounded-pill d-flex align-items-center">
                        <i class="bi bi-arrow-left me-2"></i>{{ __('Back to List') }}
                    </a>
                </div>
            </div>

            <form id="employee-form" action="{{ isset($employee) ? route('personnel.employees.update', $employee->id) : route('personnel.employees.store') }}" method="POST">
                @csrf
                @if(isset($employee))
                @method('PUT')
                @endif

                <div class="form-card mb-5">
                    <!-- Personal Information -->
                    <div class="form-section-title">
                        <i class="bi bi-person-badge"></i>{{ __('Personal Information') }}
                    </div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Employee Code') }} <span class="text-danger">*</span></label>
                            <input type="text" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror" value="{{ old('employee_code', $employee->employee_code ?? $autoEmployeeCode ?? '') }}" required placeholder="e.g. EMP001">
                            @error('employee_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name ?? '') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Corporate Email') }}</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email ?? '') }}" placeholder="corporate@example.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Personal Email') }}</label>
                            <input type="email" name="personal_email" class="form-control @error('personal_email') is-invalid @enderror" value="{{ old('personal_email', $employee->personal_email ?? '') }}" placeholder="personal@example.com">
                            @error('personal_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Father Name') }}</label>
                            <input type="text" name="father_name" class="form-control @error('father_name') is-invalid @enderror" value="{{ old('father_name', $employee->father_name ?? '') }}">
                            @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Mother Name') }}</label>
                            <input type="text" name="mother_name" class="form-control @error('mother_name') is-invalid @enderror" value="{{ old('mother_name', $employee->mother_name ?? '') }}">
                            @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Spouse Name') }}</label>
                            <input type="text" name="spouse_name" class="form-control @error('spouse_name') is-invalid @enderror" value="{{ old('spouse_name', $employee->spouse_name ?? '') }}">
                            @error('spouse_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Gender') }}</label>
                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                <option value="">{{ __('Select Gender') }}</option>
                                <option value="Male" {{ old('gender', $employee->gender ?? '') == 'Male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                <option value="Female" {{ old('gender', $employee->gender ?? '') == 'Female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                <option value="Other" {{ old('gender', $employee->gender ?? '') == 'Other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                            </select>
                            @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Religion') }}</label>
                            <input type="text" name="religion" class="form-control @error('religion') is-invalid @enderror" value="{{ old('religion', $employee->religion ?? '') }}">
                            @error('religion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Marital Status') }}</label>
                            <select name="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="Single" {{ old('marital_status', $employee->marital_status ?? '') == 'Single' ? 'selected' : '' }}>{{ __('Single') }}</option>
                                <option value="Married" {{ old('marital_status', $employee->marital_status ?? '') == 'Married' ? 'selected' : '' }}>{{ __('Married') }}</option>
                                <option value="Divorced" {{ old('marital_status', $employee->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>{{ __('Divorced') }}</option>
                                <option value="Widowed" {{ old('marital_status', $employee->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>{{ __('Widowed') }}</option>
                            </select>
                            @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('National ID (NID)') }}</label>
                            <input type="text" name="national_id" class="form-control @error('national_id') is-invalid @enderror" value="{{ old('national_id', $employee->national_id ?? '') }}">
                            @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('TIN') }}</label>
                            <input type="text" name="tin" class="form-control @error('tin') is-invalid @enderror" value="{{ old('tin', $employee->tin ?? '') }}">
                            @error('tin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Nationality') }}</label>
                            <input type="text" name="nationality" class="form-control @error('nationality') is-invalid @enderror" value="{{ old('nationality', $employee->nationality ?? 'Bangladeshi') }}">
                            @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Number of Children') }}</label>
                            <input type="number" name="no_of_children" class="form-control @error('no_of_children') is-invalid @enderror" value="{{ old('no_of_children', $employee->no_of_children ?? '') }}" min="0">
                            @error('no_of_children') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Blood Group') }}</label>
                            <select name="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                <option value="">{{ __('Select Group') }}</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group', $employee->blood_group ?? '') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                @endforeach
                            </select>
                            @error('blood_group') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Phone Number') }}</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone ?? '') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Contact Number (Alternate)') }}</label>
                            <input type="text" name="contact_no" class="form-control @error('contact_no') is-invalid @enderror" value="{{ old('contact_no', $employee->contact_no ?? '') }}">
                            @error('contact_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Date of Birth') }}</label>
                            <input type="text" id="date_of_birth" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}" placeholder="Select date of birth">
                            @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Joining Date') }} <span class="text-danger">*</span></label>
                            <input type="text" id="joining_date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', $employee->joining_date ?? '') }}" placeholder="Select joining date" required>
                            @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Discontinuation Date') }}</label>
                            <input type="text" id="discontinuation_date" name="discontinuation_date" class="form-control @error('discontinuation_date') is-invalid @enderror" value="{{ old('discontinuation_date', $employee->discontinuation_date ?? '') }}" placeholder="Select discontinuation date">
                            @error('discontinuation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Discontinuation Reason') }}</label>
                            <textarea name="discontinuation_reason" class="form-control @error('discontinuation_reason') is-invalid @enderror" rows="2">{{ old('discontinuation_reason', $employee->discontinuation_reason ?? '') }}</textarea>
                            @error('discontinuation_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Present Address') }}</label>
                            <textarea name="present_address" class="form-control @error('present_address') is-invalid @enderror" rows="2">{{ old('present_address', $employee->present_address ?? '') }}</textarea>
                            @error('present_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Permanent Address') }}</label>
                            <textarea name="permanent_address" class="form-control @error('permanent_address') is-invalid @enderror" rows="2">{{ old('permanent_address', $employee->permanent_address ?? '') }}</textarea>
                            @error('permanent_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Emergency Contact Information -->
                    <div class="form-section-title">
                        <i class="bi bi-telephone-outbound"></i>{{ __('Emergency Contact Information') }}
                    </div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Contact Name') }}</label>
                            <input type="text" name="emergency_contact_name" class="form-control @error('emergency_contact_name') is-invalid @enderror" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}">
                            @error('emergency_contact_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Relation') }}</label>
                            <input type="text" name="emergency_contact_relation" class="form-control @error('emergency_contact_relation') is-invalid @enderror" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation ?? '') }}">
                            @error('emergency_contact_relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Contact Number') }}</label>
                            <input type="text" name="emergency_contact_no" class="form-control @error('emergency_contact_no') is-invalid @enderror" value="{{ old('emergency_contact_no', $employee->emergency_contact_no ?? '') }}">
                            @error('emergency_contact_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Contact Address') }}</label>
                            <textarea name="emergency_contact_address" class="form-control @error('emergency_contact_address') is-invalid @enderror" rows="1">{{ old('emergency_contact_address', $employee->emergency_contact_address ?? '') }}</textarea>
                            @error('emergency_contact_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Organization Details -->
                    <div class="form-section-title">
                        <i class="bi bi-building"></i>{{ __('Organization & Role') }}
                    </div>
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Office') }}</label>
                            <select name="office_id" class="form-select @error('office_id') is-invalid @enderror">
                                <option value="">{{ __('Select Office') }}</option>
                                @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ old('office_id', $employee->office_id ?? '') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                @endforeach
                            </select>
                            @error('office_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Department') }}</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">{{ __('Select Department') }}</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Designation') }}</label>
                            <select name="designation_id" class="form-select @error('designation_id') is-invalid @enderror">
                                <option value="">{{ __('Select Designation') }}</option>
                                @foreach($designations as $desig)
                                <option value="{{ $desig->id }}" {{ old('designation_id', $employee->designation_id ?? '') == $desig->id ? 'selected' : '' }}>{{ $desig->name }}</option>
                                @endforeach
                            </select>
                            @error('designation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Section') }}</label>
                            <select name="section_id" class="form-select @error('section_id') is-invalid @enderror">
                                <option value="">{{ __('Select Section') }}</option>
                                @foreach($sections as $sec)
                                <option value="{{ $sec->id }}" {{ old('section_id', $employee->section_id ?? '') == $sec->id ? 'selected' : '' }}>{{ $sec->name }} ({{ $sec->department->short_name ?? '' }})</option>
                                @endforeach
                            </select>
                            @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Grade') }}</label>
                            <select name="grade_id" class="form-select @error('grade_id') is-invalid @enderror">
                                <option value="">{{ __('Select Grade') }}</option>
                                @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ old('grade_id', $employee->grade_id ?? '') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                @endforeach
                            </select>
                            @error('grade_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Gross Salary') }}</label>
                            <input type="number" id="gross_salary" name="gross_salary" class="form-control @error('gross_salary') is-invalid @enderror" value="{{ old('gross_salary', $employee->gross_salary ?? '') }}" placeholder="e.g. 50000" step="0.01" min="0" data-initial="{{ $employee->gross_salary ?? '' }}">
                            <input type="hidden" id="salary_change_reason" name="salary_change_reason" value="">
                            @error('gross_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Office Time (Shift)') }}</label>
                            <select id="office_time_id" name="office_time_id" class="form-select @error('office_time_id') is-invalid @enderror">
                                <option value="">{{ __('Select Shift') }}</option>
                                @foreach($officeTimes as $time)
                                <option value="{{ $time->id }}" {{ old('office_time_id', $employee->office_time_id ?? '') == $time->id ? 'selected' : '' }}>{{ $time->shift_name }} ({{ \Carbon\Carbon::parse($time->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($time->end_time)->format('H:i') }})</option>
                                @endforeach
                            </select>
                            @error('office_time_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4" id="rosterGroupWrapper" data-roster-shift-id="{{ \App\Models\OfficeTime::where('shift_name', 'Roster')->value('id') }}" style="display: none;">
                            <label class="form-label">{{ __('Roster Group') }}</label>
                            <select name="roster_group" class="form-select @error('roster_group') is-invalid @enderror">
                                <option value="">{{ __('None') }}</option>
                                @foreach(['All', 'NOC (Borak)', 'NOC (Sylhet)', 'Technician (Gulshan)', 'Technician (Borak)', 'Technician (Jessore)', 'Technician (Sylhet)'] as $rg)
                                <option value="{{ $rg }}" {{ old('roster_group', $employee->roster_group ?? 'All') == $rg ? 'selected' : '' }}>{{ $rg }}</option>
                                @endforeach
                            </select>
                            @error('roster_group') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Reporting Manager') }}</label>
                            <select name="reporting_manager_id" class="form-select @error('reporting_manager_id') is-invalid @enderror">
                                <option value="">{{ __('Select Manager') }}</option>
                                @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ old('reporting_manager_id', $employee->reporting_manager_id ?? '') == $manager->id ? 'selected' : '' }}>{{ $manager->name }} ({{ $manager->employee_code }})</option>
                                @endforeach
                            </select>
                            @error('reporting_manager_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Employee Type') }} <span class="text-danger">*</span></label>
                            <select id="employee_type" name="employee_type" class="form-select @error('employee_type') is-invalid @enderror" required>
                                <option value="Regular" {{ old('employee_type', $employee->employee_type ?? 'Regular') == 'Regular' ? 'selected' : '' }}>{{ __('Regular') }}</option>
                                <option value="Probation" {{ old('employee_type', $employee->employee_type ?? '') == 'Probation' ? 'selected' : '' }}>{{ __('Probation') }}</option>
                            </select>
                            @error('employee_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Probation Information -->
                    <div id="probation-section" class="row g-4 mb-5" @style(['display' => old('employee_type', $employee->employee_type ?? '') == 'Probation' ? 'flex' : 'none'])>
                        <div class="col-12">
                            <div class="form-section-title mt-0">
                                <i class="bi bi-clock-history"></i>{{ __('Probation Information') }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Duration (In months)') }}</label>
                            <input type="number" id="probation_duration" name="probation_duration" class="form-control @error('probation_duration') is-invalid @enderror" value="{{ old('probation_duration', $employee->probation_duration ?? '') }}" min="1">
                            @error('probation_duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Probation Start Date') }}</label>
                            <input type="text" id="probation_start_date" name="probation_start_date" class="form-control @error('probation_start_date') is-invalid @enderror" value="{{ old('probation_start_date', $employee->probation_start_date ?? '') }}" placeholder="Same as joining date" readonly>
                            @error('probation_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Probation End Date') }}</label>
                            <input type="text" id="probation_end_date" name="probation_end_date" class="form-control @error('probation_end_date') is-invalid @enderror" value="{{ old('probation_end_date', $employee->probation_end_date ?? '') }}" placeholder="Auto calculated" readonly>
                            @error('probation_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Account & Status -->
                    <div class="form-section-title">
                        <i class="bi bi-shield-lock"></i>{{ __('System User Account') }}
                    </div>
                    <div class="row g-4 mb-3">
                        <div class="col-12 mb-2">
                            <div class="form-text text-muted">
                                <i class="bi bi-info-circle me-1"></i>{{ __('Fill these fields to create or update the system user account for this employee. The corporate email field above will be used as the login email. To update an existing user account, its password field can be left blank.') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Password') }}</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ isset($employee) && $employee->user_id ? __('Leave blank to keep existing') : __('Enter password') }}">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Confirm Password') }}</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="{{ __('Confirm password') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Role') }}</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                                <option value="">{{ __('— No Account —') }}</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', isset($employee) && $employee->user ? $employee->user->role_id : '') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('User Status') }}</label>
                            <select name="user_status" class="form-select @error('user_status') is-invalid @enderror">
                                <option value="active" {{ old('user_status', isset($employee) && $employee->user ? $employee->user->status : 'active') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ old('user_status', isset($employee) && $employee->user ? $employee->user->status : '') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                            @error('user_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Experience Information -->
                <div class="form-card mb-5">
                    <div class="form-section-title position-relative d-flex align-items-center mb-4">
                        <span><i class="bi bi-briefcase"></i>{{ __('Work Experience') }}</span>
                        <button type="button" id="add-experience" class="btn btn-outline-success rounded-pill font-bold transition-all hover:bg-success hover:text-white position-absolute" style="font-size: 11px; padding: 2px 12px; width: fit-content; border-width: 1px; right: 0; top: 50%; transform: translateY(-50%);">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('Add Experience') }}
                        </button>
                    </div>
                    
                    <div id="experience-container">
                        @php
                            $experiences = old('experiences', (isset($employee) ? $employee->experiences : []));
                        @endphp
                        
                        @forelse($experiences as $index => $exp)
                            @php $expId = is_array($exp) ? ($exp['id'] ?? null) : ($exp->id ?? null); @endphp
                            <div class="experience-row border rounded p-3 mb-3 bg-light/30">
                                <div class="position-relative d-flex align-items-center mb-3">
                                    <h6 class="mb-0 text-xs font-bold text-gray-500">{{ __('Record #') }}<span class="row-number">{{ $index + 1 }}</span></h6>
                                    <button type="button" class="btn btn-outline-danger rounded-pill font-bold transition-all hover:bg-danger hover:text-white position-absolute" 
                                        onclick="handleExperienceDelete(this, '{{ $expId }}')"
                                        style="font-size: 11px; padding: 2px 12px; width: fit-content; border-width: 1px; right: 0; top: 50%; transform: translateY(-50%);">
                                        <i class="bi bi-trash me-1"></i>{{ __('Remove') }}
                                    </button>
                                    @if($expId)
                                        <input type="hidden" name="experiences[{{ $index }}][id]" value="{{ $expId }}">
                                    @endif
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-xs uppercase">{{ __('Organization') }}</label>
                                        <input type="text" name="experiences[{{ $index }}][organization]" class="form-control form-control-sm" value="{{ is_array($exp) ? ($exp['organization'] ?? '') : ($exp->organization ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-xs uppercase">{{ __('Designation') }}</label>
                                        <input type="text" name="experiences[{{ $index }}][designation]" class="form-control form-control-sm" value="{{ is_array($exp) ? ($exp['designation'] ?? '') : ($exp->designation ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-xs uppercase">{{ __('Department') }}</label>
                                        <input type="text" name="experiences[{{ $index }}][department]" class="form-control form-control-sm" value="{{ is_array($exp) ? ($exp['department'] ?? '') : ($exp->department ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-xs uppercase">{{ __('Date From') }}</label>
                                        <input type="text" name="experiences[{{ $index }}][date_from]" class="form-control form-control-sm experience-date" value="{{ is_array($exp) ? ($exp['date_from'] ?? '') : ($exp->date_from ?? '') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-xs uppercase">{{ __('Date To') }}</label>
                                        <input type="text" name="experiences[{{ $index }}][date_to]" class="form-control form-control-sm experience-date" value="{{ is_array($exp) ? ($exp['date_to'] ?? '') : ($exp->date_to ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-xs uppercase">{{ __('Responsibilities') }}</label>
                                        <textarea name="experiences[{{ $index }}][responsibilities]" class="form-control form-control-sm" rows="1">{{ is_array($exp) ? ($exp['responsibilities'] ?? '') : ($exp->responsibilities ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div id="no-experience-msg" class="text-center py-4 text-gray-400">
                                <i class="bi bi-info-circle me-1"></i>{{ __('No experience records added yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <template id="experience-template">
                    <div class="experience-row border rounded p-3 mb-3 bg-light/30">
                        <div class="position-relative d-flex align-items-center mb-3">
                            <h6 class="mb-0 text-xs font-bold text-gray-500">{{ __('Record #') }}<span class="row-number">__ITERATION__</span></h6>
                            <button type="button" class="btn btn-outline-danger rounded-pill font-bold transition-all hover:bg-danger hover:text-white position-absolute" 
                                onclick="handleExperienceDelete(this, null)"
                                style="font-size: 11px; padding: 2px 12px; width: fit-content; border-width: 1px; right: 0; top: 50%; transform: translateY(-50%);">
                                <i class="bi bi-trash me-1"></i>{{ __('Remove') }}
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-xs uppercase">{{ __('Organization') }}</label>
                                <input type="text" name="experiences[__INDEX__][organization]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-xs uppercase">{{ __('Designation') }}</label>
                                <input type="text" name="experiences[__INDEX__][designation]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-xs uppercase">{{ __('Department') }}</label>
                                <input type="text" name="experiences[__INDEX__][department]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs uppercase">{{ __('Date From') }}</label>
                                <input type="text" name="experiences[__INDEX__][date_from]" class="form-control form-control-sm experience-date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-xs uppercase">{{ __('Date To') }}</label>
                                <input type="text" name="experiences[__INDEX__][date_to]" class="form-control form-control-sm experience-date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-xs uppercase">{{ __('Responsibilities') }}</label>
                                <textarea name="experiences[__INDEX__][responsibilities]" class="form-control form-control-sm" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Qualification Information -->
                <div class="form-card mb-5">
                    <div class="form-section-title position-relative d-flex align-items-center mb-4">
                        <span><i class="bi bi-mortarboard"></i>{{ __('Academic Qualifications') }}</span>
                        <button type="button" id="add-qualification" class="btn btn-outline-success rounded-pill font-bold transition-all hover:bg-success hover:text-white position-absolute" style="font-size: 11px; padding: 2px 12px; width: fit-content; border-width: 1px; right: 0; top: 50%; transform: translateY(-50%);">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('Add Qualification') }}
                        </button>
                    </div>
                    
                    <div id="qualification-container">
                        @php
                            $qualifications = old('qualifications', (isset($employee) ? $employee->qualifications : []));
                        @endphp
                        
                        @forelse($qualifications as $index => $qual)
                            @php $qualId = is_array($qual) ? ($qual['id'] ?? null) : ($qual->id ?? null); @endphp
                            <div class="qualification-row border rounded p-3 mb-3 bg-light/30">
                                <div class="position-relative d-flex align-items-center mb-3">
                                    <h6 class="mb-0 text-xs font-bold text-gray-500">{{ __('Record #') }}<span class="row-number">{{ $index + 1 }}</span></h6>
                                    <button type="button" class="btn btn-outline-danger rounded-pill font-bold transition-all hover:bg-danger hover:text-white position-absolute" 
                                        onclick="handleQualificationDelete(this, '{{ $qualId }}')"
                                        style="font-size: 11px; padding: 2px 12px; width: fit-content; border-width: 1px; right: 0; top: 50%; transform: translateY(-50%);">
                                        <i class="bi bi-trash me-1"></i>{{ __('Remove') }}
                                    </button>
                                    @if($qualId)
                                        <input type="hidden" name="qualifications[{{ $index }}][id]" value="{{ $qualId }}">
                                    @endif
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-xs uppercase">{{ __('Qualification') }}</label>
                                        <input type="text" name="qualifications[{{ $index }}][qualification]" class="form-control form-control-sm" value="{{ is_array($qual) ? ($qual['qualification'] ?? '') : ($qual->qualification ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-xs uppercase">{{ __('Level') }}</label>
                                        <input type="text" name="qualifications[{{ $index }}][level]" class="form-control form-control-sm" value="{{ is_array($qual) ? ($qual['level'] ?? '') : ($qual->level ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-xs uppercase">{{ __('Institution') }}</label>
                                        <input type="text" name="qualifications[{{ $index }}][institution]" class="form-control form-control-sm" value="{{ is_array($qual) ? ($qual['institution'] ?? '') : ($qual->institution ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-xs uppercase">{{ __('Board/University') }}</label>
                                        <input type="text" name="qualifications[{{ $index }}][board_university]" class="form-control form-control-sm" value="{{ is_array($qual) ? ($qual['board_university'] ?? '') : ($qual->board_university ?? '') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label text-xs uppercase">{{ __('Passing Year') }}</label>
                                        <input type="text" name="qualifications[{{ $index }}][passing_year]" class="form-control form-control-sm" value="{{ is_array($qual) ? ($qual['passing_year'] ?? '') : ($qual->passing_year ?? '') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-xs uppercase">{{ __('Group/Major') }}</label>
                                        <input type="text" name="qualifications[{{ $index }}][group_major]" class="form-control form-control-sm" value="{{ is_array($qual) ? ($qual['group_major'] ?? '') : ($qual->group_major ?? '') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label text-xs uppercase">{{ __('Result') }}</label>
                                        <input type="text" name="qualifications[{{ $index }}][result]" class="form-control form-control-sm" value="{{ is_array($qual) ? ($qual['result'] ?? '') : ($qual->result ?? '') }}">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div id="no-qualification-msg" class="text-center py-4 text-gray-400">
                                <i class="bi bi-info-circle me-1"></i>{{ __('No qualification records added yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <template id="qualification-template">
                    <div class="qualification-row border rounded p-3 mb-3 bg-light/30">
                        <div class="position-relative d-flex align-items-center mb-3">
                            <h6 class="mb-0 text-xs font-bold text-gray-500">{{ __('Record #') }}<span class="row-number">__ITERATION__</span></h6>
                            <button type="button" class="btn btn-outline-danger rounded-pill font-bold transition-all hover:bg-danger hover:text-white position-absolute" 
                                onclick="handleQualificationDelete(this, null)"
                                style="font-size: 11px; padding: 2px 12px; width: fit-content; border-width: 1px; right: 0; top: 50%; transform: translateY(-50%);">
                                <i class="bi bi-trash me-1"></i>{{ __('Remove') }}
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-xs uppercase">{{ __('Qualification') }}</label>
                                <input type="text" name="qualifications[__INDEX__][qualification]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-xs uppercase">{{ __('Level') }}</label>
                                <input type="text" name="qualifications[__INDEX__][level]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-xs uppercase">{{ __('Institution') }}</label>
                                <input type="text" name="qualifications[__INDEX__][institution]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-xs uppercase">{{ __('Board/University') }}</label>
                                <input type="text" name="qualifications[__INDEX__][board_university]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs uppercase">{{ __('Passing Year') }}</label>
                                <input type="text" name="qualifications[__INDEX__][passing_year]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-xs uppercase">{{ __('Group/Major') }}</label>
                                <input type="text" name="qualifications[__INDEX__][group_major]" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-xs uppercase">{{ __('Result') }}</label>
                                <input type="text" name="qualifications[__INDEX__][result]" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </template>

                <div class="d-flex justify-content-end gap-3 mb-5">
                    <a href="{{ route('personnel.employees.index') }}" class="btn btn-light bg-white border rounded-pill px-4 py-2 font-bold">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary bg-success border-success rounded-pill px-5 py-2 font-bold">{{ isset($employee) ? __('Update Employee') : __('Create Employee') }}</button>
                </div>
            </form>
        </main>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const officeSelect = document.querySelector('select[name="office_id"]');
            const employeeCodeInput = document.querySelector('input[name="employee_code"]');
            const isEditMode = "{{ isset($employee) ? 'true' : 'false' }}" === "true";

            function updateEmployeeCode(selectedDate) {
                if (isEditMode) return;
                if (!selectedDate) return;

                const selectedOfficeId = officeSelect ? officeSelect.value : '';
                const url = `/personnel/employees/next-code?date=${selectedDate}&office_id=${selectedOfficeId}`;
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.code) {
                            employeeCodeInput.value = data.code;
                        }
                    })
                    .catch(error => console.error('Error fetching next employee code:', error));
            }

            const joiningPicker = flatpickr('#joining_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
                onChange: function(selectedDates, dateStr) {
                    updateEmployeeCode(dateStr);
                }
            });

            flatpickr('#date_of_birth', {
                dateFormat: 'Y-m-d',
                allowInput: false,
            });

            flatpickr('#discontinuation_date', {
                dateFormat: 'Y-m-d',
                allowInput: false,
            });

            if (officeSelect && employeeCodeInput && !isEditMode) {
                officeSelect.addEventListener('change', function() {
                    const dateStr = joiningPicker.selectedDates.length ? joiningPicker.input.value : '';
                    updateEmployeeCode(dateStr);
                });

                if (officeSelect.value && joiningPicker.input.value) {
                    updateEmployeeCode(joiningPicker.input.value);
                }
            }

            // Intercept form submission if gross salary changed
            const employeeForm = document.getElementById('employee-form');
            const grossSalaryInput = document.getElementById('gross_salary');
            const salaryReasonInput = document.getElementById('salary_change_reason');

            if (employeeForm && grossSalaryInput && isEditMode) {
                employeeForm.addEventListener('submit', function(e) {
                    const initialSalary = parseFloat(grossSalaryInput.getAttribute('data-initial')) || 0;
                    const currentSalary = parseFloat(grossSalaryInput.value) || 0;

                    if (initialSalary !== currentSalary && initialSalary > 0) {
                        e.preventDefault(); // Stop normal submission

                        const isIncrement = currentSalary > initialSalary;
                        const actionText = isIncrement ? 'increment' : 'pay cut';
                        const titleText = isIncrement ? 'Salary Increment' : 'Salary Pay Cut';
                        const confirmColor = isIncrement ? '#10b981' : '#ef4444';

                        Swal.fire({
                            title: titleText,
                            html: `You are about to issue a <b>${actionText}</b> from <b>${initialSalary.toFixed(2)}</b> to <b>${currentSalary.toFixed(2)}</b>.<br><br>Please provide an optional reason for this change:`,
                            icon: 'warning',
                            input: 'text',
                            inputPlaceholder: 'Reason for salary change (optional)',
                            showCancelButton: true,
                            confirmButtonColor: confirmColor,
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, proceed',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                salaryReasonInput.value = result.value || '';
                                employeeForm.submit(); // Submit programmatically
                            }
                        });
                    }
                });
            }

            // Probation Logic
            const employeeTypeSelect = document.getElementById('employee_type');
            const probationSection = document.getElementById('probation-section');
            const probationDurationInput = document.getElementById('probation_duration');
            const probationStartDateInput = document.getElementById('probation_start_date');
            const probationEndDateInput = document.getElementById('probation_end_date');
            const joiningDateInput = document.getElementById('joining_date');

            function calculateProbationEndDate() {
                const startDateStr = probationStartDateInput.value;
                const durationMonths = parseInt(probationDurationInput.value);

                if (startDateStr && !isNaN(durationMonths)) {
                    const startDate = new Date(startDateStr);
                    const endDate = new Date(startDate);
                    endDate.setMonth(startDate.getMonth() + durationMonths);
                    
                    // Format back to YYYY-MM-DD
                    const yyyy = endDate.getFullYear();
                    const mm = String(endDate.getMonth() + 1).padStart(2, '0');
                    const dd = String(endDate.getDate()).padStart(2, '0');
                    probationEndDateInput.value = `${yyyy}-${mm}-${dd}`;
                } else {
                    probationEndDateInput.value = '';
                }
            }

            if (employeeTypeSelect) {
                employeeTypeSelect.addEventListener('change', function() {
                    if (this.value === 'Probation') {
                        probationSection.style.display = 'flex';
                        probationStartDateInput.value = joiningDateInput.value;
                        calculateProbationEndDate();
                    } else {
                        probationSection.style.display = 'none';
                    }
                });
            }

            if (joiningDateInput) {
                joiningDateInput.addEventListener('change', function() {
                    if (employeeTypeSelect.value === 'Probation') {
                        probationStartDateInput.value = this.value;
                        calculateProbationEndDate();
                    }
                });
            }

            if (probationDurationInput) {
                probationDurationInput.addEventListener('input', calculateProbationEndDate);
            }

            // Experience Repeater Logic
            const experienceContainer = document.getElementById('experience-container');
            const addExperienceBtn = document.getElementById('add-experience');
            const noExpMsg = document.getElementById('no-experience-msg');
            const expTemplate = document.getElementById('experience-template');
            
            let expCount = document.querySelectorAll('.experience-row').length;

            function initDatePickers(container = document) {
                container.querySelectorAll('.experience-date').forEach(el => {
                    if (!el._flatpickr) {
                        flatpickr(el, {
                            dateFormat: 'Y-m-d',
                            allowInput: false,
                        });
                    }
                });
            }

            // Initialize existing datepickers
            initDatePickers();

            if (addExperienceBtn) {
                addExperienceBtn.addEventListener('click', function() {
                    if (noExpMsg) noExpMsg.style.display = 'none';
                    
                    const index = expCount;
                    let html = expTemplate.innerHTML;
                    html = html.replace(/__INDEX__/g, index);
                    html = html.replace(/__ITERATION__/g, expCount + 1);
                    
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html;
                    const newRow = wrapper.firstElementChild;
                    
                    experienceContainer.appendChild(newRow);
                    initDatePickers(newRow);
                    expCount++;
                });
            }

            // Handle Experience Deletion with SweetAlert2
            window.handleExperienceDelete = function(btn, id) {
                Swal.fire({
                    title: 'Delete Experience?',
                    text: 'Are you sure you want to remove this experience record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const row = btn.closest('.experience-row');
                        
                        if (id) {
                            // Perform backend deletion
                            fetch(`/personnel/employees/experience/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    row.remove();
                                    reorderExperiences();
                                    Swal.fire('Deleted!', 'The record has been deleted.', 'success');
                                } else {
                                    Swal.fire('Error', 'Failed to delete record from server.', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error', 'An error occurred while deleting the record.', 'error');
                            });
                        } else {
                            // Just remove from UI for unsaved records
                            row.remove();
                            reorderExperiences();
                        }
                    }
                });
            };

            // Reorder function moved to global scope within the script
            window.reorderExperiences = function() {
                const container = document.getElementById('experience-container');
                const noMsg = document.getElementById('no-experience-msg');
                const rows = container.querySelectorAll('.experience-row');
                
                if (rows.length === 0) {
                    if (noMsg) noMsg.style.display = 'block';
                } else {
                    if (noMsg) noMsg.style.display = 'none';
                }
                
                rows.forEach((row, idx) => {
                    const rowNum = row.querySelector('.row-number');
                    if (rowNum) rowNum.textContent = idx + 1;
                    
                    row.querySelectorAll('input, textarea').forEach(field => {
                        const name = field.getAttribute('name');
                        if (name && name.includes('experiences[')) {
                            field.setAttribute('name', name.replace(/experiences\[\d+\]/, `experiences[${idx}]`));
                        }
                    });
                });
            };

            // Qualification Repeater Logic
            const qualificationContainer = document.getElementById('qualification-container');
            const addQualificationBtn = document.getElementById('add-qualification');
            const noQualMsg = document.getElementById('no-qualification-msg');
            const qualTemplate = document.getElementById('qualification-template');
            
            let qualCount = document.querySelectorAll('.qualification-row').length;

            if (addQualificationBtn) {
                addQualificationBtn.addEventListener('click', function() {
                    if (noQualMsg) noQualMsg.style.display = 'none';
                    
                    const index = qualCount;
                    let html = qualTemplate.innerHTML;
                    html = html.replace(/__INDEX__/g, index);
                    html = html.replace(/__ITERATION__/g, qualCount + 1);
                    
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = html;
                    const newRow = wrapper.firstElementChild;
                    
                    qualificationContainer.appendChild(newRow);
                    qualCount++;
                });
            }

            // Handle Qualification Deletion with SweetAlert2
            window.handleQualificationDelete = function(btn, id) {
                Swal.fire({
                    title: 'Delete Qualification?',
                    text: 'Are you sure you want to remove this academic record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const row = btn.closest('.qualification-row');
                        
                        if (id) {
                            // Perform backend deletion
                            fetch(`/personnel/employees/qualification/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    row.remove();
                                    reorderQualifications();
                                    Swal.fire('Deleted!', 'The record has been deleted.', 'success');
                                } else {
                                    Swal.fire('Error', 'Failed to delete record from server.', 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error', 'An error occurred while deleting the record.', 'error');
                            });
                        } else {
                            // Just remove from UI for unsaved records
                            row.remove();
                            reorderQualifications();
                        }
                    }
                });
            };

            // Reorder function for Qualifications
            window.reorderQualifications = function() {
                const container = document.getElementById('qualification-container');
                const noMsg = document.getElementById('no-qualification-msg');
                const rows = container.querySelectorAll('.qualification-row');
                
                if (rows.length === 0) {
                    if (noMsg) noMsg.style.display = 'block';
                } else {
                    if (noMsg) noMsg.style.display = 'none';
                }
                
                rows.forEach((row, idx) => {
                    const rowNum = row.querySelector('.row-number');
                    if (rowNum) rowNum.textContent = idx + 1;
                    
                    row.querySelectorAll('input').forEach(field => {
                        const name = field.getAttribute('name');
                        if (name && name.includes('qualifications[')) {
                            field.setAttribute('name', name.replace(/qualifications\[\d+\]/, `qualifications[${idx}]`));
                        }
                    });
                });
            };

            // Roster Group Toggle Logic
            const officeTimeSelect = document.getElementById('office_time_id');
            const rosterWrapper = document.getElementById('rosterGroupWrapper');
            const rosterShiftId = rosterWrapper && rosterWrapper.dataset.rosterShiftId ? parseInt(rosterWrapper.dataset.rosterShiftId) : null;
            
            function toggleRosterGroup() {
                if (officeTimeSelect && rosterWrapper && rosterShiftId) {
                    if (parseInt(officeTimeSelect.value) === rosterShiftId) {
                        rosterWrapper.style.display = 'block';
                    } else {
                        rosterWrapper.style.display = 'none';
                    }
                }
            }

            if (officeTimeSelect) {
                officeTimeSelect.addEventListener('change', toggleRosterGroup);
                toggleRosterGroup();
            }
        });
    </script>
    @endpush
</x-app-layout>



