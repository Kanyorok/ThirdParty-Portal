@extends('layouts.app')
@section('title', 'Business Driver')
@section('content')
    <div class="card p-4">
        <h5>📈 Business Driver Setup</h5>
        <p class="text-muted">Define drivers that will be used to automatically compute budget projections such as
            growth rates, interest rates, headcount, etc.</p>

        <div class="mb-3">
            <label for="driverName" class="form-label">Driver Name</label>
            <input type="text" class="form-control" id="driverName" placeholder="e.g., Loan Book Growth Rate">
        </div>

        <div class="mb-3">
            <label for="driverCode" class="form-label">Driver Code</label>
            <input type="text" class="form-control" id="driverCode" placeholder="e.g., DRV_LOAN_GROWTH">
        </div>

        <div class="mb-3">
            <label for="driverType" class="form-label">Driver Type</label>
            <select class="form-select" id="driverType">
                <option>Financial</option>
                <option>Operational</option>
                <option>Strategic</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="unitOfMeasure" class="form-label">Unit of Measure</label>
            <select class="form-select" id="unitOfMeasure">
                <option>%</option>
                <option>KES</option>
                <option>Count</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="frequency" class="form-label">Frequency</label>
            <select class="form-select" id="frequency">
                <option>Monthly</option>
                <option>Quarterly</option>
            </select>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="isActive" checked>
            <label class="form-check-label" for="isActive">Active</label>
        </div>


        <div class="mb-2 d-flex justify-content-between">
            <button class="btn btn-primary">💾 Save</button>
            <button class="btn btn-secondary">🔄 Reset</button>
        </div>

    </div>

@endsection
