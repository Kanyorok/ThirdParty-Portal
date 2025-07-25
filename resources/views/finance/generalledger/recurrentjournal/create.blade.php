@extends('layouts.app')
@section('title', 'Recurring Journal Setup')

@section('content')
    <div class="container mt-4">
        <div class="card shadow rounded-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">🔁 Recurring Journal Setup</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="#" id="recurringJournalForm">
                    @csrf

                    {{-- Header --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="StartDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference Name</label>
                            <input type="text" name="ReferenceName" class="form-control" placeholder="e.g., Monthly Rent" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Frequency</label>
                            <select name="Frequency" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Annually">Annually</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Description</label>
                            <input type="text" name="Description" class="form-control" placeholder="Optional">
                        </div>
                    </div>

                    {{-- Recurring Lines --}}
                    <h5 class="border-bottom pb-2 mb-3 text-primary">📋 Recurring Journal Lines</h5>

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
                            <tbody id="recurringBody">
                            @for ($i = 0; $i < 3; $i++)
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
                        <span id="balanceStatus" class="badge bg-warning text-dark">Unbalanced</span>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="#" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success" id="saveBtn" disabled>Save Recurring Journal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const recurringBody = document.getElementById('recurringBody');
        const totalDr = document.getElementById('totalDr');
        const totalCr = document.getElementById('totalCr');
        const saveBtn = document.getElementById('saveBtn');
        const balanceStatus = document.getElementById('balanceStatus');

        function calculateRecurringTotals() {
            let debit = 0, credit = 0, valid = true;

            [...recurringBody.rows].forEach(row => {
                const gl = row.querySelector('select[name="GLAccount[]"]').value;
                const drcr = row.querySelector('select[name="DRCR[]"]').value;
                const amt = parseFloat(row.querySelector('input[name="Amount[]"]').value) || 0;

                if (!gl || !drcr || amt <= 0) valid = false;

                if (drcr === 'DR') debit += amt;
                else if (drcr === 'CR') credit += amt;
            });

            totalDr.textContent = debit.toFixed(2);
            totalCr.textContent = credit.toFixed(2);

            if (debit === credit && debit > 0 && valid) {
                balanceStatus.className = 'badge bg-success';
                balanceStatus.textContent = 'Balanced';
                saveBtn.disabled = false;
            } else {
                balanceStatus.className = 'badge bg-danger';
                balanceStatus.textContent = 'Unbalanced / Invalid';
                saveBtn.disabled = true;
            }
        }

        document.addEventListener('input', calculateRecurringTotals);
        document.addEventListener('change', calculateRecurringTotals);

        document.getElementById('addRow').addEventListener('click', () => {
            const firstRow = recurringBody.querySelector('tr');
            const clone = firstRow.cloneNode(true);

            clone.querySelectorAll('input, select').forEach(el => {
                if (el.tagName === 'INPUT') el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });

            recurringBody.appendChild(clone);
            calculateRecurringTotals();
        });

        recurringBody.addEventListener('click', function (e) {
            if (e.target.closest('.remove-line') && recurringBody.rows.length > 1) {
                e.target.closest('tr').remove();
                calculateRecurringTotals();
            }
        });

        calculateRecurringTotals(); // Initial run
    </script>
@endsection
