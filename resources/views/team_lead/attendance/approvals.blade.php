<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Subordinate Attendance Adjustments') }}
        </h2>
    </x-slot>

    

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-0">{{ __('Pending Requests in Your Team') }}</h4>
                    </div>
                </div>

                <div class="ui-panel p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table ui-table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Employee</th>
                                    <th>Date</th>
                                    <th>Requested In Time</th>
                                    <th>Requested Out Time</th>
                                    <th>Reason</th>
                                    <th class="pe-4 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adjustments as $adjustment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold">{{ $adjustment->employee->name }}</div>
                                            <div class="small text-muted">{{ $adjustment->employee->employee_code }} • {{ optional($adjustment->employee->department)->name }}</div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($adjustment->date)->format('d M Y') }}</td>
                                        <td>{{ $adjustment->in_time ? \Carbon\Carbon::parse($adjustment->in_time)->format('h:i A') : '-' }}</td>
                                        <td>{{ $adjustment->out_time ? \Carbon\Carbon::parse($adjustment->out_time)->format('h:i A') : '-' }}</td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $adjustment->reason }}">
                                                {{ $adjustment->reason }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('team-lead.attendances.approve', $adjustment->id) }}" method="POST"
                                                    data-confirm
                                                    data-confirm-message="Approve attendance adjustment for {{ $adjustment->employee->name }} on {{ \Carbon\Carbon::parse($adjustment->date)->format('d M Y') }}?"
                                                    data-confirm-type="success">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm px-3 font-bold rounded-pill btn-pill-action">
                                                        <i class="bi bi-check-lg me-1"></i>{{ __('Approve') }}
                                                    </button>
                                                </form>

                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm px-3 font-bold rounded-pill btn-pill-action"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal{{ $adjustment->id }}">
                                                    <i class="bi bi-x-lg me-1"></i>{{ __('Reject') }}
                                                </button>
                                            </div>

                                            <div class="modal fade text-start" id="rejectModal{{ $adjustment->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <form class="modal-content border-0 shadow" action="{{ route('team-lead.attendances.reject', $adjustment->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold"><i class="bi bi-x-circle text-danger me-2"></i>Reject Adjustment</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="text-muted small mb-3">You are rejecting the request for <strong>{{ $adjustment->employee->name }}</strong> on <strong>{{ \Carbon\Carbon::parse($adjustment->date)->format('d M Y') }}</strong>.</p>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Reason for Rejection <span class="text-danger">*</span></label>
                                                                <textarea name="reject_reason" class="form-control rounded-3" rows="3" required placeholder="Enter rejection reason..." maxlength="50"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-x-circle me-1"></i>Reject</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                            No pending attendance adjustments in your team.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>




