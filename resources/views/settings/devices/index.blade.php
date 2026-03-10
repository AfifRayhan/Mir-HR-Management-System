<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Devices') }}
        </h2>
    </x-slot>

    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('Devices Management') }}</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                            <i class="bi bi-plus-lg me-2"></i>{{ __('Add Device') }}
                        </button>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="hr-panel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Device UID') }}</th>
                                    <th>{{ __('API Token') }}</th>
                                    <th>{{ __('Last Sync') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devices as $device)
                                <tr>
                                    <td>{{ $device->id }}</td>
                                    <td><strong>{{ $device->name }}</strong><br><small class="text-muted">{{ $device->address }}</small></td>
                                    <td><code>{{ $device->device_uid ?? __('N/A') }}</code></td>
                                    <td>
                                        @if($device->api_token)
                                        <div class="input-group input-group-sm" style="max-width: 200px;">
                                            <input type="password" class="form-control" value="{{ $device->api_token }}" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="this.previousElementSibling.type = this.previousElementSibling.type === 'password' ? 'text' : 'password'"><i class="bi bi-eye"></i></button>
                                        </div>
                                        @else
                                        {{ __('N/A') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($device->last_sync_at)
                                        <span class="badge bg-success">{{ \Carbon\Carbon::parse($device->last_sync_at)->diffForHumans() }}</span>
                                        @else
                                        <span class="badge bg-secondary">{{ __('Never') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editDeviceModal{{ $device->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('settings.devices.destroy', $device) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editDeviceModal{{ $device->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('settings.devices.update', $device) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ __('Edit Device') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Device Name') }} *</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $device->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Device UID (Unique ID)') }}</label>
                                                        <input type="text" name="device_uid" class="form-control" value="{{ $device->device_uid }}" placeholder="e.g. H94139">
                                                        <small class="text-muted">{{ __('This ID must match the identifier sent by the device.') }}</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Address/Location') }}</label>
                                                        <input type="text" name="address" class="form-control" value="{{ $device->address }}" placeholder="e.g. 192.168.1.10">
                                                    </div>
                                                    @if($device->device_uid)
                                                    <div class="form-check mb-3">
                                                        <input class="form-check-input" type="checkbox" name="regenerate_token" id="regToken{{ $device->id }}">
                                                        <label class="form-check-label text-danger" for="regToken{{ $device->id }}">
                                                            {{ __('Regenerate API Token') }}
                                                        </label>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">{{ __('No devices found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addDeviceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('settings.devices.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add New Device') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Device Name') }} *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Main Entrance Biometric" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Device UID (Unique ID)') }}</label>
                            <input type="text" name="device_uid" class="form-control" placeholder="e.g. H94139">
                            <small class="text-muted">{{ __('Assigning a UID will automatically generate an API token.') }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Address/Location') }}</label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. 192.168.1.10 or Main Gate">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Add Device') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>