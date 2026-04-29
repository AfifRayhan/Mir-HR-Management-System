<x-app-layout>
    <div class="ui-layout">
        @include('partials.team-lead-sidebar')

        <main class="ui-main">
            <div class="row mb-4 align-items-center">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-1">{{ __('Supervisor Remarks') }}</h4>
                    <p class="text-muted mb-0">{{ __('Manage and send messages to your team members') }}</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('team-lead.remarks.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm">
                        <i class="bi bi-plus-lg me-2"></i>{{ __('Send New Remark') }}
                    </a>
                </div>
            </div>


            <div class="ui-panel">
                <div class="table-responsive">
                    <table class="table ui-table text-nowrap">
                        <thead>
                            <tr>
                                <th>{{ __('Recipient') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Message') }}</th>
                                <th>{{ __('Sent Date') }}</th>
                                <th>{{ __('Expiry Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($remarks as $remark)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            {{ substr($remark->employee->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold small">{{ $remark->employee->name }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">ID: {{ $remark->employee->employee_code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="small fw-medium">{{ $remark->title }}</span></td>
                                <td>
                                    <span class="d-inline-block text-truncate small" style="max-width: 250px;" title="{{ $remark->message }}">
                                        {{ $remark->message }}
                                    </span>
                                </td>
                                <td class="small">{{ $remark->created_at->format('d M, Y') }}</td>
                                <td class="small">
                                    {{ $remark->expires_at ? $remark->expires_at->format('d M, Y H:i') : __('No Expiry') }}
                                </td>
                                <td>
                                    @if($remark->expires_at && $remark->expires_at->isPast())
                                        <span class="badge bg-danger-soft text-danger"><i class="bi bi-clock-history me-1"></i>{{ __('Expired') }}</span>
                                    @else
                                        <span class="badge bg-success-soft text-success"><i class="bi bi-check-circle me-1"></i>{{ __('Active') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('team-lead.remarks.destroy', $remark) }}" method="POST" class="d-inline" data-confirm data-confirm-message="{{ __('Are you sure you want to delete this remark?') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 border-0 text-decoration-none" title="{{ __('Delete') }}">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-chat-left-dots text-muted opacity-25" style="font-size: 3rem;"></i>
                                    </div>
                                    <p class="text-muted">{{ __('No remarks sent yet.') }}</p>
                                    <a href="{{ route('team-lead.remarks.create') }}" class="btn btn-outline-success btn-sm rounded-pill px-4 mt-2">
                                        {{ __('Send your first remark') }}
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $remarks->links() }}
                </div>
            </div>
        </main>
    </div>
    @push('styles')
    <style>
        .btn:focus, .btn:active, .form-check-input:focus, .form-control:focus, a:focus {
            box-shadow: none !important;
            outline: none !important;
        }
    </style>
    @endpush
</x-app-layout>




