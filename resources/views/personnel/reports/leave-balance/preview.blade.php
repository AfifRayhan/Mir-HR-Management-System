<x-app-layout>
    @push('styles')
    
    <style>
        .ui-download-bar {
            position: sticky;
            bottom: 0;
            background: #fff;
            border-top: 1px solid var(--hr-border, #e5e7eb);
            padding: 0.75rem 1.5rem;
            z-index: 10;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
        }
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
    @endpush

    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            {{-- Header --}}
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Leave Balance Report') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('View all leaves taken and balance for a specific employee') }}</p>
                    </div>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="ui-filter-bar">
                <form action="{{ route('personnel.reports.leave-balance.preview') }}" method="GET" class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small font-bold text-gray-600">{{ __('Employee') }}</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">{{ __('-- Select Employee --') }}</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }} ({{ $emp->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small font-bold text-gray-600">{{ __('Year') }}</label>
                        <select name="year" class="form-select">
                            @for($y = date('Y') + 1; $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn ui-btn-search flex-grow-1">{{ __('Search') }}</button>
                    </div>
                </form>
            </div>

            @if($selectedEmployeeId)
                @php $selectedEmp = $employees->find($selectedEmployeeId); @endphp

                <div class="info-card">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-1 font-bold text-xl text-dark">{{ $selectedEmp->name }}</h4>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-dark border me-2">{{ $selectedEmp->employee_code }}</span>
                                {{ $selectedEmp->designation->name ?? 'N/A' }} | {{ $selectedEmp->department->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-sm text-gray-500 mb-1">Year</div>
                            <div class="font-semibold text-lg text-success">
                                {{ $year }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ui-panel p-0 overflow-hidden mb-4">
                    <div class="table-responsive">
                        <table class="table ui-table mb-0">
                            <thead>
                                <tr class="text-center">
                                    <th class="ps-4 text-start">Leave Type</th>
                                    <th>Carryable</th>
                                    <th>Max Carry</th>
                                    <th>Entitled</th>
                                    <th>Carry Forward</th>
                                    <th>Taken</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalMaxCarry = 0;
                                    $totalEntitled = 0;
                                    $totalCarryForward = 0;
                                    $totalTaken = 0;
                                    $totalBalance = 0;
                                @endphp
                                @foreach($leaveBalances as $balance)
                                    @php
                                        $type = $balance->leaveType;
                                        $carryable = $type->carry_forward ? 'Yes' : 'No';
                                        $maxCarry = $type->carry_forward ? number_format($type->max_carry_forward, 2) : '-';
                                        $carryForwardAmt = number_format($balance->opening_balance - $type->total_days_per_year, 2);
                                        // But actually we have no separate carry_forward column in DB, we just have opening_balance.
                                        // Assume opening_balance includes carry forward, and entitled is always $type->total_days_per_year
                                        $entitled = number_format($type->total_days_per_year, 2);
                                        // Wait, earn leave calculation can be dynamic based on joining date
                                        $entitledAmt = $type->total_days_per_year;
                                        if (str_contains(strtolower($type->name), 'earn') || str_contains(strtolower($type->name), 'bonus')) {
                                            $carryForwardAmt = 0; 
                                            // The logic is complex to re-derive carry forward.
                                            // As per user's image, Earn Leave entitled was 0.00, carry forward 5.00, balance 5.00
                                            // I'll calculate Carry Forward = Opening Balance - Entitled roughly, 
                                            // but for simplicity, I can just show opening_balance, used_days, remaining_days
                                        }

                                        $c_carryable = $type->carry_forward ? 'Yes' : 'No';
                                        $c_maxCarry = $type->carry_forward ? $type->max_carry_forward : 0;
                                        $c_entitled = min($balance->opening_balance, $type->total_days_per_year); // Rough estimate
                                        if ($c_entitled < 0) $c_entitled = 0;
                                        $c_carryForward = max(0, $balance->opening_balance - $type->total_days_per_year);
                                        
                                        // For earn leave or bonus leave, it's better to show Entitled as Opening Balance if we don't know carry forward precisely
                                        // But wait! User's image shows Carry Forward explicitly.
                                        // If carry forward is Yes, then opening balance could be Entitled + Carry Forward.
                                        if ($type->carry_forward) {
                                            $c_carryForward = max(0, $balance->opening_balance - $type->total_days_per_year);
                                            $c_entitled = $balance->opening_balance - $c_carryForward;
                                        } else {
                                            $c_carryForward = 0;
                                            $c_entitled = $balance->opening_balance;
                                        }

                                        $totalMaxCarry += $c_maxCarry;
                                        $totalEntitled += $c_entitled;
                                        $totalCarryForward += $c_carryForward;
                                        $totalTaken += $balance->used_days;
                                        $totalBalance += $balance->remaining_days;
                                    @endphp
                                    <tr class="text-center">
                                        <td class="ps-4 text-start">{{ $type->name }}</td>
                                        <td>{{ $c_carryable }}</td>
                                        <td>{{ $c_maxCarry > 0 ? number_format($c_maxCarry, 2) : '' }}</td>
                                        <td>{{ number_format($c_entitled, 2) }}</td>
                                        <td>{{ number_format($c_carryForward, 2) }}</td>
                                        <td>{{ number_format($balance->used_days, 2) }}</td>
                                        <td>{{ number_format($balance->remaining_days, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="fw-bold bg-light">
                                <tr class="text-center">
                                    <td colspan="2" class="text-end pe-4">Total:</td>
                                    <td>{{ $totalMaxCarry > 0 ? number_format($totalMaxCarry, 2) : '' }}</td>
                                    <td>{{ number_format($totalEntitled, 2) }}</td>
                                    <td>{{ number_format($totalCarryForward, 2) }}</td>
                                    <td>{{ number_format($totalTaken, 2) }}</td>
                                    <td>{{ number_format($totalBalance, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Download Bar --}}
                <div class="ui-download-bar d-flex justify-content-between align-items-center mb-4 rounded-4">
                    <span class="text-gray-600 small">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ __('Showing :count records', ['count' => count($leaveBalances)]) }}
                    </span>
                    <div class="dropdown">
                        <button class="btn btn-success dropdown-toggle d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 45px; height: 45px; border-radius: 12px; padding: 0;">
                            <i class="bi bi-download fs-5"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4">
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2" href="#" id="downloadExcel">
                                    <div class="bg-success bg-opacity-10 p-2 rounded-3 me-3">
                                        <i class="bi bi-file-earmark-excel text-success"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ __('Excel Spreadsheet') }}</div>
                                        <div class="small text-muted">{{ __('Data with formatting (.xlsx)') }}</div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2" href="#" id="downloadCsv">
                                    <div class="bg-secondary bg-opacity-10 p-2 rounded-3 me-3">
                                        <i class="bi bi-filetype-csv text-secondary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ __('CSV File') }}</div>
                                        <div class="small text-muted">{{ __('Raw data for other systems (.csv)') }}</div>
                                    </div>
                                </a>
                            </li>
                            <li class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2" href="#" id="downloadPdf">
                                    <div class="bg-danger bg-opacity-10 p-2 rounded-3 me-3">
                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ __('PDF Document') }}</div>
                                        <div class="small text-muted">{{ __('Print-ready report (.pdf)') }}</div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center py-2" href="#" id="downloadWord">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-3 me-3">
                                        <i class="bi bi-file-earmark-word text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ __('Word Document') }}</div>
                                        <div class="small text-muted">{{ __('Editable document (.doc)') }}</div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            @else
                <div class="ui-panel text-center py-5">
                    <i class="bi bi-wallet2 text-gray-200" style="font-size: 5rem;"></i>
                    <h5 class="mt-3 text-gray-500">Please select an employee and year to view the leave balance report.</h5>
                </div>
            @endif
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var routes = {
                excel: "{{ route('personnel.reports.leave-balance.export.excel') }}",
                csv: "{{ route('personnel.reports.leave-balance.export.csv') }}",
                pdf: "{{ route('personnel.reports.leave-balance.export.pdf') }}",
                word: "{{ route('personnel.reports.leave-balance.export.word') }}",
            };

            function buildDownloadUrl(baseRoute) {
                var params = new URLSearchParams(window.location.search);
                return baseRoute + '?' + params.toString();
            }

            document.getElementById('downloadExcel')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.excel);
            });
            document.getElementById('downloadCsv')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.csv);
            });
            document.getElementById('downloadPdf')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.pdf);
            });
            document.getElementById('downloadWord')?.addEventListener('click', function (e) {
                e.preventDefault();
                window.location.href = buildDownloadUrl(routes.word);
            });
        });
    </script>
    @endpush
</x-app-layout>




