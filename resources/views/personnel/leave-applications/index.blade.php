<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Leave Applications (HR)') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Review and manage employee leave requests') }}</p>
                    </div>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
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
                                            <div class="font-bold text-gray-800">{{ $app->employee->first_name }} {{ $app->employee->last_name }}</div>
                                            <div class="small text-muted">{{ $app->employee->employee_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark rounded-pill px-3">{{ $app->leaveType->name }}</span>
                                </td>
                                <td>
                                    <div class="small font-bold text-gray-700">{{ \Carbon\Carbon::parse($app->from_date)->format('d M Y') }}</div>
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
                                        <form action="{{ route('personnel.leave-applications.status', $app->id) }}" method="POST" onsubmit="return confirm('Approve this leave application?');">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                <i class="bi bi-check-lg me-1"></i>{{ __('Approve') }}
                                            </button>
                                        </form>

                                        <form action="{{ route('personnel.leave-applications.status', $app->id) }}" method="POST" onsubmit="return confirm('Reject this leave application?');">
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
                                    <i class="bi bi-inbox d-block mb-3 fs-1 text-gray-300"></i>
                                    <span class="text-gray-500">{{ __('No leave applications found.') }}</span>
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