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

            <!-- Filter Bar -->
            <div class="filter-bar mb-4">
                <form action="{{ route('security.users.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small font-bold text-gray-600">{{ __('Search') }}</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-gray-400">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Name or Email..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small font-bold text-gray-600">{{ __('Role') }}</label>
                        <select name="role_id" class="form-select">
                            <option value="">{{ __('All Roles') }}</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small font-bold text-gray-600">{{ __('Status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-secondary w-100 me-2">
                            {{ __('Filter') }}
                        </button>
                        <a href="{{ route('security.users.index') }}" class="btn btn-link text-gray-500 p-0 mb-1">
                            <i class="bi bi-x-circle text-xl"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="hr-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table hr-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">
                                    <a href="{{ route('security.users.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        {{ __('Name') }}
                                        @if(request('sort', 'name') === 'name')
                                        <i class="bi bi-sort-{{ request('direction', 'asc') === 'asc' ? 'down' : 'up' }} sort-icon text-success"></i>
                                        @else
                                        <i class="bi bi-sort-down sort-icon"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('security.users.index', array_merge(request()->query(), ['sort' => 'email', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="sort-link">
                                        {{ __('Email') }}
                                        @if(request('sort') === 'email')
                                        <i class="bi bi-sort-{{ request('direction') === 'asc' ? 'down' : 'up' }} sort-icon text-success"></i>
                                        @else
                                        <i class="bi bi-sort-down sort-icon"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="hr-avatar-sm">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                        <span class="font-bold text-gray-700">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $u->role->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $u->status === 'active' ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' }}">
                                        {{ ucfirst($u->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('security.users.edit', $u) }}" class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @php $confirmMsg = __('Are you sure you want to delete this user?'); @endphp
                                        <form action="{{ route('security.users.destroy', $u) }}" method="POST" onsubmit="return confirm('{{ $confirmMsg }}')">
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
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-people text-4xl text-gray-200 d-block mb-3"></i>
                                    <span class="text-gray-500">{{ __('No users found.') }}</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </main>
    </div>
</x-app-layout>