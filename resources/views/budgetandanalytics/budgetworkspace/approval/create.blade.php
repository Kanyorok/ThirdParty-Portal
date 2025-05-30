@extends('layouts.app')
@section('title', 'Review Submitted Budget Lines')
@section('content')

    <div class="card p-4">
        <h5>🔍 Review Submitted Budget Lines</h5>
        <p class="text-muted">Inspect all submitted budget lines from the selected branch and scenario. You can approve
            or return for revision.</p>

        <div class="mb-3">
            <label class="form-label">Branch</label>
            <input type="text" class="form-control" value="Central Branch" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Scenario</label>
            <input type="text" class="form-control" value="Base Case" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Period</label>
            <input type="text" class="form-control" value="2025" readonly>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Budget Line</th>
                    <th>Period</th>
                    <th>Amount (KES)</th>
                    <th>Entry Mode</th>
                    <th>Driver Used</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Interest Income – Loans</td>
                    <td>Jan-2025</td>
                    <td>1,200,000</td>
                    <td>Driver-Based</td>
                    <td>Loan Book Growth</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Interest Expense – Deposits</td>
                    <td>Jan-2025</td>
                    <td>600,000</td>
                    <td>Manual</td>
                    <td>–</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <label for="reviewComment" class="form-label">Review Comments</label>
            <textarea class="form-control" id="reviewComment" rows="3"
                      placeholder="Add remarks or conditions here..."></textarea>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-success">✅ Approve Budget</button>
            <button class="btn btn-warning">🔁 Return for Revision</button>
            <button class="btn btn-danger">❌ Reject Budget</button>
        </div>

@endsection
