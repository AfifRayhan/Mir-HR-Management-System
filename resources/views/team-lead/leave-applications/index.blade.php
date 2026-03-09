<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.team-lead-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-1 fw-bold">{{ __('Team Leave Applications') }}</h5>
                    <p class="mb-0 text-muted">{{ __('Review and manage leave requests from your direct reports') }}</p>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="hr-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table hr-table mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">{{ __('Employee') }}</th>
                                <th>{{ __('Leave Type') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Days') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $app)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="emp-avatar-sm me-3">
                                            {{ strtoupper(substr($app->employee->first_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $app->employee->first_name }} {{ $app->employee->last_name }}</div>
                                            <div class="small text-muted">{{ $app->employee->employee_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark rounded-pill px-3">{{ $app->leaveType->name }}</span>
                                </td>
                                <td>
                                    <div class="small fw-bold">{{ \Carbon\Carbon::parse($app->from_date)->format('d M Y') }}</div>
                                    <div class="small text-muted">{{ __('to') }} {{ \Carbon\Carbon::parse($app->to_date)->format('d M Y') }}</div>
                                </td>
                                <td><span class="badge bg-secondary rounded-pill">{{ $app->total_days }}</span></td>
                                <td>
                                    <span class="d-inline-block text-truncate small text-muted" style="max-width: 150px;" title="{{ $app->reason }}">
                                        {{ $app->reason }}
                                    </span>
                                </td>
                                <td>
                                    @if($app->status === 'approved')
                                    <span class="badge bg-success-soft text-success">{{ __('Approved') }}</span>
                                    @elseif($app->status === 'rejected')
                                    <span class="badge bg-danger-soft text-danger">{{ __('Rejected') }}</span>
                                    @else
                                    <span class="badge bg-warning-soft text-warning">{{ __('Pending') }}</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($app->status === 'pending')
                                    <div class="d-flex justify-content-end gap-2">
                                        <form action="{{ route('team-lead.leave-applications.status', $app->id) }}" method="POST" onsubmit="return confirm('Approve this leave application?');">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                <i class="bi bi-check-lg me-1"></i>{{ __('Approve') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('team-lead.leave-applications.status', $app->id) }}" method="POST" onsubmit="return confirm('Reject this leave application?');">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-x-lg me-1"></i>{{ __('Reject') }}
                                            </button>
                                        </form>
                                    </div>
                                    @else
                                    <span class="small text-muted">{{ __('Processed') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox d-block mb-3 fs-1 opacity-50"></i>
                                    <span class="text-muted">{{ __('No leave applications from your team.') }}</span>
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