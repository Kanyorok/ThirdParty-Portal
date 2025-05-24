@extends('layouts.app')
@section('title', 'Driver Projections')
@section('content')
<div class="card p-4">
  <h5>📈 Driver Projections Entry</h5>
  <p class="text-muted">Enter monthly or quarterly driver values (projections) per product or branch. These feed into formula-based budget calculations.</p>

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
    <textarea class="form-control" id="notes" rows="2" placeholder="Describe basis for projection..."></textarea>
  </div>

  <button class="btn btn-primary">💾 Save Projection</button>
  <button class="btn btn-secondary">➕ Add Another</button>
</div>
@endsection
