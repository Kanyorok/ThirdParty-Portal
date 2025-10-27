@extends('layouts.app')
@section('title','Add Ledger Limit')
@section('content')
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">➕ Ledger Transaction Limit</div>
        <div class="card-body">
            <form method="POST" action="{{ route('budgetandanalytics.limits.store') }}">
                @csrf
                <div class="mb-3">
                    <label>Budget Line</label>
                    <select name="BudgetLineID" class="form-select">
                        <option>Marketing Expense</option>
                        <option>Training</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Ledger (CBS GL)</label>
                    <input type="text" name="LedgerID" class="form-control" placeholder="e.g. 5001">
                </div>
                <div class="mb-3">
                    <label>Limit Type</label>
                    <select name="LimitType" class="form-select">
                        <option>Monthly</option>
                        <option>Quarterly</option>
                        <option>Annual</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Limit Amount</label>
                    <input type="number" step="0.01" name="LimitAmount" class="form-control">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Effective From</label>
                        <input type="date" name="EffectiveFrom" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Effective To (optional)</label>
                        <input type="date" name="EffectiveTo" class="form-control">
                    </div>
                </div>
                <button class="btn btn-success">Save Limit</button>
            </form>
        </div>
    </div>
@endsection
