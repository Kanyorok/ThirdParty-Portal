@extends('layouts.app')
@section('title', 'Add Document Template')

@section('styles')
    <!-- Bootstrap & Icons via CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <style>
        .clause-item { cursor: pointer; transition: background-color .2s; }
        .clause-item:hover { background: #f8fafc; }
        .selected-pill { background:#eef6ff; border:1px solid #bfdbfe; border-radius:.75rem; padding:.5rem .75rem; }
        .selected-pill .drag { cursor:grab; color:#94a3b8; }
        .empty-hint { border:1px dashed #e5e7eb; border-radius:.75rem; padding:1rem; text-align:center; color:#64748b; }
        .sticky-col { position: sticky; top: 1rem; }
    </style>
@endsection

@section('content')
    <div class="container my-3">
        <div class="row g-3">

            {{-- Main Form --}}
            <div class="col-lg-9">
                <form action="{{ route('legal.templates.store') }}" method="POST" id="templateForm">
                    @csrf

                    <div class="card shadow rounded-4">
                        <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 text-info"><i class="fa-solid fa-file-signature me-2"></i> Add New Template</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="previewBtn">
                                    <i class="fa-regular fa-eye me-1"></i> Preview
                                </button>
                                <a href="{{ route('legal.templates.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            {{-- Meta --}}
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" name="TemplateName" class="form-control" placeholder="e.g. Master Services Agreement" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Document Type</label>
                                    <input type="text" name="DocumentType" class="form-control" placeholder="e.g. Contract / NDA / Lease">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Version</label>
                                    <input type="number" step="0.0001" name="Version" class="form-control" value="1.0">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="Description" class="form-control" rows="2" placeholder="Short summary for internal reference..."></textarea>
                                </div>
                            </div>

                            <hr class="my-3">

                            {{-- Editor --}}
                            <div class="mb-3">
                                <label for="TemplateBody" class="form-label">Template Body</label>
                                <textarea name="TemplateBody" id="editor" class="form-control" rows="14"></textarea>
                            </div>

                            {{-- Selected Clauses (submitted in order) --}}
                            <input type="hidden" name="AttachedClauseIDs" id="AttachedClauseIDs" value="[]">

                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-success" onclick="if(this.form.checkValidity()){this.disabled = true; this.innerText = 'Saving...'; this.form.submit();}">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Template
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Right Column: Clause Library + Selected --}}
            <div class="col-lg-3">
                <div class="sticky-col">

                    {{-- Clause Library --}}
                    <div class="card shadow rounded-4 mb-3">
                        <div class="card-header bg-light py-2 px-3">
                            <h6 class="mb-0 text-info"><i class="fa-solid fa-book me-1"></i> Clause Library</h6>
                        </div>
                        <div class="card-body">

                            <div class="input-group mb-2">
                                <input type="text" id="clause-search" class="form-control" placeholder="Search clauses (title/text/type)…">
                                <button class="btn btn-outline-secondary" id="clearSearch" type="button"><i class="fa-solid fa-xmark"></i></button>
                            </div>

                            <div id="libraryState" class="text-center small text-muted my-2">Loaded {{ ($clauses ?? collect())->count() }} clauses.</div>
                            <div id="clause-results" style="max-height: 320px; overflow-y: auto;"></div>
                        </div>
                    </div>

                    {{-- Selected Clauses --}}
                    <div class="card shadow rounded-4">
                        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-info"><i class="fa-solid fa-thumbtack me-1"></i> Selected Clauses</h6>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" id="insertAll"><i class="fa-solid fa-download me-1"></i> Insert All</button>
                                <button class="btn btn-sm btn-outline-danger" id="clearSelected"><i class="fa-solid fa-trash-can me-1"></i> Clear</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="selectedList" class="d-flex flex-column gap-2">
                                <div class="empty-hint">No clauses selected yet.</div>
                            </div>
                            <div class="form-text mt-2">Order is saved to the template.</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-regular fa-eye me-2"></i> Template Preview</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="previewFrame" style="width:100%; height:70vh; border:0;"></iframe>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- CKEditor & Bootstrap JS via CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ===== Data from controller =====
        const CLAUSES = @json($clauses ?? []);

        // ===== CKEditor init =====
        let editor;
        ClassicEditor.create(document.querySelector('#editor'))
            .then(ed => editor = ed)
            .catch(console.error);

        // ===== Selected clauses state =====
        const selected = []; // {id, title, type, text}
        const selectedList = document.getElementById('selectedList');
        const hiddenIds = document.getElementById('AttachedClauseIDs');

        function renderSelected() {
            selectedList.innerHTML = '';
            if (!selected.length) {
                selectedList.innerHTML = '<div class="empty-hint">No clauses selected yet.</div>';
            } else {
                selected.forEach((c) => {
                    const row = document.createElement('div');
                    row.className = 'selected-pill d-flex align-items-center justify-content-between gap-2';
                    row.dataset.id = c.id;

                    row.innerHTML = `
          <div class="d-flex align-items-center gap-2">
            <div>
              <div class="fw-semibold">${escapeHtml(c.title)}</div>
              <div class="small text-muted">${escapeHtml(c.type || '—')}</div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-1">
            <button class="btn btn-sm btn-outline-primary insert-one" title="Insert into body">
              <i class="fa-solid fa-download"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger remove-one" title="Remove">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
        `;

                    // actions
                    row.querySelector('.insert-one').addEventListener('click', () => insertAtCursor(c.text));
                    row.querySelector('.remove-one').addEventListener('click', () => {
                        const i = selected.findIndex(x => String(x.id) === String(c.id));
                        if (i > -1) { selected.splice(i,1); syncHidden(); renderSelected(); }
                    });

                    selectedList.appendChild(row);
                });
            }
        }

        function syncHidden() { hiddenIds.value = JSON.stringify(selected.map(x => x.id)); }
        function addToSelected(item) {
            if (selected.some(x => String(x.id) === String(item.id))) return;
            selected.push(item); syncHidden(); renderSelected();
        }

        // ===== Clause Library render / filter (client-side) =====
        const searchInput = document.getElementById('clause-search');
        const libState = document.getElementById('libraryState');
        const resultsDiv = document.getElementById('clause-results');

        function applyFilter() {
            const q = (searchInput.value || '').toLowerCase().trim();

            let list = CLAUSES.slice();
            if (q) {
                list = list.filter(x =>
                    (x.Title || '').toLowerCase().includes(q) ||
                    (x.Content || '').toLowerCase().includes(q) ||
                    (x.ClauseType || '').toLowerCase().includes(q)
                );
            }

            resultsDiv.innerHTML = '';
            if (!list.length) {
                resultsDiv.innerHTML = `<div class="empty-hint">No clauses match your search.</div>`;
                libState.innerText = '0 results';
                return;
            }

            libState.innerText = `${list.length} result(s)`;
            list.forEach(clause => {
                const item = document.createElement('div');
                item.className = 'clause-item border rounded p-2 mb-2';
                const snippet = (clause.Content || '').slice(0, 140) + ((clause.Content || '').length > 140 ? '…' : '');
                item.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold">${escapeHtml(clause.Title || 'Untitled')}</div>
            <div class="small text-muted">
              ${escapeHtml(clause.ClauseType || '—')} • v${escapeHtml(clause.Version || '—')}
            </div>
          </div>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-primary insert-btn" title="Insert into body"><i class="fa-solid fa-download"></i></button>
            <button class="btn btn-sm btn-outline-success attach-btn" title="Attach to template"><i class="fa-solid fa-plus"></i></button>
          </div>
        </div>
        <div class="small text-muted mt-2">${escapeHtml(snippet)}</div>
      `;

                item.querySelector('.insert-btn').addEventListener('click', () => insertAtCursor(clause.Content || ''));
                item.querySelector('.attach-btn').addEventListener('click', () => {
                    addToSelected({
                        id: String(clause.Id),
                        title: clause.Title || 'Untitled',
                        type: clause.ClauseType || '',
                        text: clause.Content || ''
                    });
                });

                resultsDiv.appendChild(item);
            });
        }

        function insertAtCursor(text) {
            if (!editor) return;
            editor.model.change(writer => {
                const pos = editor.model.document.selection.getFirstPosition();
                writer.insertText(text, pos);
            });
        }

        searchInput.addEventListener('input', applyFilter);
        document.getElementById('clearSearch').addEventListener('click', () => { searchInput.value = ''; applyFilter(); });
        applyFilter(); // initial render

        // ===== Insert All selected into editor =====
        document.getElementById('insertAll').addEventListener('click', () => {
            if (!selected.length) return;
            insertAtCursor(selected.map(c => c.text).join('\n\n'));
        });

        // ===== Clear selected =====
        document.getElementById('clearSelected').addEventListener('click', () => {
            selected.splice(0, selected.length); syncHidden(); renderSelected();
        });

        // ===== Preview (client-side) =====
        document.getElementById('previewBtn').addEventListener('click', () => {
            const html = editor?.getData?.() || '';
            document.getElementById('previewFrame').srcdoc = `
      <html>
        <head><meta charset="utf-8"><title>Preview</title></head>
        <body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial; line-height:1.6; padding:1.5rem;">
          ${html}
        </body>
      </html>`;
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        });

        // ===== Utils =====
        function escapeHtml(str) {
            return (str ?? '').toString()
                .replace(/&/g,'&amp;')
                .replace(/</g,'&lt;')
                .replace(/>/g,'&gt;')
                .replace(/"/g,'&quot;')
                .replace(/'/g,'&#39;');
        }

        renderSelected();
    </script>
@endsection
