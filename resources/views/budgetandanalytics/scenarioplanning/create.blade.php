@extends('layouts.app')
@section('title', 'Budget Scenarios')
@section('content')
    <div class="card p-4">
        <h5>📘 Scenario Planning Setup</h5>
        <p class="text-muted">Define budgeting scenarios and select the planning method (top-down, bottom-up, or
            hybrid).</p>

        <div class="mb-3">
            <label for="scenarioName" class="form-label">Scenario Name</label>
            <input type="text" class="form-control" id="scenarioName"
                   placeholder="e.g., Base Case, Best Case, Worst Case">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" rows="3"
                      placeholder="Describe the scenario purpose or assumptions..."></textarea>
        </div>

        <div class="mb-3">
            <label for="budgetPeriod" class="form-label">Budget Period</label>
            <select class="form-select" id="budgetPeriod">
                <option>2025</option>
                <option>2026</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Planning Method</label>
            <select class="form-select" id="planningMethod">
                <option value="Bottom-Up">Bottom-Up</option>
                <option value="Top-Down">Top-Down</option>
                <option value="Hybrid">Hybrid</option>
            </select>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="isDefault">
            <label class="form-check-label" for="isDefault">Set as Default Scenario</label>
        </div>

        <div class="mb-2 d-flex justify-content-between">
            <button class="btn btn-primary">💾 Save Scenario</button>
            <button class="btn btn-secondary">🔄 Reset</button>

        </div>
    </div>
@endsection
