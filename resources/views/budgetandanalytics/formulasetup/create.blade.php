@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="card p-4">
  <h5>🧠 Budget Formula Setup</h5>
  <p class="text-muted">Define how driver-based budget lines should be calculated using dynamic formulas.</p>

  <div class="mb-3">
    <label for="budgetLine" class="form-label">Target Budget Line</label>
    <select class="form-select" id="budgetLine">
      <option>Interest Income – Loans</option>
      <option>Interest Expense – Deposits</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="driver1" class="form-label">Driver 1</label>
    <select class="form-select" id="driver1">
      <option>Loan Book</option>
      <option>Deposit Book</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="driver2" class="form-label">Driver 2 (optional)</label>
    <select class="form-select" id="driver2">
      <option>None</option>
      <option>Interest Rate</option>
      <option>Growth Rate</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="expression" class="form-label">Formula Expression</label>
    <input type="text" class="form-control" id="expression" placeholder="e.g., LoanBook * InterestRate">
  </div>

  <div class="mb-3">
    <label for="unit" class="form-label">Output Unit</label>
    <select class="form-select" id="unit">
      <option>KES</option>
      <option>%</option>
      <option>Count</option>
    </select>
  </div>

  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="isActive" checked>
    <label class="form-check-label" for="isActive">Activate Formula</label>
  </div>

  <div class="mb-2 d-flex justify-content-between">
  <button class="btn btn-primary">💾 Save Formula</button>
  <button class="btn btn-secondary">🔄 Reset</button>
    
  </div>
</div>
@endsection