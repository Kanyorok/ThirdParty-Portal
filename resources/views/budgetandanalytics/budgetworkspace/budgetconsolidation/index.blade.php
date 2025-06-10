@extends('layouts.app')
@section('title', 'Budget Consolidation')
@section('content')
    <div class="card p-4">
        <h5>📊 Budget Consolidation & Roll-Up</h5>
        <p class="text-muted">View aggregated budget data across branches, products, or budget lines by scenario.</p>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="scenario" class="form-label">Scenario</label>
                <select class="form-select" id="scenario">
                    <option>Base Case</option>
                    <option>Best Case</option>
                    <option>Worst Case</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="viewBy" class="form-label">View By</label>
                <select class="form-select" id="viewBy">
                    <option>Branch</option>
                    <option>Product</option>
                    <option>Budget Line</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Export</label><br>
                <button class="btn btn-outline-success btn-sm">📥 Export Excel</button>
                <button class="btn btn-outline-danger btn-sm">📄 Export PDF</button>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Entity</th>
                    <th>Total Budget (KES)</th>
                    <th>Status Summary</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Central Branch</td>
                    <td>48,000,000</td>
                    <td>
                        <span class="badge bg-success">Approved: 20</span>
                        <span class="badge bg-warning text-dark">Pending: 3</span>
                        <span class="badge bg-danger">Returned: 1</span>
                    </td>
                    <td>
                        <a href="{{ route('budgetconsolidation.create') }}" class="btn btn-sm btn-outline-primary">🔍
                            Drilldown</a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
