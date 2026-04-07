<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-5">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ isset($employee) ? __('Edit Employee') : __('Add New Employee') }}</h5>
                        <p class="mb-0 text-gray-500">{{ isset($employee) ? __('Update employee profile and associations') : __('Create a new employee profile in the system') }}</p>
                    </div>
                    <a href="{{ route('personnel.employees.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
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
                            <label class="form-label">{{ __('Personal Email') }}</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email ?? '') }}" placeholder="personal@example.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <select name="office_time_id" class="form-select @error('office_time_id') is-invalid @enderror">
                                <option value="">{{ __('Select Shift') }}</option>
                                @foreach($officeTimes as $time)
                                <option value="{{ $time->id }}" {{ old('office_time_id', $employee->office_time_id ?? '') == $time->id ? 'selected' : '' }}>{{ $time->shift_name }} ({{ \Carbon\Carbon::parse($time->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($time->end_time)->format('H:i') }})</option>
                                @endforeach
                            </select>
                            @error('office_time_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                    <div id="probation-section" class="row g-4 mb-5" style="display: {{ old('employee_type', $employee->employee_type ?? '') == 'Probation' ? 'flex' : 'none' }};">
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
                                <i class="bi bi-info-circle me-1"></i>{{ __('Fill these fields to create or update the system user account for this employee. The personal email field above will be used as the login email. To update an existing user account, its password field can be left blank.') }}
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

                <div class="d-flex justify-content-end gap-3 mb-5">
                    <a href="{{ route('personnel.employees.index') }}" class="btn btn-light bg-white border px-4 py-2 font-bold">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary bg-success border-success px-5 py-2 font-bold">{{ isset($employee) ? __('Update Employee') : __('Create Employee') }}</button>
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
        });
    </script>
    @endpush
</x-app-layout>