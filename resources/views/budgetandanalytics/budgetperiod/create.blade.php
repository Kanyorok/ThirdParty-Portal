@extends('layouts.app')
@section('title', 'Budget Period Setup')
@section('content')
    <div class="card p-4">
        <h5>🗓️ Budget Period Setup</h5>
        <div class="mb-3">
            <label for="fiscalYear" class="form-label">Fiscal Year</label>
            <input type="number" class="form-control" id="fiscalYear" placeholder="e.g., 2025">
        </div>
        <div class="mb-3">
            <label for="periodType" class="form-label">Periods</label>
            <select class="form-select" id="periodType">
                <option>Annually</option>
                <option>Quarterly</option>
            </select>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="lockPeriods">
            <label class="form-check-label" for="lockPeriods">Lock Previous Periods</label>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" rows="3"></textarea>
        </div>
        <button class="btn btn-primary">💾 Save</button>
        <button class="btn btn-secondary">🔄 Reset</button>
    </div>
@endsection
