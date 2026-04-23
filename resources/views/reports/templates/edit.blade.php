<x-app-layout>
    @push('styles')
    @vite(['resources/css/custom-hr-dashboard.css'])
    <style>
        .ck-editor__editable_inline {
            min-height: 500px;
        }
        .tag-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
        }
        .tag-btn {
            font-size: 0.75rem;
            margin: 0.2rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tag-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
    @endpush

    <div class="hr-layout">
        @include('partials.hr-sidebar')

        <main class="hr-main">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('personnel.report-templates.index') }}" class="text-secondary text-decoration-none mb-2 d-inline-block small hover-opacity">
                            <i class="bi bi-arrow-left"></i> {{ __('Back to Templates') }}
                        </a>
                        <h5 class="mb-1 text-2xl font-bold">{{ __('Edit Report Template') }}</h5>
                        <p class="mb-0 text-gray-500">{{ __('Modify the template structure and dynamic placeholders.') }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('personnel.report-templates.update', $template->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="hr-panel shadow-sm border-0 rounded-4">
                            <div class="mb-3">
                                <label for="content" class="form-label font-bold text-gray-700">{{ __('Template Content') }}</label>
                                <textarea name="content" id="editor" class="form-control">{{ old('content', $template->content) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="hr-panel mb-4 shadow-sm border-0 rounded-4">
                            <h6 class="font-bold text-gray-800 mb-3"><i class="bi bi-info-circle me-2 text-success"></i>{{ __('Template Settings') }}</h6>
                            <div class="mb-3">
                                <label for="report_template_type_id" class="form-label small font-bold text-muted">{{ __('Report Type') }}</label>
                                <select name="report_template_type_id" id="report_template_type_id" class="form-select rounded-3">
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" @selected($template->report_template_type_id == $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="format" class="form-label small font-bold text-muted">{{ __('Format/Language') }}</label>
                                <select name="format" id="format" class="form-select rounded-3">
                                    @foreach($formats as $format)
                                        <option value="{{ $format }}" @selected($template->format == $format)>{{ $format }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked($template->is_active)>
                                    <label class="form-check-label font-bold" for="is_active">{{ __('Active Template') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="tag-panel shadow-sm border-0 rounded-4">
                            <h6 class="font-bold text-gray-800 mb-2"><i class="bi bi-tags me-2 text-success"></i>{{ __('Dynamic Tags') }}</h6>
                            <p class="text-xs text-muted mb-2">{{ __('Click a tag to insert it into the editor at the cursor position.') }}</p>
                            
                            <div class="input-group input-group-sm mb-3">
                                <input type="text" id="new-tag-input" class="form-control" placeholder="{{ __('e.g. #NewTag') }}">
                                <button class="btn btn-outline-primary" type="button" id="add-tag-btn">
                                    <i class="bi bi-plus"></i> {{ __('Add') }}
                                </button>
                            </div>
                            
                            <div id="tag-container" class="d-flex flex-wrap gap-1">
                                @php
                                    $tags = preg_split('/[\s,;]+|&nbsp;?/', $template->type->key_tags, -1, PREG_SPLIT_NO_EMPTY);
                                @endphp
                                @foreach($tags as $tag)
                                    <button type="button" class="btn btn-secondary-soft text-secondary btn-sm tag-btn rounded-pill border-0" data-tag="{{ $tag }}">
                                        {{ $tag }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 font-bold py-2 shadow-sm">
                                <i class="bi bi-save me-2"></i> {{ __('Save Changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>

    @push('scripts')
    <!-- Load CKEditor 5 Classic from CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let activeEditor;

            ClassicEditor
                .create(document.querySelector('#editor'), {
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                            'outdent', 'indent', '|',
                            'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo'
                        ]
                    }
                })
                .then(editor => {
                    activeEditor = editor;
                    console.log('CKEditor initialized');
                })
                .catch(error => {
                    console.error(error);
                });

            // Named function for inserting tag
            function insertTag(tag) {
                if (activeEditor) {
                    const model = activeEditor.model;
                    model.change(writer => {
                        writer.insertText(tag, model.document.selection.getFirstPosition());
                    });
                    activeEditor.editing.view.focus();
                }
            }

            // Bind existing tags
            document.querySelectorAll('.tag-btn').forEach(button => {
                button.addEventListener('click', function() {
                    insertTag(this.getAttribute('data-tag'));
                });
            });

            // Add new manual tags
            document.getElementById('add-tag-btn').addEventListener('click', function() {
                const input = document.getElementById('new-tag-input');
                let tag = input.value.trim();
                
                if (tag) {
                    if (!tag.startsWith('#')) {
                        tag = '#' + tag;
                    }
                    
                    // Add to UI
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-secondary-soft text-secondary btn-sm tag-btn rounded-pill border-0';
                    btn.dataset.tag = tag;
                    btn.textContent = tag;
                    btn.onclick = () => insertTag(tag);
                    document.getElementById('tag-container').appendChild(btn);
                    
                    // Add hidden input to form
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'new_tags[]';
                    hiddenInput.value = tag;
                    document.querySelector('form').appendChild(hiddenInput);
                    
                    input.value = '';
                }
            });

            // Add on enter key
            document.getElementById('new-tag-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('add-tag-btn').click();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
