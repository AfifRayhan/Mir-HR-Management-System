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
                            {{ $employee ? $employee->name : ($user->name ?? __('HR Administrator')) }}
                            • {{ $roleName }}
                        </p>
                    </div>
                    <div class="text-end text-sm text-gray-500">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>{{ now()->format('l, d M Y') }}
                    </div>
                </div>
            </div>


            <div class="hr-panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="hr-panel-title mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Shift Schedule List') }}
                    </div>
                    <a href="{{ route('settings.office-times.create') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">
                        <i class="bi bi-plus-circle me-2"></i> {{ __('Add New Shift') }}
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="hr-table">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('Shift Name') }}</th>
                                <th>{{ __('Shift Timing') }}</th>
                                <th>{{ __('Lunch Break') }}</th>
                                <th>{{ __('Late Threshold') }}</th>
                                <th>{{ __('Absent Threshold') }}</th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($officeTimes as $time)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $time->shift_name }}</div>
                                    <div class="small text-muted">{{ __('General Shift') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success-soft text-success rounded-pill px-3 border border-success-subtle">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>{{ \Carbon\Carbon::parse($time->start_time)->format('h:i A') }}
                                        </span>
                                        <i class="bi bi-arrow-right text-muted small"></i>
                                        <span class="badge bg-secondary-soft text-secondary rounded-pill px-3 border border-secondary-subtle">
                                            <i class="bi bi-box-arrow-right me-1"></i>{{ \Carbon\Carbon::parse($time->end_time)->format('h:i A') }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary-soft text-primary rounded-pill px-3 border border-primary-subtle">
                                            <i class="bi bi-cup-hot me-1"></i>{{ $time->lunch_start ? \Carbon\Carbon::parse($time->lunch_start)->format('h:i A') : '--:--' }}
                                        </span>
                                        <i class="bi bi-dash text-muted small"></i>
                                        <span class="badge bg-primary-soft text-primary rounded-pill px-3 border border-primary-subtle">
                                            {{ $time->lunch_end ? \Carbon\Carbon::parse($time->lunch_end)->format('h:i A') : '--:--' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center text-warning fw-500">
                                        <i class="bi bi-clock me-2"></i>{{ $time->late_after ? \Carbon\Carbon::parse($time->late_after)->format('h:i A') : '--:--' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center text-danger fw-500">
                                        <i class="bi bi-person-x me-2"></i>{{ $time->absent_after ? \Carbon\Carbon::parse($time->absent_after)->format('h:i A') : '--:--' }}
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        @if($time->remarks)
                                        <button class="btn btn-sm btn-outline-info border-0" title="{{ __('View Remarks') }}" data-bs-toggle="modal" data-bs-target="#remarksModal{{ $time->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @endif
                                        <a href="{{ route('settings.office-times.edit', $time) }}" class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @php $statement = "Are you sure you want to delete this shift?"; @endphp
                                        <form action="{{ route('settings.office-times.destroy', $time) }}" method="POST" data-confirm data-confirm-message="{{ $statement }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                    @if($time->remarks)
                                    <!-- Remarks Modal -->
                                    <div class="modal fade" id="remarksModal{{ $time->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-primary">
                                                        <i class="bi bi-chat-left-text me-2"></i>{{ __('Shift Remarks') }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-4 text-start">
                                                    <div class="p-3 bg-light rounded-3 text-muted">
                                                        {{ $time->remarks }}
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
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