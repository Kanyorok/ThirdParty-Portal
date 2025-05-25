@extends('layouts.app')
@section('title', 'Plan vs Actual Dashboard')
@section('content')
<div class="card p-4">
  <h5>📊 Plan vs Actual Dashboard</h5>
  <p class="text-muted">Compare budgeted values with actuals retrieved from CBS across branches, products, or budget lines.</p>

  <div class="row mb-3">
    <div class="col-md-3">
      <label class="form-label">Scenario</label>
      <select class="form-select">
        <option>Base Case</option>
        <option>Best Case</option>
        <option>Worst Case</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Period</label>
      <select class="form-select">
        <option>Jan-2025</option>
        <option>Feb-2025</option>
        <option>Mar-2025</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Branch</label>
      <select class="form-select">
        <option>All Branches</option>
        <option>Central Branch</option>
        <option>West Branch</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">View By</label>
      <select class="form-select">
        <option>Budget Line</option>
        <option>Product</option>
        <option>GL Code</option>
      </select>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Item</th>
          <th>Budgeted Amount</th>
          <th>Actual Amount</th>
          <th>Variance</th>
          <th>Variance (%)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td>Interest Income – Loans</td>
          <td>1,200,000</td>
          <td>1,080,000</td>
          <td>-120,000</td>
          <td>-10%</td>
          <td><span class="badge bg-warning text-dark">Below Target</span></td>
        </tr>
        <tr>
          <td>2</td>
          <td>Commission Income</td>
          <td>300,000</td>
          <td>360,000</td>
          <td>+60,000</td>
          <td>+20%</td>
          <td><span class="badge bg-success">Above Target</span></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
