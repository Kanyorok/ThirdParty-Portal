@extends('layouts.app')
@section('title', 'Reverse Journal Entry')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        /* --------------------------
           GENERAL LAYOUT & CARD STYLE
        --------------------------- */
        body {
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1100px;
        }

        .card {
            border-radius: 1rem;
            overflow: hidden;
        }

        .card-body {
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.25rem;
            }
        }

        /* --------------------------
           SELECT2 STYLING
        --------------------------- */
        .select2-container {
            width: 100% !important;
        }

        .select2-dropdown {
            width: auto !important;
            min-width: 100% !important;
            max-width: 100% !important;
            position: absolute !important;
            left: 0 !important;
            top: 100% !important;
            z-index: 2055 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
        }

        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            background-color: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
        }

        .select2-selection__rendered {
            white-space: normal !important;
        }

        .select2-results__options {
            max-height: 250px;
            overflow-y: auto;
        }

        /* Option styling */
        .s2-option .s2-row {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .s2-option .s2-type {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            color: #fff;
        }

        /* --------------------------
           PREVIEW SECTION STYLING
        --------------------------- */
        #journalPreviewSection {
            transition: all 0.3s ease-in-out;
        }

        #journalPreviewSection iframe {
            width: 100%;
            border: 0;
            height: 65vh;
            border-radius: 0.5rem;
            background: #fff;
        }

        @media (max-width: 768px) {
            #journalPreviewSection iframe {
                height: 55vh;
            }
        }

        @media (max-width: 576px) {
            #journalPreviewSection iframe {
                height: 45vh;
            }
        }

        #previewLoader {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.85);
            z-index: 10;
            position: absolute;
            inset: 0;
        }
    </style>
@endsection

