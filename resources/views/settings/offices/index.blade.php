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
                        <h5 class="mb-1">{{ __('Office Management') }}</h5>
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
                <!-- New Office Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title">
                            <i class="bi bi-plus-circle me-2 text-primary"></i>{{ __('Add New Office') }}
                        </div>

                        <form action="{{ route('settings.offices.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Office Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="{{ __('e.g. Head Office - Dhaka') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Office Type') }} <span class="text-danger">*</span></label>
                                <select name="office_type_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('Select Type') }}</option>
                                    @foreach($officeTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Order Number') }} <span class="text-danger">*</span></label>
                                <input type="number" name="order_number" class="form-control rounded-3" value="0" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Email Address') }}</label>
                                <input type="email" name="email" class="form-control rounded-3" placeholder="{{ __('office@example.com') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Phone Number') }}</label>
                                <input type="text" name="phone" class="form-control rounded-3" placeholder="{{ __('+880...') }}">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Address') }}</label>
                                <textarea name="address" class="form-control rounded-3" rows="3" placeholder="{{ __('Full office address...') }}"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i>{{ __('Save Office') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Office List -->
                <div class="col-lg-8">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-list-task me-2 text-primary"></i>{{ __('Office List') }}
                        </div>

                        <div class="table-responsive">
                            <table class="hr-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 60px;">{{ __('Order') }}</th>
                                        <th>{{ __('Office Information') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Contact') }}</th>
                                        <th class="text-center">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($offices as $office)
                                    <tr>
                                        <td><span class="badge bg-secondary rounded-pill">{{ $office->order_number }}</span></td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $office->name }}</div>
                                            <div class="small text-muted text-truncate" style="max-width: 200px;" title="{{ $office->address }}">{{ $office->address }}</div>
                                        </td>
                                        <td><span class="hr-badge hr-badge-global">{{ $office->type->name }}</span></td>
                                        <td>
                                            <div class="small"><i class="bi bi-envelope me-1"></i>{{ $office->email ?? '---' }}</div>
                                            <div class="small"><i class="bi bi-telephone me-1"></i>{{ $office->phone ?? '---' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn btn-link text-primary p-1" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editModal{{ $office->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <?php $statement = "Are you sure you want to delete this office?"; ?>
                                                <form action="{{ route('settings.offices.destroy', $office) }}" method="POST" onsubmit="return confirm('{{ $statement }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-1" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal{{ $office->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-primary">{{ __('Edit Office') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('settings.offices.update', $office) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body py-4">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Office Name') }}</label>
                                                                <input type="text" name="name" class="form-control rounded-3" value="{{ $office->name }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Office Type') }}</label>
                                                                <select name="office_type_id" class="form-select rounded-3" required>
                                                                    @foreach($officeTypes as $type)
                                                                    <option value="{{ $type->id }}" {{ $office->office_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Order Number') }}</label>
                                                                <input type="number" name="order_number" class="form-control rounded-3" value="{{ $office->order_number }}" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Email Address') }}</label>
                                                                <input type="email" name="email" class="form-control rounded-3" value="{{ $office->email }}">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Phone Number') }}</label>
                                                                <input type="text" name="phone" class="form-control rounded-3" value="{{ $office->phone }}">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Address') }}</label>
                                                                <textarea name="address" class="form-control rounded-3" rows="3">{{ $office->address }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Update Office') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-building-add d-block mb-3 fs-1 opacity-50"></i>
                                                {{ __('No offices found.') }}
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