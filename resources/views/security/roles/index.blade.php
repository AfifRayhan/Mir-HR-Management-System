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
                        <h4 class="fw-bold mb-1">{{ __('Role Management') }}</h4>
                        <p class="mb-0 small text-muted">{{ __('Define roles. Use Role Permission to assign menu access.') }}</p>
                    </div>
                    <a href="{{ route('security.role-permissions.index') }}" class="btn btn-outline-primary rounded-pill btn-sm px-3">
                        <i class="bi bi-shield-lock me-1"></i>{{ __('Role Permissions') }}
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <!-- Add Role Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-shield-plus me-2 text-success"></i>{{ __('Add New Role') }}
                        </div>

                        <form action="{{ route('security.roles.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Role Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" value="{{ old('name') }}" placeholder="e.g. Supervisor" required>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control rounded-3" rows="3" placeholder="Optional description">{{ old('description') }}</textarea>
                                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-shield-check me-2"></i>{{ __('Create Role') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Role List -->
                <div class="col-lg-8">
                    <div class="hr-panel p-0 overflow-hidden">
                        <div class="hr-panel-title p-4 border-bottom">
                            <i class="bi bi-list-stars me-2 text-primary"></i>{{ __('Existing Roles') }}
                        </div>

                        <div class="table-responsive">
                            <table class="table hr-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">{{ __('Role Name') }}</th>
                                        <th>{{ __('Users') }}</th>
                                        <th>{{ __('Menu Access') }}</th>
                                        <th class="text-end pe-4">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $role)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-gray-800">{{ $role->name }}</div>
                                            @if($role->description)
                                                <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $role->description }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-primary border rounded-pill px-2">{{ $role->users_count }} users</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-info border rounded-pill px-2">{{ $role->menu_items_count }} items</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <form action="{{ route('security.roles.destroy', $role) }}" method="POST" data-confirm data-confirm-message="{{ __('Are you sure you want to delete this role?') }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Role Modal -->
                                    <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0 px-4 pt-4">
                                                    <h5 class="modal-title fw-bold text-primary">{{ __('Edit Role') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('security.roles.update', $role) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-muted">{{ __('Role Name') }} <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $role->name) }}" required>
                                                        </div>
                                                        <div class="mb-0">
                                                            <label class="form-label small fw-bold text-muted">{{ __('Description') }}</label>
                                                            <textarea name="description" class="form-control rounded-3" rows="3">{{ old('description', $role->description) }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 p-4 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Update Role') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-shield-x fs-1 d-block mb-2 opacity-25"></i>
                                            {{ __('No roles found.') }}
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($roles->hasPages())
                            <div class="px-4 py-3 border-top bg-light">
                                {{ $roles->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>