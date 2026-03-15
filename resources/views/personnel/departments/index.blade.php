<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Department Management') }}</h5>
                        <p class="mb-0 small text-muted">
                            {{ __('Welcome,') }}
                            {{ $employee ? $employee->first_name.' '.$employee->last_name : ($user->name ?? __('HR Administrator')) }}
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

            <div class="row g-4">
                <!-- New Department Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>{{ __('Add New Department') }}
                        </div>

                        <form action="{{ route('personnel.departments.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Department Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="{{ __('e.g. Information Technology') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Short Name') }}</label>
                                <input type="text" name="short_name" class="form-control rounded-3" placeholder="{{ __('e.g. IT') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('In-charge') }}</label>
                                <select name="incharge_id" class="form-select rounded-3">
                                    <option value="">{{ __('Select Employee') }}</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Order Sequence') }}</label>
                                <input type="number" name="order_sequence" class="form-control rounded-3" placeholder="{{ __('e.g. 1') }}" value="0">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control rounded-3" rows="3" placeholder="{{ __('Brief description...') }}"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>{{ __('Save Department') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Department List -->
                <div class="col-lg-8">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-list-task me-2 text-primary"></i>{{ __('Department List') }}
                        </div>

                        <div class="table-responsive">
                            <table class="hr-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Order') }}</th>
                                        <th>{{ __('Department Info') }}</th>
                                        <th>{{ __('Short Name') }}</th>
                                        <th>{{ __('In-charge') }}</th>
                                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($departments as $department)
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-primary border rounded-pill px-3">{{ $department->order_sequence }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $department->name }}</div>
                                            @if($department->description)
                                            <div class="small text-muted text-truncate" style="max-width: 250px;" title="{{ $department->description }}">
                                                {{ $department->description }}
                                            </div>
                                            @endif
                                        </td>
                                        <td><span class="hr-badge hr-badge-global">{{ $department->short_name ?: '---' }}</span></td>
                                        <td>
                                            <div class="small d-flex align-items-center">
                                                <i class="bi bi-person-badge me-2 text-muted"></i>
                                                {{ $department->incharge ? $department->incharge->first_name.' '.$department->incharge->last_name : '---' }}
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            @php $confirmMsg = __('Are you sure?'); @endphp
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editModal{{ $department->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('personnel.departments.destroy', $department) }}" method="POST" onsubmit="return confirm('{{ $confirmMsg }}')">
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
                                    <div class="modal fade" id="editModal{{ $department->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-primary">{{ __('Edit Department') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('personnel.departments.update', $department) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body py-4">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Department Name') }}</label>
                                                                <input type="text" name="name" class="form-control rounded-3" value="{{ $department->name }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Short Name') }}</label>
                                                                <input type="text" name="short_name" class="form-control rounded-3" value="{{ $department->short_name }}">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Order Sequence') }}</label>
                                                                <input type="number" name="order_sequence" class="form-control rounded-3" value="{{ $department->order_sequence }}">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('In-charge') }}</label>
                                                                <select name="incharge_id" class="form-select rounded-3">
                                                                    <option value="">{{ __('Select Employee') }}</option>
                                                                    @foreach($employees as $emp)
                                                                    <option value="{{ $emp->id }}" {{ $department->incharge_id == $emp->id ? 'selected' : '' }}>
                                                                        {{ $emp->first_name }} {{ $emp->last_name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Description') }}</label>
                                                                <textarea name="description" class="form-control rounded-3" rows="3">{{ $department->description }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Update Department') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-building d-block mb-3 fs-1 opacity-50"></i>
                                                {{ __('No departments found.') }}
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