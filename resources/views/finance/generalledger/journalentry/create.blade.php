@extends('layouts.app')
@section('title', 'Journal Entry')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        /* Select2 full-width */
        .select2-container {
            width: 100% !important;
        }

        /* Table base */
        .je-table {
            min-width: 1700px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .je-table th, .je-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        /* Sticky headers + columns (disabled per request) */
        .je-sticky {
            position: static;
            background: #fff;
            z-index: auto;
        }

        .je-sticky-col {
            left: auto;
        }

        .je-sticky-gl {
            left: auto;
        }

        .je-table thead th.je-sticky {
            background: #f8f9fa;
            z-index: auto;
        }

        /* Column widths */
        .je-table th.col-gl, .je-table td.col-gl {
            min-width: 360px;
        }

        .je-table th.col-branch, .je-table td.col-branch {
            min-width: 220px;
        }

        .je-table th.col-dept, .je-table td.col-dept {
            min-width: 280px;
        }

        .je-table th.col-drcr, .je-table td.col-drcr {
            min-width: 120px;
            text-align: center;
        }

        .je-table th.col-amount, .je-table td.col-amount {
            min-width: 200px;
            text-align: right;
        }

        .je-table th.col-narr, .je-table td.col-narr {
            min-width: 420px;
        }

        .je-table th.col-action, .je-table td.col-action {
            min-width: 120px;
            text-align: center;
        }

        /* Inputs and selects spacing */
        .je-table .form-select,
        .je-table .form-control,
        .je-table textarea {
            padding: 0.45rem 0.65rem;
            font-size: 0.875rem;
        }

        /* Narration textarea height */
        .narration-input {
            min-height: 60px;
            resize: vertical;
        }

        /* Totals section */
        .totals-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }

        /* Match Bootstrap form-select look */
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 2px); /* same as Bootstrap form-select */
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            background-color: #fff;
        }

        /* Remove the extra arrow style */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            top: 50%;
            transform: translateY(-50%);
            right: 0.75rem;
        }

    </style>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif



    <div class="container mt-0">
        <div class="card shadow rounded-4">
            <div class="card-header bg-light py-2 px-3 d-flex align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fab fa-wpforms text-info me-1"></i> Journal Entry
                </h6>
            </div>

            <div class="card-body">
                <form method="POST" action="{{route('journalentry.store')}}" id="journalForm">
                    @csrf
                    @method('POST')

                    {{-- Journal Header --}}
                    <div class="row mb-4 g-3">
                        <div class="col-md-4">
                            <label class="form-label">Journal Date</label>
                            <input type="date" name="JournalDate" class="form-control"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <input type="text" name="Description" class="form-control"
                                   placeholder="e.g., Loan Disbursement">
                        </div>
                    </div>

                    {{-- Journal Lines --}}
                    <h5 class="border-bottom pb-2 mb-3 text-primary">🧾 Journal Lines</h5>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle table-sm je-table">
                            <thead class="table-light">
                            <tr>
                                <th class="je-sticky je-sticky-col" style="width: 56px">#</th>
                                <th class="col-gl je-sticky je-sticky-gl">GL Account</th>
                                <th class="col-branch">Branch</th>
                                <th class="col-dept">Department</th>
                                <th class="col-drcr">DR / CR</th>
                                <th class="col-amount">Amount</th>
                                <th class="col-narr">Narration</th>
                                <th class="col-action">Action</th>
                            </tr>
                            </thead>
                            <tbody id="journalBody">
                            @for ($i = 0; $i < 2; $i++)
                                <tr>
                                    <td class="line-number je-sticky je-sticky-col">{{ $i + 1 }}</td>
                                    <td class="col-gl je-sticky je-sticky-gl">
                                        <select name="GLAccount[]" class="form-select gl-account-select p-2" required>
                                            <option value="" class="p-2" selected disabled>Select GL</option>
                                            @foreach($gls as $gl)
                                                <option value="{{ $gl->Id }}"
                                                        data-code="{{ $gl->GLCode }}"
                                                        data-name="{{ $gl->GLName }}">
                                                    {{ $gl->GLCode }} ({{ $gl->GLName }})
                                                </option>
                                            @endforeach
                                        </select>

                                    </td>
                                    <td class="col-branch">
                                        <select name="Branch[]" class="form-select" required>
                                            @foreach($branches as $branch)
                                                <option value="{{$branch->Id}}">{{$branch->Name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="col-dept">
                                        <select name="Department[]" class="form-select" required>
                                            @foreach($departments as $department)
                                                <option value="{{$department->Id}}">{{$department->Name}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="col-drcr">
                                        <select name="DRCR[]" class="form-select drcr-select" required>
                                            <option value="DR">DR</option>
                                            <option value="CR">CR</option>
                                        </select>
                                    </td>
                                    <td class="col-amount">
                                        <input type="number" name="Amount[]" class="form-control amount-input text-end"
                                               step="0.01" required>
                                    </td>
                                    <td class="col-narr">
                                        <textarea name="Narration[]" class="form-control narration-input"
                                                  placeholder="Narration"></textarea>
                                    </td>
                                    <td class="col-action">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-line"
                                                title="Remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-3">
                        <button type="button" id="addRow" class="btn btn-outline-primary btn-sm">
                            + Add Line
                        </button>
                    </div>

                    <div class="totals-box alert alert-info">
                        <div>
                            <strong>Total Debit:</strong> <span id="totalDr">0.00</span> &nbsp;
                            <strong>Total Credit:</strong> <span id="totalCr">0.00</span>
                        </div>
                        <span id="balanceStatus" class="badge bg-warning text-dark fw-bold px-3 py-2">
                            Unbalanced
                        </span>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{route('journalentry.index')}}" class="btn btn-secondary">Back</a>
                        <button class="btn btn-success" id="postBtn" type="submit"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">
                            Save Journal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        (function () {
            const body = document.getElementById('journalBody');
            const totalDr = document.getElementById('totalDr');
            const totalCr = document.getElementById('totalCr');
            const postBtn = document.getElementById('postBtn');
            const balanceStatus = document.getElementById('balanceStatus');

            function updateLineNumbers() {
                if (!body) return;
                const rows = body.querySelectorAll('tr');
                rows.forEach((row, index) => {
                    const lineNumberCell = row.querySelector('.line-number');
                    if (lineNumberCell) lineNumberCell.textContent = index + 1;
                });
            }

            function calculateTotals() {
                if (!body || !totalDr || !totalCr || !balanceStatus || !postBtn) return;
                let debit = 0, credit = 0, valid = true;
                [...body.rows].forEach(row => {
                    const gl = row.querySelector('select[name="GLAccount[]"]')?.value;
                    const drcr = row.querySelector('select[name="DRCR[]"]')?.value;
                    const amt = parseFloat(row.querySelector('input[name="Amount[]"]')?.value || 0);
                    if (!gl || !drcr || amt <= 0) valid = false;
                    if (drcr === 'DR') debit += amt; else if (drcr === 'CR') credit += amt;
                });
                totalDr.textContent = debit.toFixed(2);
                totalCr.textContent = credit.toFixed(2);
                if (debit === credit && debit > 0 && valid) {
                    balanceStatus.className = 'badge bg-success';
                    balanceStatus.textContent = 'Balanced';
                    postBtn.disabled = false;
                } else {
                    balanceStatus.className = 'badge bg-danger';
                    balanceStatus.textContent = 'Unbalanced / Invalid';
                    postBtn.disabled = true;
                }
            }

            document.addEventListener('input', calculateTotals);
            document.addEventListener('change', calculateTotals);

            const addRowBtn = document.getElementById('addRow');
            if (addRowBtn) {
                addRowBtn.addEventListener('click', () => {
                    if (window.jQuery && $.fn.select2) {
                        $('.gl-account-select').select2('destroy');
                    }
                    const firstRow = body?.querySelector('tr');
                    if (!firstRow) return;
                    const clone = firstRow.cloneNode(true);
                    clone.querySelectorAll('input, select, textarea').forEach(el => {
                        if (el.tagName === 'INPUT') el.value = '';
                        if (el.tagName === 'SELECT') el.selectedIndex = 0;
                        if (el.tagName === 'TEXTAREA') el.value = '';
                    });
                    body.appendChild(clone);
                    if (window.jQuery && $.fn.select2) {
                        $('.gl-account-select').select2({
                            placeholder: 'Select GL',
                            width: '100%'
                        });
                    }
                    updateLineNumbers();
                    calculateTotals();
                });
            }

            if (body) {
                body.addEventListener('click', function (e) {
                    if (e.target.closest && e.target.closest('.remove-line') && body.rows.length > 1) {
                        e.target.closest('tr').remove();
                        updateLineNumbers();
                        calculateTotals();
                    }
                });
            }

            calculateTotals();
            updateLineNumbers();

            if (window.jQuery && $.fn.select2) {
                $('.gl-account-select').select2({
                    placeholder: 'Select GL',
                    width: '100%'
                });
            }
        })();

        if (window.jQuery && $.fn.select2) {
            $('.gl-account-select').select2({
                placeholder: 'Select GL',
                width: '100%',
                dropdownAutoWidth: true,
                templateResult: function (data) {
                    if (!data.id) return data.text;

                    let $option = $(data.element);
                    return $(
                        '<div><strong>' + $option.data('code') + '</strong> ' +
                        '<small class="text-muted">(' + $option.data('name') + ')</small></div>'
                    );
                },
                templateSelection: function (data) {
                    if (!data.id) return data.text;

                    let $option = $(data.element);
                    return $(
                        '<div><strong>' + $option.data('code') + '</strong> ' +
                        '<small>(' + $option.data('name') + ')</small></div>'
                    );
                }
            });
        }


    </script>
@endsection
