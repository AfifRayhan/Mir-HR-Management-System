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
                        <h5 class="mb-1">{{ __('Office Time (Shift) Management') }}</h5>
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

            <div class="hr-panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="hr-panel-title mb-0">
                        <i class="bi bi-clock me-2 text-primary"></i>{{ __('Shift Schedule List') }}
                    </div>
                    <a href="{{ route('settings.office-times.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('Add New Shift') }}
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="hr-table">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('Shift Name') }}</th>
                                <th>{{ __('Start Time') }}</th>
                                <th>{{ __('End Time') }}</th>
                                <th>{{ __('Late After') }}</th>
                                <th>{{ __('Absent After') }}</th>
                                <th class="text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($officeTimes as $time)
                            <tr>
                                <td class="fw-bold text-primary">{{ $time->shift_name }}</td>
                                <td><span class="badge bg-success-soft text-success rounded-pill px-3">{{ $time->start_time }}</span></td>
                                <td><span class="badge bg-secondary rounded-pill px-3">{{ $time->end_time }}</span></td>
                                <td><span class="text-warning fw-500">{{ $time->late_after ?? '--:--' }}</span></td>
                                <td><span class="text-danger fw-500">{{ $time->absent_after ?? '--:--' }}</span></td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('settings.office-times.edit', $time) }}" class="btn btn-link text-primary p-1" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <?php $statement = "Are you sure you want to delete this shift?"; ?>
                                        <form action="{{ route('settings.office-times.destroy', $time) }}" method="POST" onsubmit='return confirm("{{ $statement }}")'>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-1" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-clock-history d-block mb-3 fs-1 opacity-50"></i>
                                        {{ __('No shift schedules found.') }}
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>