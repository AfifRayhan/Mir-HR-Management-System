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
                        <h5 class="mb-1">{{ __('User Management') }}</h5>
                        <p class="mb-0 small text-muted">{{ __('Create, edit and manage system users') }}</p>
                    </div>
                    <a href="{{ route('security.users.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('Add User') }}
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="hr-panel">
                <div class="table-responsive">
                    <table class="hr-table">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="hr-avatar-sm">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                        {{ $u->name }}
                                    </div>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="hr-badge">{{ $u->role->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="hr-status-badge {{ $u->status === 'active' ? 'hr-status-active' : 'hr-status-inactive' }}">
                                        {{ ucfirst($u->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('security.users.edit', $u) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('security.users.destroy', $u) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('No users found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </main>
    </div>
</x-app-layout>