<x-app-layout>
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.5rem;
            border-color: #dee2e6;
            min-height: 38px;
        }
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 24px;
            padding-top: 6px;
        }
    </style>
    @endpush
    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function($) {
            $(document).ready(function() {
                if (typeof $.fn.select2 !== 'undefined') {
                    $('.select2').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: '{{ __("All Users") }}',
                        allowClear: true
                    });
                }
            });
        })(jQuery);
    </script>
    @endpush
    

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <script>
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('[data-confirm-user]');
                    if (!btn) return;

                    const form = btn.closest('form');
                    const pass = form.querySelector('input[name="password"]');
                    const conf = form.querySelector('input[name="password_confirmation"]');
                    
                    if (pass && conf) {
                        const pv = pass.value;
                        const cv = conf.value;
                        
                        // Mismatch
                        if (pv.length > 0 && pv !== cv) {
                            e.preventDefault();
                            e.stopPropagation();
                            alert('Passwords do not match! Please check again.');
                            conf.focus();
                            return false;
                        }

                        // Confirm First
                        if (btn.hasAttribute('data-confirm-user')) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const msg = pv.length > 0 
                                ? 'Update user and CHANGE password?' 
                                : 'Update user info? (Password will not be changed)';
                                
                            if (confirm(msg)) {
                                btn.removeAttribute('data-confirm-user');
                                btn.click();
                            }
                        }
                    }
                }, true); // Use capture phase to get ahead of other listeners
            </script>
            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">{{ __('User Management') }}</h4>
                        <p class="mb-0 small text-muted">{{ __('Create, edit and manage system users') }}</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Add User Form -->
                <div class="col-lg-4">
                    <div class="ui-panel">
                        <div class="ui-panel-title mb-4">
                            <i class="bi bi-person-plus me-2 text-success"></i>{{ __('Add New User') }}
                        </div>

                        <form action="{{ route('security.users.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control rounded-3" value="{{ old('email') }}" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Password') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control rounded-3" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('Confirm') }} <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Role') }}</label>
                                <select name="role_id" class="form-select rounded-3">
                                    <option value="">{{ __('— No Role —') }}</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Status') }}</label>
                                <select name="status" class="form-select rounded-3" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm btn-update-user" data-confirm-user="true">
                                <i class="bi bi-person-check me-2"></i>{{ __('Create User') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- User List -->
                <div class="col-lg-8">
                    <div class="ui-panel p-0 overflow-hidden">
                        <!-- Filter/Search Bar -->
                        <div class="p-4 border-bottom bg-light bg-opacity-10">
                            <form action="{{ route('security.users.index') }}" method="GET" class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <select name="search" class="form-select select2" onchange="this.form.submit()">
                                        <option value="">{{ __('All Users') }}</option>
                                        @foreach($allUsers as $uOpt)
                                            <option value="{{ $uOpt->id }}" {{ request('search') == $uOpt->id ? 'selected' : '' }}>
                                                {{ $uOpt->name }} ({{ $uOpt->employee->employee_code ?? $uOpt->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="role_id" class="form-select">
                                        <option value="">{{ __('All Roles') }}</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">{{ __('All Status') }}</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn ui-btn-search flex-grow-1">{{ __('Search') }}</button>
                                    <a href="{{ route('security.users.index') }}" class="btn ui-btn-clear flex-grow-1">{{ __('Clear') }}</a>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table ui-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">{{ __('User') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $u)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="hr-avatar-sm flex-shrink-0">
                                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-gray-800">{{ $u->name }}</div>
                                                    <div class="text-muted small">{{ $u->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-muted border">{{ $u->role->name ?? '—' }}</span></td>
                                        <td>
                                            <span class="badge {{ $u->status === 'active' ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}">
                                                {{ ucfirst($u->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('security.users.destroy', $u) }}" method="POST" data-confirm data-confirm-message="{{ __('Are you sure you want to delete this user?') }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit User Modal -->
                                    <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                    <h5 class="modal-title fw-bold text-primary">{{ __('Edit User') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('security.users.update', $u) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-muted">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $u->name) }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-muted">{{ __('Email') }} <span class="text-danger">*</span></label>
                                                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $u->email) }}" required>
                                                        </div>
                                                        <div class="row g-2 mb-3">
                                                            <div class="col-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('New Password') }}</label>
                                                                <input type="password" name="password" id="edit_password_{{ $u->id }}" class="form-control rounded-3" placeholder="Enter new password">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Confirm') }} <span class="text-danger">*</span></label>
                                                                <input type="password" name="password_confirmation" id="edit_password_confirmation_{{ $u->id }}" class="form-control rounded-3" placeholder="Repeat new password">
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Role') }}</label>
                                                                <select name="role_id" class="form-select rounded-3">
                                                                    <option value="">{{ __('— No Role —') }}</option>
                                                                    @foreach($roles as $role)
                                                                    <option value="{{ $role->id }}" {{ old('role_id', $u->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small fw-bold text-muted">{{ __('Status') }}</label>
                                                                <select name="status" class="form-select rounded-3" required>
                                                                    <option value="active" {{ old('status', $u->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                                                    <option value="inactive" {{ old('status', $u->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-4 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 btn-update-user" data-confirm-user="true">{{ __('Update User') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                                            {{ __('No users found.') }}
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($users->hasPages())
                            <div class="px-4 py-3 border-top bg-light">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>



