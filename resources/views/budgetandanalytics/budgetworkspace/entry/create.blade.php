@extends('layouts.app')
@section('title', 'Budget Entry')
@section('content')
<div class="card p-4">
  <h5>📦 Budget Entry by Product</h5>
  <p class="text-muted">Branches enter volume and value projections for each product. These will generate budget lines based on linked drivers and formulas.</p>

  <div class="mb-3">
    <label for="branch" class="form-label">Branch</label>
    <select class="form-select" id="branch">
      <option selected disabled>Select Branch</option>
      <option>Central Branch</option>
      <option>West Branch</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="scenario" class="form-label">Scenario</label>
    <select class="form-select" id="scenario">
      <option>Base Case</option>
      <option>Best Case</option>
      <option>Worst Case</option>
    </select>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Product</th>
          <th>Period</th>
          <th>Volume</th>
          <th>Projected Value (KES)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <select class="form-select">
              <option>Personal Loan</option>
              <option>Fixed Deposit</option>
              <option>Savings Account</option>
            </select>
          </td>
          <td>
            <select class="form-select">
              <option>Jan-2025</option>
              <option>Feb-2025</option>
              <option>Mar-2025</option>
            </select>
          </td>
          <td><input type="number" class="form-control" placeholder="e.g., 120" /></td>
          <td><input type="number" step="0.01" class="form-control" placeholder="e.g., 12000000" /></td>
        </tr>
        <!-- Add more rows dynamically -->
      </tbody>
    </table>
  </div>

  <button class="btn btn-secondary">➕ Add Row</button>
  <button class="btn btn-primary">💾 Save Entry</button>
</div>
@endsection
