@extends('layouts.app')
@section('title', 'Add Driver Projection')
@section('content')
<<<<<<< HEAD
<div class="card mt-4">
    <div class="card-header bg-primary text-white">➕ Add KPI Driver Projection</div>
    <div class="card-body">
        <form>
            <div class="mb-3">
                <label class="form-label">Budget Period</label>
                <select class="form-select">
                    <option selected>FY2025-Q1</option>
                    <option>FY2025-Q2</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Branch</label>
                <select class="form-select">
                    <option selected>Main Branch</option>
                    <option>Westlands Branch</option>
                    <option>Industrial Area Branch</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Product</label>
                <select class="form-select">
                    <option selected>Personal Loan</option>
                    <option>SME Loan</option>
                    <option>Fixed Deposit</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">KPI Type</label>
                <select class="form-select">
                    <option selected>Loan</option>
                    <option>Deposit</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Projected Amount</label>
                <input type="number" class="form-control" placeholder="e.g. 10000000">
            </div>
            <div class="mb-3">
                <label class="form-label">Growth Rate (%)</label>
                <input type="number" step="0.01" class="form-control" placeholder="e.g. 5.5">
            </div>
            <button type="submit" class="btn btn-success">Save Projection</button>
        </form>
    </div>
</div>
=======
    <div class="card p-4">
        <h5>📈 Driver Projections Entry</h5>
        <p class="text-muted">Enter monthly or quarterly driver values (projections) per product or branch. These feed
            into formula-based budget calculations.</p>

        <div class="mb-3">
            <label for="driver" class="form-label">Select Driver</label>
            <select class="form-select" id="driver">
                <option selected disabled>Choose a driver</option>
                <option>Loan Book Growth</option>
                <option>Deposit Book Growth</option>
                <option>Average Lending Rate</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="branch" class="form-label">Branch</label>
            <select class="form-select" id="branch">
                <option>All Branches</option>
                <option>Central Branch</option>
                <option>West Branch</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="product" class="form-label">Product</label>
            <select class="form-select" id="product">
                <option>All Products</option>
                <option>Personal Loan</option>
                <option>Fixed Deposit</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="period" class="form-label">Period</label>
            <select class="form-select" id="period">
                <option>Jan-2025</option>
                <option>Feb-2025</option>
                <option>Mar-2025</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="value" class="form-label">Projected Value</label>
            <input type="number" step="0.01" class="form-control" id="value" placeholder="e.g., 12.5">
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea class="form-control" id="notes" rows="2"
                      placeholder="Describe basis for projection..."></textarea>
        </div>

        <button class="btn btn-primary">💾 Save Projection</button>
        <button class="btn btn-secondary">➕ Add Another</button>
    </div>
>>>>>>> feature/newMenu-dev
@endsection
