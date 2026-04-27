<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Devices') }}
        </h2>
    </x-slot>

    

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('Devices Management') }}</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                            <i class="bi bi-plus-lg me-2"></i>{{ __('Add Device') }}
                        </button>
                    </div>
                </div>


                <div class="ui-panel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('ID') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Device UID') }}</th>
                                    <th>{{ __('Port') }}</th>
                                    <th>{{ __('Last Sync') }}</th>
                                    <th class="text-end pe-4">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devices as $device)
                                <tr>
                                    <td>{{ $device->id }}</td>
                                    <td>
                                        <strong>{{ $device->name }}</strong><br>
                                        <small class="text-muted"><i class="bi bi-broadcast me-1"></i>{{ $device->ip_address }}</small><br>
                                        <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $device->location }}</small>
                                    </td>
                                    <td><code>{{ $device->device_uid ?? __('N/A') }}</code></td>
                                    <td>
                                        @if($device->port)
                                        <span class="badge bg-info text-dark">{{ $device->port }}</span>
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
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary border-0"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editDeviceModal{{ $device->id }}"
                                                title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            @php $confirmMsg = __('Are you sure you want to delete this device?'); @endphp
                                            <form action="{{ route('settings.devices.destroy', $device) }}" method="POST" data-confirm data-confirm-message="{{ $confirmMsg }}">
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
                                                        <label class="form-label">{{ __('IP Address') }}</label>
                                                        <input type="text" name="ip_address" class="form-control" value="{{ $device->ip_address }}" placeholder="e.g. 192.168.1.10">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Location') }}</label>
                                                        <input type="text" name="location" class="form-control" value="{{ $device->location }}" placeholder="e.g. Main Gate">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Port') }}</label>
                                                        <input type="text" name="port" class="form-control" value="{{ $device->port }}" placeholder="e.g. 4370">
                                                    </div>
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
                            <input type="text" name="device_uid" class="form-control" placeholder="e.g. 1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('IP Address') }}</label>
                            <input type="text" name="ip_address" class="form-control" placeholder="e.g. 192.168.1.10">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Port') }}</label>
                            <input type="text" name="port" class="form-control" placeholder="e.g. 4370">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Location') }}</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Main Gate">
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



