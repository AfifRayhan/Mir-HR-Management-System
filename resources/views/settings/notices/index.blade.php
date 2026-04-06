<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notices & Events') }}
        </h2>
    </x-slot>

    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4 align-items-center">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">{{ __('Notices & Events') }}</h4>
                        <p class="text-muted mb-0 small">{{ __('Manage announcements and upcoming events') }}</p>
                    </div>
                    <a href="{{ route('settings.notices.create') }}" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-plus-lg me-1"></i>{{ __('Create New') }}
                    </a>
                </div>
            </div>


            <div class="hr-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table hr-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">{{ __('Title') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Expiry') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th class="pe-4 text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notices as $notice)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-gray-800">{{ $notice->title }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 250px;">{{ Str::limit($notice->content, 50) }}</div>
                                    </td>
                                    <td>
                                        @if($notice->type === 'notice')
                                            <span class="badge bg-info-soft text-info">{{ __('Notice') }}</span>
                                        @else
                                            <span class="badge bg-primary-soft text-primary" style="background-color: #e0e7ff; color: #4338ca;">{{ __('Event') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($notice->is_active)
                                            <span class="badge bg-success-soft text-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary text-white">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        {{ $notice->expires_at ? $notice->expires_at->format('d M Y') : __('No Expiry') }}
                                        @if($notice->expires_at && $notice->expires_at->isPast())
                                            <br><span class="text-danger small">{{ __('Expired') }}</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $notice->creator->name }}</td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group">
                                            <a class="btn btn-sm btn-outline-primary border-0" href="{{ route('settings.notices.edit', $notice) }}" title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @php $confirmMsg = __('Are you sure you want to delete this notice?'); @endphp
                                            <form action="{{ route('settings.notices.destroy', $notice) }}" method="POST" data-confirm data-confirm-message="{{ $confirmMsg }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="{{ __('Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-megaphone fs-1 d-block mb-2 opacity-25"></i>
                                        {{ __('No notices or events found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($notices->hasPages())
                    <div class="px-4 py-3 border-top bg-light">
                        {{ $notices->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</x-app-layout>
