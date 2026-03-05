<x-app-layout>
    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0 text-gray-800">Office Times</h1>
                        <a href="{{ route('personnel.office-times.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Add Shift
                        </a>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="hr-panel">
                    <div class="table-responsive">
                        <table class="hr-table">
                            <thead>
                                <tr>
                                    <th>Shift Name</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($officeTimes as $time)
                                <tr>
                                    <td>{{ $time->shift_name }}</td>
                                    <td>{{ $time->start_time }}</td>
                                    <td>{{ $time->end_time }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('personnel.office-times.edit', $time) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('personnel.office-times.destroy', $time) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No shifts found.</td>
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