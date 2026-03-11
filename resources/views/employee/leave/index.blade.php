<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-employee-dashboard.css'])
    @endpush

    <div class="emp-layout">
        @include('partials.employee-sidebar')

        <main class="emp-main">

            <div class="row mb-4 align-items-center">
                <div class="col-12">
                    <h4 class="fw-bold mb-1">{{ __('My Leave Space') }}</h4>
                    <p class="text-muted mb-0">{{ __('Apply for leave and check your balances') }}</p>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-2 small shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-2 small shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 px-4 py-3 small shadow-sm mb-4" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Balances Row -->
            <div class="row g-3 mb-5 flex-nowrap" style="overflow-x: auto;">
                @forelse($balances as $balance)
                <div class="col">
                    <div class="balance-card h-100" style="padding: 1rem 1.15rem;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-uppercase fw-bold text-gray-500" style="font-size: 0.7rem; letter-spacing: 0.03em;">{{ $balance->leaveType->name }}</span>
                            <i class="bi bi-calendar-check text-success" style="font-size: 1.1rem;"></i>
                        </div>
                        <div class="fw-bold text-gray-800 mb-1" style="font-size: 1.75rem; line-height: 1;">{{ $balance->remaining_days }}</div>
                        <div class="fw-bold text-gray-500" style="font-size: 0.72rem;">{{ __('Days Remaining') }}</div>
                        <div class="mt-2 pt-2 border-top border-gray-200">
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.72rem;">
                                <span>{{ __('Used:') }} <span class="fw-bold">{{ $balance->used_days }}</span></span>
                                <span>{{ __('Total:') }} <span class="fw-bold">{{ $balance->opening_balance }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info border-0 shadow-sm rounded-4">
                        <i class="bi bi-info-circle-fill me-2"></i>{{ __('You do not have any leave balances recorded yet.') }}
                    </div>
                </div>
                @endforelse
            </div>

            <div class="row g-4">
                <!-- Apply Leave Form -->
                <div class="col-lg-4">
                    <div class="emp-panel">
                        <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-journal-plus me-2 text-primary"></i>{{ __('Apply for Leave') }}</h5>
                        <form action="{{ route('employee.leave.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Leave Type') }} <span class="text-danger">*</span></label>
                                <select name="leave_type_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('Select Type') }}</option>
                                    @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->total_days_per_year }} days/yr)</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('From Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="from_date" class="form-control rounded-3" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted">{{ __('To Date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="to_date" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Reason / Remarks') }} <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control rounded-3" rows="3" required placeholder="{{ __('Why are you applying for leave?') }}"></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Emergency Contact / Leave Address') }}</label>
                                <textarea name="leave_address" class="form-control rounded-3" rows="2" placeholder="{{ __('Optional during leave period...') }}"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-send-check me-2"></i>{{ __('Submit Application') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- My Applications History -->
                <div class="col-lg-8">
                    <div class="emp-panel">
                        <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-clock-history me-2 text-primary"></i>{{ __('My Applications History') }}</h5>

                        <div class="table-responsive">
                            <table class="table emp-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>{{ __('Leave Type') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Days') }}</th>
                                        <th>{{ __('Reason') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $app)
                                    <tr>
                                        <td><span class="badge bg-info text-dark rounded-pill">{{ $app->leaveType->name }}</span></td>
                                        <td>
                                            <div class="small fw-bold">{{ \Carbon\Carbon::parse($app->from_date)->format('d M') }} - {{ \Carbon\Carbon::parse($app->to_date)->format('d M Y') }}</div>
                                            <div class="small text-muted border-top pt-1 mt-1">Applied: {{ $app->created_at->format('d M') }}</div>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ $app->total_days }}</span></td>
                                        <td>
                                            <span class="d-inline-block text-truncate small" style="max-width: 200px;" title="{{ $app->reason }}">{{ $app->reason }}</span>
                                        </td>
                                        <td>
                                            @if($app->status === 'approved')
                                            <span class="badge bg-success-soft text-success"><i class="bi bi-check-circle me-1"></i>{{ __('Approved') }}</span>
                                            @elseif($app->status === 'rejected')
                                            <span class="badge bg-danger-soft text-danger"><i class="bi bi-x-circle me-1"></i>{{ __('Rejected') }}</span>
                                            @else
                                            <span class="badge bg-warning-soft text-warning"><i class="bi bi-hourglass-split me-1"></i>{{ __('Pending') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="bi bi-journal-x d-block fs-1 text-gray-300 mb-3"></i>
                                            <span class="text-gray-500">{{ __('No leave history found.') }}</span>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</x-app-layout>