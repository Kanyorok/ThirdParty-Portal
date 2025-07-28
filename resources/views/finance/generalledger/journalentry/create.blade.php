@extends('layouts.app')
@section('title', 'Journal Entry')

@section('content')
    <div class="container mt-1">
        <div class="card shadow rounded-4">
            <div class="card-header bg-light py-1 px-3">
                <h6 class="mb-0 text-muted"><i class="fab fa-wpforms text-info"></i></h6>
            </div>
            <div class="card-body">
                <form method="POST" action="#" id="journalForm">
                    @csrf

                    {{-- Journal Header --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Journal Date</label>
                            <input type="date" name="JournalDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
{{--                        <div class="col-md-3">--}}
{{--                            <label class="form-label">Reference Number</label>--}}
{{--                            <input type="text" name="ReferenceNumber" class="form-control" placeholder="Optional or system-generated">--}}
{{--                        </div>--}}
{{--                        <div class="col-md-3">--}}
{{--                            <label class="form-label">Transaction Type</label>--}}
{{--                            <select name="TransactionTypeID" class="form-select" required>--}}
{{--                                <option value="">-- Select --</option>--}}
{{--                                <option value="JE">JE – Manual</option>--}}
{{--                                <option value="REVJ">REVJ – Reversing</option>--}}
{{--                                <option value="RECUR">RECUR – Recurring</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="Description" class="form-control" placeholder="e.g., Loan Disbursement">
                        </div>
                    </div>

                    {{-- Journal Lines --}}
                    <h5 class="border-bottom pb-2 mb-3 text-primary">🧾 Journal Lines</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-sm">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>GL Account</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>DR / CR</th>
                                <th>Amount</th>
                                <th>Narration</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody id="journalBody">
                            @for ($i = 0; $i < 2; $i++)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <select name="GLAccount[]" class="form-select" required>
                                            <option value="">Select</option>
                                            <option value="1000">1000 - Cash & Bank</option>
                                            <option value="1100">1100 - Cash - HQ</option>
                                            <option value="2000">2000 - Accounts Payable</option>
                                            <option value="3000">3000 - Revenue</option>
                                            <option value="4000">4000 - Salary Expenses</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="Branch[]" class="form-select" required>
                                            <option value="001">001 - HQ</option>
                                            <option value="002">002 - Nairobi</option>
                                            <option value="003">003 - Mombasa</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="Department[]" class="form-select" required>
                                            <option value="100">100 - Finance</option>
                                            <option value="200">200 - HR</option>
                                            <option value="300">300 - Operations</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="DRCR[]" class="form-select drcr-select" required>
                                            <option value="DR">DR</option>
                                            <option value="CR">CR</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="Amount[]" class="form-control amount-input" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="text" name="Narration[]" class="form-control" placeholder="Narration">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-3">
                        <button type="button" id="addRow" class="btn btn-outline-primary btn-sm">+ Add Line</button>
                    </div>

                    <div class="alert alert-info rounded-3">
                        <strong>Total Debit:</strong> <span id="totalDr">0.00</span> &nbsp;
                        <strong>Total Credit:</strong> <span id="totalCr">0.00</span> &nbsp;
                        <span id="balanceStatus" class="badge bg-warning text-dark fw-bold px-3 py-2" style="font-size: 0.75rem;">
                            Unbalanced
                        </span>

                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="#" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success" id="postBtn" disabled>Post Journal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const body = document.getElementById('journalBody');
        const totalDr = document.getElementById('totalDr');
        const totalCr = document.getElementById('totalCr');
        const postBtn = document.getElementById('postBtn');
        const balanceStatus = document.getElementById('balanceStatus');

        function calculateTotals() {
            let debit = 0, credit = 0, valid = true;

            [...body.rows].forEach(row => {
                const gl = row.querySelector('select[name="GLAccount[]"]')?.value;
                const drcr = row.querySelector('select[name="DRCR[]"]')?.value;
                const amt = parseFloat(row.querySelector('input[name="Amount[]"]')?.value || 0);

                if (!gl || !drcr || amt <= 0) valid = false;

                if (drcr === 'DR') debit += amt;
                else if (drcr === 'CR') credit += amt;
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

        document.getElementById('addRow').addEventListener('click', () => {
            const firstRow = body.querySelector('tr');
            const clone = firstRow.cloneNode(true);

            clone.querySelectorAll('input, select').forEach(el => {
                if (el.tagName === 'INPUT') el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            body.appendChild(clone);
            calculateTotals();
        });

        body.addEventListener('click', function (e) {
            if (e.target.closest('.remove-line') && body.rows.length > 1) {
                e.target.closest('tr').remove();
                calculateTotals();
            }
        });

        calculateTotals(); // initial calc
    </script>
@endsection
