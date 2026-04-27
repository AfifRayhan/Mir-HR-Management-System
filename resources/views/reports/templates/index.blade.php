<x-app-layout>
    
    <div class="ui-layout">
        @include('partials.ui-sidebar')

        <main class="ui-main">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Letter Template') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Manage dynamic document templates and placeholders.') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('personnel.report-templates.create') }}" class="btn btn-sm btn-primary d-flex align-items-center">
                            <i class="bi bi-plus-lg me-4"></i>
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="ui-panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table ui-table mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">{{ __('Letter Name') }}</th>
                                <th class="py-3">{{ __('Format') }}</th>
                                <th class="py-3">{{ __('Status') }}</th>
                                <th class="pe-4 text-end py-3">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td class="ps-4">
                                        <div class="font-bold text-gray-800">{{ $template->type->name }}</div>
                                        <div class="small text-muted">ID: #{{ $template->id }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $template->format }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($template->is_active)
                                            <span class="hr-status-badge hr-status-active">Active</span>
                                        @else
                                            <span class="hr-status-badge hr-status-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('personnel.report-templates.edit', $template->id) }}" class="btn btn-sm btn-outline-primary border-0" title="{{ __('Edit') }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('personnel.report-templates.destroy', $template->id) }}" method="POST" data-confirm="true" data-confirm-message="Are you sure you want to delete this template?">
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
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-file-earmark-text display-4 mb-3 d-block opacity-25"></i>
                                            {{ __('No letter templates found. Click "Add Template" to create one.') }}
                                        </div>
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




