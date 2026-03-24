<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css', 'resources/css/custom-holidays.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Leave Types Management') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-2 small shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-2 small shadow-sm mb-4" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="row g-4">
                <!-- New Leave Type Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>{{ __('Add Leave Type') }}
                        </div>

                        <form action="{{ route('settings.leave-types.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Leave Type Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="{{ __('e.g. Annual, Sick, Casual') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Office Availability') }}</label>
                                <select name="office_id" class="form-select rounded-3">
                                    <option value="">{{ __('All Offices') }}</option>
                                    @foreach($offices as $office)
                                    <option value="{{ $office->id }}">{{ $office->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text small text-muted">{{ __('Leave empty if applicable to all offices') }}</div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Total Days / Year') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="total_days_per_year" class="form-control rounded-3" value="0" min="0" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Max Consecutive Days') }}</label>
                                    <input type="number" name="max_consecutive_days" class="form-control rounded-3" min="1" placeholder="{{ __('Unlimited') }}">
                                    <div class="form-text small text-muted">{{ __('Leave blank for unlimited') }}</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Priority Order') }} <span class="text-danger">*</span></label>
                                <input type="number" name="sort_order" class="form-control rounded-3" value="99" min="1" required>
                                <div class="form-text small text-muted">{{ __('Lower number = higher priority (e.g. 1 for Sick/Emergency leave)') }}</div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch custom-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="carryForward" name="carry_forward" value="1">
                                    <label class="form-check-label small fw-bold text-muted ms-2" for="carryForward">{{ __('Carry Forward to Next Year') }}</label>
                                </div>
                                <div class="form-text small">{{ __('If enabled, unused days will carry over to the next year automatically.') }}</div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>{{ __('Save Leave Type') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Leave Types List -->
                <div class="col-lg-8">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-list-task me-2 text-primary"></i>{{ __('Leave Types List') }}
                        </div>

                        <div class="table-responsive">
                            <table class="hr-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Priority') }}</th>
                                        <th>{{ __('Type Name') }}</th>
                                        <th>{{ __('Office') }}</th>
                                        <th class="text-center">{{ __('Days/Year') }}</th>
                                        <th class="text-center">{{ __('Max Consec.') }}</th>
                                        <th class="text-center">{{ __('Carry Fwd') }}</th>
                                        <th class="text-center">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leaveTypes as $leaveType)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-primary-soft text-primary fw-bold rounded-pill px-2">#{{ $leaveType->sort_order }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $leaveType->name }}</div>
                                        </td>
                                        <td>
                                            @if($leaveType->office)
                                            <span class="badge bg-secondary-soft text-secondary rounded-pill px-2">{{ $leaveType->office->name }}</span>
                                            @else
                                            <span class="badge bg-light text-muted rounded-pill px-2">{{ __('All Offices') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark rounded-pill px-3">{{ $leaveType->total_days_per_year }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($leaveType->max_consecutive_days)
                                            <span class="badge bg-warning-soft text-warning rounded-pill px-3">{{ $leaveType->max_consecutive_days }}</span>
                                            @else
                                            <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($leaveType->carry_forward)
                                            <span class="text-success"><i class="bi bi-check-circle-fill"></i> {{ __('Yes') }}</span>
                                            @else
                                            <span class="text-danger"><i class="bi bi-x-circle-fill"></i> {{ __('No') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editModal{{ $leaveType->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                @php $statement = __('Are you sure you want to delete this leave type?'); @endphp
                                                <form action="{{ route('settings.leave-types.destroy', $leaveType->id) }}" method="POST" onsubmit="return confirm('{{ $statement }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $leaveType->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-primary">{{ __('Edit Leave Type') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('settings.leave-types.update', $leaveType->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body py-4">
                                                        <div class="mb-3 text-start">
                                                            <label class="form-label small fw-bold text-muted">{{ __('Leave Type Name') }}</label>
                                                            <input type="text" name="name" class="form-control rounded-3" value="{{ $leaveType->name }}" required>
                                                        </div>
                                                        <div class="mb-3 text-start">
                                                            <label class="form-label small fw-bold text-muted">{{ __('Office Availability') }}</label>
                                                            <select name="office_id" class="form-select rounded-3">
                                                                <option value="">{{ __('All Offices') }}</option>
                                                                @foreach($offices as $office)
                                                                <option value="{{ $office->id }}" {{ $leaveType->office_id == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="row g-2 mb-3">
                                                            <div class="col-6 text-start">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Total Days / Year') }}</label>
                                                                <input type="number" name="total_days_per_year" class="form-control rounded-3" value="{{ $leaveType->total_days_per_year }}" required>
                                                            </div>
                                                            <div class="col-6 text-start">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Max Consecutive Days') }}</label>
                                                                <input type="number" name="max_consecutive_days" class="form-control rounded-3" min="1" value="{{ $leaveType->max_consecutive_days }}" placeholder="{{ __('Unlimited') }}">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 text-start">
                                                            <label class="form-label small fw-bold text-muted">{{ __('Priority Order') }}</label>
                                                            <input type="number" name="sort_order" class="form-control rounded-3" min="1" value="{{ $leaveType->sort_order }}" required>
                                                            <div class="form-text small text-muted">{{ __('Lower = higher priority') }}</div>
                                                        </div>
                                                        <div class="mb-3 text-start">
                                                            <div class="form-check form-switch custom-switch mt-2">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="carryForwardEdit{{ $leaveType->id }}" name="carry_forward" value="1" {{ $leaveType->carry_forward ? 'checked' : '' }}>
                                                                <label class="form-check-label small fw-bold text-muted ms-2" for="carryForwardEdit{{ $leaveType->id }}">{{ __('Carry Forward to Next Year') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Update Type') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-tag d-block mb-3 fs-1 opacity-50"></i>
                                                {{ __('No leave types found.') }}
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>