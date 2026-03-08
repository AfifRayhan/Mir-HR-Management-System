<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <style>
        .emp-avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background: #ecfdf5;
            color: #059669;
        }
    </style>
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Leave Accounts Management') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Initialize and monitor employee leave balances for ') }} {{ $currentYear }}</p>
                    </div>
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

            <div class="row g-4">
                <!-- Initialize Account Form -->
                <div class="col-lg-4">
                    <div class="hr-panel">
                        <div class="hr-panel-title">
                            <i class="bi bi-person-plus me-2 text-primary"></i>{{ __('Initialize Employee Account') }}
                        </div>

                        <p class="small text-muted mb-4">{{ __('Select an employee to open their leave account for the specified year. This will create starting balances based on current Leave Type settings.') }}</p>

                        <form action="{{ route('personnel.leave-balances.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">{{ __('Select Employee') }} <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select rounded-3" required>
                                    <option value="">{{ __('Choose...') }}</option>
                                    @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }} ({{ $emp->employee_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">{{ __('Target Year') }} <span class="text-danger">*</span></label>
                                <input type="number" name="year" class="form-control rounded-3" value="{{ $currentYear }}" required min="2020" max="2050">
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-wallet2 me-2"></i>{{ __('Initialize Account') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Existing Balances List -->
                <div class="col-lg-8">
                    <div class="hr-panel">
                        <div class="hr-panel-title mb-4">
                            <i class="bi bi-list-task me-2 text-primary"></i>{{ __('Initialized Accounts ') }} ({{ $currentYear }})
                        </div>

                        <div class="table-responsive">
                            <table class="table hr-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">{{ __('Employee') }}</th>
                                        <th>{{ __('Department / Role') }}</th>
                                        <th>{{ __('Leave Types Allocated') }}</th>
                                        <th>{{ __('Total Allocation') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($balances as $employeeId => $employeeBalances)
                                    @php
                                    $emp = $employeeBalances->first()->employee;
                                    $totalDays = $employeeBalances->sum('opening_balance');
                                    $typesCount = $employeeBalances->count();
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="emp-avatar-sm me-3">
                                                    {{ strtoupper(substr($emp->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-800">{{ $emp->first_name }} {{ $emp->last_name }}</div>
                                                    <div class="small text-muted">{{ $emp->employee_code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold">{{ $emp->department->name ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $emp->designation->name ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark rounded-pill">{{ $typesCount }} Types</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">{{ $totalDays }} Days</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <i class="bi bi-wallet2 d-block mb-3 fs-1 opacity-50"></i>
                                            {{ __('No leave accounts have been initialized for ') }} {{ $currentYear }}.
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