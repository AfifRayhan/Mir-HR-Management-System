<x-app-layout>
    @push('styles')
    <style>
        .mode-toggle-btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            border: 1.5px solid #6366f1;
            color: #6366f1;
            background: transparent;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .mode-toggle-btn.active-insert {
            background: #6366f1;
            color: #fff;
        }
        #insert_hint {
            background: #eef2ff;
            border-left: 3px solid #6366f1;
            border-radius: 6px;
            padding: 0.6rem 0.9rem;
            font-size: 0.82rem;
            color: #4338ca;
        }
    </style>
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('personnel.designations.index') }}">Designations</a></li>
                                <li class="breadcrumb-item active">{{ isset($designation) ? 'Edit' : 'Create' }} Designation</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-0 text-gray-800">{{ isset($designation) ? 'Edit' : 'Create' }} Designation</h1>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 col-lg-6">
                        <div class="hr-panel">
                            <form action="{{ isset($designation) ? route('personnel.designations.update', $designation) : route('personnel.designations.store') }}" method="POST">
                                @csrf
                                @if(isset($designation))
                                @method('PUT')
                                @endif

                                <input type="hidden" name="insert_mode" id="insert_mode" value="0">

                                <div class="mb-3">
                                    <label for="name" class="form-label">Designation Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $designation->name ?? '') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label for="short_name" class="form-label">Short Name</label>
                                    <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name', $designation->short_name ?? '') }}">
                                    @error('short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label for="priority" class="form-label mb-0">Priority</label>
                                        @if(!isset($designation))
                                        <button type="button" id="toggleModeBtn" class="mode-toggle-btn">
                                            Switch to: Insert at Priority
                                        </button>
                                        @endif
                                    </div>

                                    <input type="number" min="1"
                                           class="form-control @error('priority') is-invalid @enderror"
                                           id="priority" name="priority"
                                           value="{{ old('priority', $designation->priority ?? '') }}"
                                           placeholder="e.g. 4">
                                    @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror

                                    <div id="insert_hint" class="mt-2 d-none">
                                        <strong>Insert at Priority mode:</strong> Designations currently at this priority level and below will each be shifted down by +1 to make room for the new entry.
                                        <br><em>Example: inserting at 4 → existing 4,5,6… become 5,6,7…</em>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Save Designation</button>
                                    <a href="{{ route('personnel.designations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn        = document.getElementById('toggleModeBtn');
            const modeInput  = document.getElementById('insert_mode');
            const hint       = document.getElementById('insert_hint');

            if (!btn) return; // edit mode — toggle not shown

            btn.addEventListener('click', function () {
                const isInsert = modeInput.value === '1';

                if (isInsert) {
                    // Switch back to Priority Order
                    modeInput.value = '0';
                    btn.textContent = 'Switch to: Insert at Priority';
                    btn.classList.remove('active-insert');
                    hint.classList.add('d-none');
                } else {
                    // Switch to Insert at Priority
                    modeInput.value = '1';
                    btn.textContent = 'Switch to: Priority Order';
                    btn.classList.add('active-insert');
                    hint.classList.remove('d-none');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
