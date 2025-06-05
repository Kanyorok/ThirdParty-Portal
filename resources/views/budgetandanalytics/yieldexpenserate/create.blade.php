@extends('layouts.app')
@section('title', 'Add Driver Rate')
@section('content')
<div class="card mt-4">
    <div class="card-header bg-primary text-white">➕ Add Yield / Expense Rate</div>
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
                <label class="form-label">Product</label>
                <select class="form-select">
                    <option selected>Personal Loan</option>
                    <option>SME Loan</option>
                    <option>Fixed Deposit</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Rate Type</label>
                <select class="form-select">
                    <option selected>LoanYield</option>
                    <option>InterestExpense</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Rate Value (%)</label>
                <input type="number" step="0.01" class="form-control" placeholder="e.g. 10.5">
            </div>
            <div class="mb-3">
                <label class="form-label">Effective Date</label>
                <input type="date" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Source</label>
                <input type="text" class="form-control" placeholder="e.g. CBS, Manual">
            </div>
            <button type="submit" class="btn btn-success">Save Rate</button>
        </form>
    </div>
</div>
@endsection
