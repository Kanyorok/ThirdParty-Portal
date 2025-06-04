@extends('layouts.app')
@section('title', 'Add Budget Entry')
@section('content')
<<<<<<< HEAD
<div class="card mt-4">
    <div class="card-header bg-info text-white">➕ Add Budget Line Entry</div>
    <div class="card-body">
        <form>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Budget Period</label>
                    <select class="form-select">
                        <option selected>FY2025-Q1</option>
                        <option>FY2025-Q2</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Branch</label>
                    <select class="form-select">
                        <option selected>Main Branch</option>
                        <option>Westlands Branch</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Budget Line</label>
                <select class="form-select">

                        <option>Salaries – Staff Costs</option>
                        <option>Marketing Expense</option>
                        <option>Loan Interest Income</option>
                        <option>Non Funded Income</option>
                        <option>Fixed Deposit Interest Expense</option>
                        <option>Savings Deposit Interest Expense</option>
                    
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Entry Method</label>
                <select class="form-select" id="entryMethodSelect">
                    <option selected>Manual</option>
                    <option>Driver-Based</option>
                </select>
            </div>

            <!-- Manual Entry -->
            <div class="manual-entry">
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" class="form-control" placeholder="e.g. 500000">
                </div>
            </div>

            <!-- Driver-Based Entry -->
            <div class="driver-entry" style="display: none;">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Product</label>
                        <select class="form-select">
                            <option selected>Consumer Loan</option>
                            <option>Agri Loan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Driver KPI Type</label>
                        <select class="form-select">
                            <option selected>Loan</option>
                            <option>Deposit</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Projected Amount (KPI)</label>
                        <input type="text" class="form-control" readonly value="10,000,000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Applied Rate (%)</label>
                        <input type="text" class="form-control" readonly value="11.00%">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Computed Budget Value</label>
                    <input type="text" class="form-control" readonly value="1,100,000">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks (optional)</label>
                <textarea class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Save Budget Line</button>
        </form>
    </div>
</div>

<script>
    const methodSelect = document.getElementById('entryMethodSelect');
    const manualEntry = document.querySelector('.manual-entry');
    const driverEntry = document.querySelector('.driver-entry');

    methodSelect.addEventListener('change', function () {
        if (this.value === 'Driver-Based') {
            manualEntry.style.display = 'none';
            driverEntry.style.display = 'block';
        } else {
            manualEntry.style.display = 'block';
            driverEntry.style.display = 'none';
        }
    });
</script>
=======
    <div class="card p-4">
        <h5>🧾 Budget Entry by GL Line</h5>
        <p class="text-muted">Input monthly budgeted amounts per budget line (GL-based). Choose manual entry or
            auto-calculate from drivers.</p>

        <div class="mb-3 row align-items-center">
            <label for="branch" class="col-sm-2 col-form-label">Branch</label>
            <div class="col-sm-4">
                <select class="form-select" id="branch">
                    <option selected disabled>Select Branch</option>
                    <option>Central Branch</option>
                    <option>West Branch</option>
                </select>
            </div>
        </div>

        <div class="mb-3 row align-items-center">
            <label for="branch" class="col-sm-2 col-form-label">Scenario</label>
            <div class="col-sm-4">
                <select class="form-select" id="scenario">
                    <option>Base Case</option>
                    <option>Best Case</option>
                    <option>Worst Case</option>
                </select>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>Budget Line</th>
                    <th>Period</th>
                    <th>Entry Mode</th>
                    <th>Amount</th>
                    <th>Driver Used</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <select class="form-select">
                            <option>Interest Income – Loans</option>
                            <option>Interest Expense – Deposits</option>
                            <option>Commission Income</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select">
                            <option>Jan-2025</option>
                            <option>Feb-2025</option>
                            <option>Mar-2025</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select">
                            <option>Manual</option>
                            <option>Driver-Based</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control" placeholder="e.g., 1,200,000"/></td>
                    <td>
                        <select class="form-select">
                            <option>Loan Book Growth</option>
                            <option>Deposit Growth</option>
                            <option>Avg Lending Rate</option>
                        </select>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="mb-2 d-flex justify-content-between">
            <button class="btn btn-secondary">➕ Add Row</button>
            <button class="btn btn-primary">💾 Save Entries</button>
        </div>
    </div>
>>>>>>> feature/newMenu-dev
@endsection
