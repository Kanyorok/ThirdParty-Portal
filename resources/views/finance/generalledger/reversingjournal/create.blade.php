@extends('layouts.app')
@section('title', 'Reverse Journal Entry')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }

        /* match bootstrap */
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            background-color: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            top: 50%;
            transform: translateY(-50%);
            right: 0.75rem;
        }
    </style>
@endsection

@section('content')
    <div class="container mt-5">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow border-0">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Reverse Journal Entry</h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{route('reversingjournal.store')}}" id="reverseJournalForm">
                    @csrf
                    @method('POST')
                    <div class="row g-4 mb-3">
                        <div class="col-md-4">
                            <label for="OriginalReferenceNumber" class="form-label">Original Journal Ref #</label>
                            <select name="OriginalJournalID" id="OriginalReferenceNumber"
                                    class="form-select je-ref-select" required>
                                <option value="" disabled selected>-- Select Journal Ref --</option>
                                @foreach ($journalEntries as $entry)
                                    <option value="{{ $entry->Id }}" data-ref="{{ $entry->RefNo }}"
                                            data-desc="{{ $entry->Description ?? 'No Description' }}">{{ $entry->RefNo }}
                                        - {{ $entry->Description ?? 'No Description' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="ReversalDate" class="form-label">Reversal Date</label>
                            <input type="date" name="ReversalDate" id="ReversalDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label for="Reason" class="form-label">Reason</label>
                            <input type="text" name="Reason" id="Reason" class="form-control" placeholder="e.g., Accrual reversal" required>
                        </div>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        This action will automatically reverse the original journal entry lines.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{route('reversingjournal.index')}}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-danger" id="openReverseModalBtn" type="button" data-bs-toggle="modal"
                                data-bs-target="#reverseConfirmModal">Reverse Journal
                        </button>
                    </div>
                </form>

                <!-- Confirmation Modal -->
                <div class="modal fade" id="reverseConfirmModal" tabindex="-1" aria-labelledby="reverseConfirmLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content rounded-4 shadow">
                            <div class="modal-header bg-light border-0">
                                <h5 class="modal-title text-danger" id="reverseConfirmLabel"><i
                                        class="fas fa-exclamation-triangle me-2"></i>Confirm Reverse Journal</h5>
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
                                    <div>Reversal Date: <strong id="confirmDate">—</strong></div>
                                    <div>Reason: <strong id="confirmReason">—</strong></div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel
                                </button>
                                <button type="button" class="btn btn-danger" id="confirmReverseBtn">Proceed</button>
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
        (function () {
            if (window.jQuery && $.fn.select2) {
                $('#OriginalReferenceNumber.je-ref-select').select2({
                    placeholder: '-- Select Journal Ref --',
                    width: '100%',
                    dropdownAutoWidth: true,
                    templateResult: function (data) {
                        if (!data.id) return data.text;
                        let $option = $(data.element);
                        return $('<div><strong>' + $option.data('ref') + '</strong> <small class="text-muted">(' + $option.data('desc') + ')</small></div>');
                    },
                    templateSelection: function (data) {
                        if (!data.id) return data.text;
                        let $option = $(data.element);
                        return $('<div><strong>' + $option.data('ref') + '</strong> <small>(' + $option.data('desc') + ')</small></div>');
                    }
                });
            }

            const form = document.getElementById('reverseJournalForm');
            const refSelect = document.getElementById('OriginalReferenceNumber');
            const dateInput = document.getElementById('ReversalDate');
            const reasonInput = document.getElementById('Reason');
            const confirmBtn = document.getElementById('confirmReverseBtn');
            const openBtn = document.getElementById('openReverseModalBtn');

            function populateConfirmDetails() {
                const opt = refSelect && refSelect.options[refSelect.selectedIndex];
                const ref = opt ? (opt.getAttribute('data-ref') || opt.textContent) : '—';
                const date = dateInput ? (dateInput.value || '—') : '—';
                const reason = reasonInput ? (reasonInput.value || '—') : '—';
                const refEl = document.getElementById('confirmRef');
                const dateEl = document.getElementById('confirmDate');
                const reasonEl = document.getElementById('confirmReason');
                if (refEl) refEl.textContent = ref;
                if (dateEl) dateEl.textContent = date;
                if (reasonEl) reasonEl.textContent = reason;
            }

            openBtn && openBtn.addEventListener('click', populateConfirmDetails);

            confirmBtn && confirmBtn.addEventListener('click', function () {
                if (!form) return;
                if (form.checkValidity()) {
                    confirmBtn.disabled = true;
                    confirmBtn.textContent = 'Reversing...';
                    form.submit();
                } else {
                    // Trigger browser validation UI
                    form.reportValidity && form.reportValidity();
                }
            });
        })();
    </script>
@endsection
