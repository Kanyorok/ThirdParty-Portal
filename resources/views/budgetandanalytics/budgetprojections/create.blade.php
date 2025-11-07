@extends('layouts.app')
@section('title', 'Add Driver Projection')
@section('content')
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
@endsection
