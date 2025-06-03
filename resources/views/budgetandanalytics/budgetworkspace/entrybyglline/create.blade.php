@extends('layouts.app')
@section('title', 'Budget Entry by GL Line')
@section('content')
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
@endsection
