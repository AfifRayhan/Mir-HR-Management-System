<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ __('Role Management') }}</h5>
                        <p class="mb-0 small text-muted">{{ __('Define roles. Use') }} <a href="{{ route('security.role-permissions.index') }}">{{ __('Role Permission') }}</a> {{ __('to assign menu access.') }}</p>
                    </div>
                    <a href="{{ route('security.roles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('Add Role') }}
                    </a>
                </div>
            </div>


            <div class="hr-panel">
                <div class="table-responsive">
                    <table class="hr-table">
                        <thead>
                            <tr>
                                <th>{{ __('Role Name') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Users') }}</th>
                                <th>{{ __('Menu Access') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                            <tr>
                                <td><strong>{{ $role->name }}</strong></td>
                                <td class="text-muted">{{ $role->description ?? '—' }}</td>
                                <td><span class="hr-badge">{{ $role->users_count }}</span></td>
                                <td><span class="hr-badge hr-badge-info">{{ $role->menu_items_count }}</span></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('security.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @php $confirmMsg = __('Are you sure you want to delete this role?'); @endphp
                                        <form action="{{ route('security.roles.destroy', $role) }}" method="POST" data-confirm data-confirm-message="{{ $confirmMsg }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('No roles found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $roles->links() }}
                </div>
            </div>
        </main>
    </div>
</x-app-layout>