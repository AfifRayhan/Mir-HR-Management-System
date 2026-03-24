<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.team-lead-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-1 fw-bold">{{ __('Team Leave History') }}</h5>
                    <p class="mb-0 text-muted">{{ __('View past leave applications from your direct reports') }}</p>
                </div>
            </div>

            <div class="hr-panel p-0 overflow-hidden mb-4">
                <div class="d-flex justify-content-between align-items-center border-bottom p-3">
                    <h6 class="fw-bold mb-0 text-nowrap me-3"><i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Applications History') }}</h6>
                    <form action="{{ route('team-lead.leave-applications.history') }}" method="GET">
                        <div class="d-flex flex-wrap gap-2 mb-2 justify-content-end">
                            <select name="month" class="form-select form-select-sm rounded-3 w-auto">
                                <option value="">{{ __('All Months') }}</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                    {{ date('M', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                    @endfor
                            </select>
                            <select name="year" class="form-select form-select-sm rounded-3 w-auto">
                                <option value="">{{ __('All Years') }}</option>
                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                                @endfor
                            </select>
                            <select name="employee_id" class="form-select form-select-sm rounded-3 w-auto border-success">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach($teamEmployees as $teamEmp)
                                <option value="{{ $teamEmp->id }}" {{ $employeeId == $teamEmp->id ? 'selected' : '' }}>
                                    {{ $teamEmp->name }} ({{ $teamEmp->employee_code }})
                                </option>
                                @endforeach
                            </select>
                            <select name="leave_type_id" class="form-select form-select-sm rounded-3 w-auto">
                                <option value="">{{ __('All Leave Types') }}</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}" {{ $leaveTypeId == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="form-select form-select-sm rounded-3 w-auto">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-sm btn-success rounded-3 px-3 fw-bold">{{ __('Filter') }}</button>
                            @if(request()->hasAny(['month', 'year', 'employee_id', 'leave_type_id', 'status']))
                            <a href="{{ route('team-lead.leave-applications.history') }}" class="btn btn-sm btn-light rounded-3 px-3 fw-bold">{{ __('Clear') }}</a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table hr-table mb-0">
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
                                <th>{{ __('Approved By') }}</th>
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
                                <td><span class="badge bg-info text-dark rounded-pill">{{ $app->leaveType->name }}</span></td>
                                <td>
                                    @php
                                        $balance = $app->employee->leaveBalances->where('leave_type_id', $app->leave_type_id)->first();
                                    @endphp
                                    <div class="fw-bold text-dark">{{ $balance ? ($balance->used_days . '/' . $balance->opening_balance) : '0/0' }}</div>
                                </td>
                                <td>
                                    <div class="small fw-bold">{{ \Carbon\Carbon::parse($app->from_date)->format('d M') }} - {{ \Carbon\Carbon::parse($app->to_date)->format('d M Y') }}</div>
                                    <div class="small text-muted border-top pt-1 mt-1">Applied: {{ $app->created_at->format('d M') }}</div>
                                </td>
                                <td><span class="badge bg-secondary">{{ $app->total_days }}</span></td>
                                <td>
                                    <span class="d-inline-block text-truncate small" style="max-width: 200px;" title="{{ $app->reason }}">{{ $app->reason }}</span>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate small text-muted" style="max-width: 150px;" title="{{ $app->leave_address }}">{{ $app->leave_address ?: '--' }}</span>
                                </td>
                                <td>
                                    @if($app->supporting_document)
                                    <a href="{{ asset('storage/' . $app->supporting_document) }}" target="_blank" class="badge bg-primary text-white text-decoration-none">
                                        <i class="bi bi-file-earmark-medical me-1"></i>{{ __('Doc') }}
                                    </a>
                                    @else
                                    <span class="text-muted small">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($app->status === 'approved')
                                    <span class="badge bg-success-soft text-success"><i class="bi bi-check-circle me-1"></i>{{ __('Approved') }}</span>
                                    @elseif($app->status === 'rejected')
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-danger-soft text-danger"><i class="bi bi-x-circle me-1"></i>{{ __('Rejected') }}</span>
                                        @if($app->remarks)
                                        <button type="button" class="btn btn-link p-0 text-danger" data-bs-toggle="modal" data-bs-target="#viewRemarksModal{{ $app->id }}" title="{{ __('View Remarks') }}">
                                            <i class="bi bi-info-circle"></i>
                                        </button>

                                        <!-- View Remarks Modal -->
                                        <div class="modal fade" id="viewRemarksModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content">
                                                    <div class="modal-header py-2">
                                                        <h6 class="modal-title">{{ __('Approval Remarks') }}</h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body py-3 text-start">
                                                        <p class="mb-0 small text-dark">{{ $app->remarks }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @else
                                    <span class="badge bg-warning-soft text-warning"><i class="bi bi-hourglass-split me-1"></i>{{ __('Pending') }}</span>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-inbox d-block mb-3 fs-1 opacity-50"></i>
                                    <span class="text-muted">{{ __('No leave history found for your team.') }}</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($applications->hasPages())
                <div class="p-3 border-top">
                    {{ $applications->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </main>
    </div>
</x-app-layout>