@section('content')
    <div class="container my-5">
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm rounded-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Pending Reversal Notice (shown via JS when pending journal selected) -->
        <div id="pendingReversalNotice" class="alert alert-danger shadow-sm rounded-4 d-none" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <span id="pendingReversalMessage"></span>
        </div>

        <div class="card shadow border-0">
            <div class="card-body">
                {{-- <h4 class="mb-4 fw-semibold text-primary text-center">Reverse Journal Entry</h4> --}}

                <form method="POST" action="{{ route('reversingjournal.store') }}" id="reverseJournalForm">
                    @csrf
                    @method('POST')

                    <div class="row g-4 mb-3">
                        <div class="col-md-4">
                            <label for="OriginalReferenceNumber" class="form-label fw-semibold">Original Journal Ref #</label>
                             <select name="OriginalJournalID" id="OriginalReferenceNumber"
                                 class="form-select je-ref-select" required>
                                <option value="" disabled selected>-- Select Journal Ref --</option>
                                @foreach ($journalEntries as $entry)
                                    @php
                                        $isRecurring = strtolower($entry->Type ?? 'normal') === 'recurring';
                                        $showUrl = $isRecurring
                                            ? route('recurrentjournal.show', $entry->Id)
                                            : route('journalentry.show', $entry->Id);
                                    @endphp
                                    <option value="{{ $entry->Id }}" data-url="{{ $showUrl }}"
                                        data-type="{{ ucfirst($entry->Type) }}" data-ref="{{ $entry->RefNo }}"
                                        data-desc="{{ $entry->Description ?? 'No Description' }}">
                                        {{ $entry->RefNo }} - {{ $entry->Description ?? 'No Description' }}
                                    </option>
                                @endforeach
                                 @if(!empty($pendingEntries))
                                     @foreach ($pendingEntries as $p)
                                         @php
                                             $isRecurring = strtolower($p->Type ?? 'normal') === 'recurring';
                                             $showUrl = $isRecurring
                                                 ? route('recurrentjournal.show', $p->Id)
                                                 : route('journalentry.show', $p->Id);
                                         @endphp
                                         <option value="{{ $p->Id }}" data-pending="1" data-url="{{ $showUrl }}"
                                             data-type="{{ ucfirst($p->Type) }}" data-ref="{{ $p->RefNo }}"
                                             data-desc="{{ $p->Description ?? 'No Description' }} (Pending reversal approval)">
                                             {{ $p->RefNo }} - {{ $p->Description ?? 'No Description' }}
                                         </option>
                                     @endforeach
                                 @endif
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="ReversalDate" class="form-label fw-semibold">Reversal Date</label>
                            <input type="date" name="ReversalDate" id="ReversalDate" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label for="Reason" class="form-label fw-semibold">Reason</label>
                            <input type="text" name="Reason" id="Reason" class="form-control"
                                placeholder="e.g., Accrual reversal" required>
                        </div>
                    </div>

                    @if(!empty($pendingReversals) && count($pendingReversals) > 0)
                        <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
                            <strong>Note:</strong> The following journals are pending reversal approval and are hidden from the list:
                            <div class="mt-1">
                                @foreach($pendingReversals as $ref)
                                    <span class="badge bg-secondary me-1 mb-1">{{ $ref }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Inline Original Journal Preview -->
                    <div id="journalPreviewSection" class="mt-4 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-info">Original Journal Preview</h6>
                            <div id="journalPreviewMeta" class="small text-muted"></div>
                        </div>
                        <hr class="my-2">
                        <div class="border rounded overflow-hidden position-relative">
                            <div id="previewLoader" class="d-none">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status"
                                        style="width: 3rem; height: 3rem;"></div>
                                    <div class="mt-2 small text-muted">Loading journal...</div>
                                </div>
                            </div>
                            <iframe id="journalPreviewFrame" src=""></iframe>
                        </div>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center mt-4 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        This action will automatically reverse the original journal entry lines.
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="{{ route('reversingjournal.index') }}" class="btn btn-secondary rounded-3 px-4">Back</a>
                        <button class="btn btn-danger rounded-3 px-4" id="openReverseModalBtn" type="button"
                            data-bs-toggle="modal" data-bs-target="#reverseConfirmModal">Reverse Journal</button>
                    </div>
                </form>

                <!-- Confirmation Modal -->
                <div class="modal fade" id="reverseConfirmModal" tabindex="-1" aria-labelledby="reverseConfirmLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 shadow">
                            <div class="modal-header bg-light border-0">
                                <h5 class="modal-title text-danger" id="reverseConfirmLabel">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Reverse Journal
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-warning small">
                                    You are about to reverse the selected journal entry. This will create reversing
                                    lines and cannot be undone easily.
                                </div>
                                <div class="small">
                                    <div>Original Reference: <strong id="confirmRef">—</strong></div>
                                    <div>Type: <strong id="confirmType">—</strong></div>
                                    <div>Reversal Date: <strong id="confirmDate">—</strong></div>
                                    <div>Reason: <strong id="confirmReason">—</strong></div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary rounded-3"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-danger rounded-3" id="confirmReverseBtn">Proceed</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        (function() {
            if (window.jQuery && $.fn.select2) {
                $('#OriginalReferenceNumber.je-ref-select').select2({
                    placeholder: '-- Select Journal Ref --',
                    width: 'resolve',
                    dropdownParent: $('#OriginalReferenceNumber').parent(),
                    dropdownAutoWidth: true,
                    escapeMarkup: function(m) {
                        return m;
                    },
                    templateResult: function(data) {
                        if (!data.id) return data.text;
                        let $option = $(data.element);
                        let ref = $option.data('ref') || '';
                        let type = ($option.data('type') || '').toString();
                        let desc = $option.data('desc') || '';
                        let isPending = !!$option.data('pending');
                        let badge = '';
                        if (type) {
                            const norm = type.toLowerCase();
                            const cls = norm === 'recurring' ? 'bg-info' : (norm === 'reversing' ? 'bg-danger' :
                                'bg-secondary');
                            badge = '<span class="s2-type ' + cls + '">' + type + '</span>';
                        }
                        return $('<div class="s2-option ' + (isPending ? 'opacity-75' : '') + '">\
                                    <div class="s2-row">\
                                        <span class="s2-ref fw-semibold">' + ref + '</span>\
                                        ' + badge + (isPending ? '<span class="badge bg-danger ms-2">Pending approval</span>' : '') + '\
                                    </div>\
                                    <div class="s2-desc text-muted small">' + desc + '</div>\
                                </div>');
                    },
                    templateSelection: function(data) {
                        if (!data.id) return data.text;
                        let $option = $(data.element);
                        let ref = $option.data('ref') || '';
                        let type = ($option.data('type') || '').toString();
                        let isPending = !!$option.data('pending');
                        let badge = '';
                        if (type) {
                            const norm = type.toLowerCase();
                            const cls = norm === 'recurring' ? 'bg-info' : (norm === 'reversing' ? 'bg-danger' :
                                'bg-secondary');
                            badge = '<span class="ms-2 badge ' + cls + '" style="font-size:.7rem;">' + type + '</span>' + (isPending ? '<span class="ms-2 badge bg-danger" style="font-size:.7rem;">Pending approval</span>' : '');
                        }
                        return $('<div class="d-flex align-items-center">\
                                    <strong>' + ref + '</strong>' + badge + '\
                                </div>');
                    }
                });
            }

            const form = document.getElementById('reverseJournalForm');
            const refSelect = document.getElementById('OriginalReferenceNumber');
            const dateInput = document.getElementById('ReversalDate');
            const reasonInput = document.getElementById('Reason');
            const confirmBtn = document.getElementById('confirmReverseBtn');
            const openBtn = document.getElementById('openReverseModalBtn');
            const previewSection = document.getElementById('journalPreviewSection');
            const previewMeta = document.getElementById('journalPreviewMeta');
            const previewFrame = document.getElementById('journalPreviewFrame');
            const previewLoader = document.getElementById('previewLoader');

            function populateConfirmDetails() {
                const opt = refSelect && refSelect.options[refSelect.selectedIndex];
                const ref = opt ? (opt.getAttribute('data-ref') || opt.textContent) : '—';
                const type = opt ? (opt.getAttribute('data-type') || 'Normal') : '—';
                const date = dateInput ? (dateInput.value || '—') : '—';
                const reason = reasonInput ? (reasonInput.value || '—') : '—';
                document.getElementById('confirmRef').textContent = ref;
                document.getElementById('confirmType').textContent = type;
                document.getElementById('confirmDate').textContent = date;
                document.getElementById('confirmReason').textContent = reason;
            }

            openBtn && openBtn.addEventListener('click', populateConfirmDetails);

            confirmBtn && confirmBtn.addEventListener('click', function() {
                if (!form) return;
                if (form.checkValidity()) {
                    confirmBtn.disabled = true;
                    confirmBtn.textContent = 'Reversing...';
                    form.submit();
                } else {
                    form.reportValidity && form.reportValidity();
                }
            });

            function loadPreview() {
                const opt = this.options[this.selectedIndex];
                const ref = opt ? (opt.getAttribute('data-ref') || '') : '';
                const type = (opt ? (opt.getAttribute('data-type') || 'Normal') : 'Normal');
                const url = (opt ? (opt.getAttribute('data-url') || '') : '');
                const isPending = opt ? (opt.getAttribute('data-pending') === '1') : false;

                const notice = document.getElementById('pendingReversalNotice');
                const noticeMessage = document.getElementById('pendingReversalMessage');
                
                if (isPending) {
                    // show notice at top, do not load preview
                    previewSection.classList.add('d-none');
                    if (notice && noticeMessage) {
                        noticeMessage.textContent = `Journal ${ref} is pending reversal approval.`;
                        notice.classList.remove('d-none');
                        // Scroll to notice
                        notice.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    if (openBtn) { 
                        openBtn.disabled = true; 
                        openBtn.title = 'Journal pending reversal approval'; 
                    }
                    return;
                } else {
                    if (notice && noticeMessage) {
                        notice.classList.add('d-none');
                        noticeMessage.textContent = '';
                    }
                    if (openBtn) { 
                        openBtn.disabled = false; 
                        openBtn.title = ''; 
                    }
                }

                if (!url) return;

                previewMeta.textContent = `Ref: ${ref} • Type: ${type}`;
                previewLoader.classList.remove('d-none');
                previewFrame.onload = function() {
                    previewLoader.classList.add('d-none');
                };
                previewFrame.src = url;
                previewSection.classList.remove('d-none');
            }

            refSelect && refSelect.addEventListener('change', loadPreview);
            if (window.jQuery) {
                $('#OriginalReferenceNumber').on('select2:select', function() {
                    loadPreview.call(refSelect);
                });
            }
        })();
    </script>
@endsection
