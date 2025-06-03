@extends('layouts.app')
@section('title', 'Drilldown')
@section('content')
    <div class="card p-4">
        <h5>🔍 Drilldown – Central Branch (Base Case)</h5>
        <p class="text-muted">Detailed view of budget submissions by budget line and period.</p>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <input type="text" class="form-control" value="Central Branch" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Scenario</label>
                <input type="text" class="form-control" value="Base Case" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <input type="text" class="form-control" value="2025" readonly>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Budget Line</th>
                    <th>Month</th>
                    <th>Amount (KES)</th>
                    <th>Entry Type</th>
                    <th>Driver</th>
                    <th>Status</th>
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
                    <td><span class="badge bg-success">Approved</span></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Commission Income</td>
                    <td>Jan-2025</td>
                    <td>300,000</td>
                    <td>Manual</td>
                    <td>–</td>
                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <a href="{{ route('budgetconsolidation.index') }}" class="btn btn-sm btn-outline-secondary">🔙 Back to
                Consolidation</a>
        </div>
    </div>
@endsection
