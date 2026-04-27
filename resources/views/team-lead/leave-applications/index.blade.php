<x-app-layout>
    

    <div class="ui-layout">
        @include('partials.team-lead-sidebar')

        <main class="ui-main">
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-1 fw-bold">{{ __('Team Leave Applications') }}</h5>
                    <p class="mb-0 text-muted">{{ __('Review and manage leave requests from your direct reports') }}</p>
                </div>
            </div>


            <div class="ui-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table ui-table mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">{{ __('Employee') }}</th>
                                <th>{{ __('Leave Type') }}</th>
                                <th>{{ __('Taken/Total') }}</th>
                                <th>{{ __('Duration') }}</th>
                                <th>{{ __('Days') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Address') }}</th>
                                <th>{{ __('Doc') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action By') }}</th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $app)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="emp-avatar-sm me-3">
                                            {{ strtoupper(substr($app->employee->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $app->employee->name }}</div>
                                            <div class="small text-muted">{{ $app->employee->employee_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ match(true) { str_contains(strtolower($app->leaveType->name), 'casual') => 'bg-primary', str_contains(strtolower($app->leaveType->name), 'sick') => 'bg-danger', str_contains(strtolower($app->leaveType->name), 'earn') => 'bg-success', str_contains(strtolower($app->leaveType->name), 'emergency') => 'bg-warning text-dark', default => 'bg-info text-dark' } }} rounded-pill px-3">{{ $app->leaveType->name }}</span>
                                </td>
                                <td>
                                    @php
                                        $balance = $app->employee->leaveBalances->where('leave_type_id', $app->leave_type_id)->first();
                                    @endphp
                                    <div class="fw-bold text-dark">{{ $balance ? ($balance->used_days . '/' . $balance->opening_balance) : '0/0' }}</div>
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
                                    <span class="d-inline-block text-truncate small text-muted" style="max-width: 150px;" title="{{ $app->leave_address }}">
                                        {{ $app->leave_address ?: '--' }}
                                    </span>
                                </td>
                                <td>
                                    @if($app->supporting_document)
                                    <a href="{{ asset('storage/' . $app->supporting_document) }}" target="_blank" class="badge bg-success text-white text-decoration-none shadow-sm">
                                        <i class="bi bi-file-earmark-medical me-1"></i>{{ __('View') }}
                                    </a>
                                    @else
                                    <span class="text-muted small">--</span>
                                    @endif
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
                                <td>
                                    @if($app->approver)
                                    <div class="small fw-bold text-gray-800">{{ $app->approver->name ?: __('HR Admin') }}</div>
                                    <div class="small text-muted" style="font-size: 0.7rem;">{{ $app->approved_at?->format('d M, h:i A') }}</div>
                                    @else
                                    <span class="text-muted small">--</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($app->status === 'pending')
                                    <div class="d-flex justify-content-end gap-2">
                                        <form action="{{ route('team-lead.leave-applications.status', $app->id) }}" method="POST" data-confirm data-confirm-message="Approve this leave application?" data-confirm-type="success">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-outline-success btn-sm px-3 font-bold rounded-pill btn-pill-action">
                                                <i class="bi bi-check-lg me-1"></i>{{ __('Approve') }}
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger btn-sm px-3 font-bold rounded-pill btn-pill-action" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $app->id }}">
                                            <i class="bi bi-x-lg me-1"></i>{{ __('Reject') }}
                                        </button>

                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal{{ $app->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $app->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <form action="{{ route('team-lead.leave-applications.status', $app->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="rejected">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="rejectModalLabel{{ $app->id }}">{{ __('Reject Leave Application') }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <p class="mb-3">{{ __('Are you sure you want to reject this leave application for') }} <strong>{{ $app->employee->name }}</strong>?</p>

                                                            <div class="mb-3">
                                                                <label for="remarks{{ $app->id }}" class="form-label fw-bold">{{ __('Approval Remarks') }} <span class="text-muted fw-normal">({{ __('Optional, max 50 chars') }})</span></label>
                                                                <input type="text" class="form-control" id="remarks{{ $app->id }}" name="remarks" maxlength="50" placeholder="{{ __('Brief reason for rejection...') }}">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                            <button type="submit" class="btn btn-danger">{{ __('Confirm Rejection') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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



