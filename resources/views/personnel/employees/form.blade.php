<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
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

            <form action="{{ isset($employee) ? route('personnel.employees.update', $employee->id) : route('personnel.employees.store') }}" method="POST">
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
                            <input type="text" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror" value="{{ old('employee_code', $employee->employee_code ?? '') }}" required placeholder="e.g. EMP001">
                            @error('employee_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $employee->first_name ?? '') }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $employee->last_name ?? '') }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Phone Number') }}</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone ?? '') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Date of Birth') }}</label>
                            <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}">
                            @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Joining Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', $employee->joining_date ?? '') }}" required>
                            @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Local Address') }}</label>
                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $employee->address ?? '') }}</textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Organization Details -->
                    <div class="form-section-title">
                        <i class="bi bi-building"></i>{{ __('Organization & Role') }}
                    </div>
                    <div class="row g-4 mb-5">
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
                                <option value="{{ $manager->id }}" {{ old('reporting_manager_id', $employee->reporting_manager_id ?? '') == $manager->id ? 'selected' : '' }}>{{ $manager->first_name }} {{ $manager->last_name }} ({{ $manager->employee_code }})</option>
                                @endforeach
                            </select>
                            @error('reporting_manager_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Account & Status -->
                    <div class="form-section-title">
                        <i class="bi bi-shield-lock"></i>{{ __('Account & Status') }}
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">{{ __('System User Account') }}</label>
                                <a href="{{ route('security.users.create') }}" target="_blank" class="btn btn-link btn-sm p-0 text-success text-decoration-none font-bold">
                                    <i class="bi bi-plus-circle me-1"></i>{{ __('Add New User') }}
                                </a>
                            </div>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">{{ __('No user linked') }}</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $employee->user_id ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <div class="form-text mt-2 small text-muted">
                                <i class="bi bi-info-circle me-1"></i>{{ __('Link this employee to a system user account for dashboard access.') }}
                            </div>
                            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Employee Status') }} <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" {{ old('status', $employee->status ?? 'active') === 'active' ? 'checked' : '' }}>
                                    <label class="form-check-label text-success font-bold" for="statusActive">{{ __('Active') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="statusResigned" value="resigned" {{ old('status', $employee->status ?? '') === 'resigned' ? 'checked' : '' }}>
                                    <label class="form-check-label text-danger font-bold" for="statusResigned">{{ __('Resigned') }}</label>
                                </div>
                            </div>
                            @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
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
</x-app-layout>