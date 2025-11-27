@extends('layouts.app')
@section('title', 'Edit Legal Draft')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"/>
    <style>
        .clause-item {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .clause-item:hover {
            background-color: #f1f1f1;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- Draft Editor Section -->
        <div class="col-md-9">
            <form action="{{ route('legal.drafts.update', $draft->ID) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow p-4 rounded-4 mb-4">
                    <h4 class="mb-4">✏️ Edit Legal Draft</h4>

                    <div class="mb-3">
                        <label for="DraftTitle" class="form-label">Draft Title</label>
                        <input type="text" name="DraftTitle" class="form-control" required
                               value="{{ old('DraftTitle', $draft->DraftTitle) }}">
                    </div>

                    <div class="mb-3">
                        <label for="DocumentType" class="form-label">Document Type</label>
                        <input type="text" name="DocumentType" class="form-control"
                               value="{{ old('DocumentType', $draft->DocumentType) }}">
                    </div>

                    <div class="mb-3">
                        <label for="Description" class="form-label">Description</label>
                        <textarea name="Description" class="form-control"
                                  rows="2">{{ old('Description', $draft->Description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="Content" class="form-label">Draft Content</label>
                        <textarea id="editor" name="Content" class="form-control"
                                  rows="10">{!! old('Content', $draft->Content) !!}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success">💾 Update Draft</button>
                </div>
            </form>
        </div>

        <!-- Clause Picker Sidebar -->
        <div class="col-md-3">
            <div class="card shadow p-3 rounded-4">
                <h5 class="mb-3">📚 Clause Library</h5>
                <input type="text" id="clause-search" class="form-control mb-2" placeholder="Search clause...">
                <div id="clause-results" style="max-height: 500px; overflow-y: auto;">
                    <p class="text-muted">Start typing to load clauses...</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        let editor;
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: [
                    'heading', '|', 'bold', 'italic', 'underline', 'link',
                    '|', 'bulletedList', 'numberedList',
                    '|', 'undo', 'redo', 'blockQuote'
                ]
            })
            .then(ed => editor = ed)
            .catch(error => console.error(error));

        document.getElementById('clause-search').addEventListener('input', function () {
            const q = this.value;
            if (q.length < 2) return;

            fetch(`/legal/drafts/clauses/list?q=${encodeURIComponent(q)}`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('clause-results');
                    container.innerHTML = '';
                    data.forEach(clause => {
                        const clauseDiv = document.createElement('div');
                        clauseDiv.classList.add('clause-item', 'border', 'rounded', 'p-2', 'mb-2');
                        clauseDiv.innerHTML = `
                        <strong>${clause.Title}</strong>
                        <p class="text-muted small">${clause.Content.substring(0, 100)}...</p>
                        <button class="btn btn-sm btn-outline-primary insert-btn" data-clause="${clause.Content.replace(/"/g, '&quot;')}">Insert</button>
                            `;
                        container.appendChild(clauseDiv);
                    });

                    document.querySelectorAll('.insert-btn').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const text = this.getAttribute('data-clause');
                            insertClause(text);
                        });
                    });
                });
        });

        function insertClause(text) {
            editor.model.change(writer => {
                const insertPosition = editor.model.document.selection.getFirstPosition();
                writer.insertText(text, insertPosition);
            });
        }
    </script>
@endsection
