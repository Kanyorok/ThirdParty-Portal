@extends('layouts.app')
@section('title', 'Budget Line Mapping')
@section('content')
<div class="card p-4">
  <h5>🔗 Budget Line to Product Mapping</h5>
  <div class="mb-3">
    <label for="budgetLine" class="form-label">Budget Line</label>
    <select class="form-select" id="budgetLine">
      <option selected disabled>Select Budget Line</option>
      <option>Interest Income - Loans</option>
      <option>Interest Expense - Deposits</option>
    </select>
  </div>
  <div class="mb-3">
    <label for="product" class="form-label">CBS Product</label>
    <select class="form-select" id="product">
      <option selected disabled>Select Product</option>
      <option>Personal Loan</option>
      <option>SME Loan</option>
      <option>Fixed Deposit</option>
    </select>
  </div>
  <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" id="isPrimary">
    <label class="form-check-label" for="isPrimary">Mark as Primary Mapping</label>
  </div>
  <button class="btn btn-primary">💾 Save Mapping</button>
  <button class="btn btn-secondary">🔄 Reset</button>
</div>
@endsection