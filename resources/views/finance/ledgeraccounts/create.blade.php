@extends('layouts.app')
@section('title', 'Add Ledger Account')
@section('content')

<div class="container">
    <div class="card shadow rounded-3">
      <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Create Ledger Account</h5>
      </div>
      <div class="card-body">
        <form id="ledgerAccountForm">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="accountCode" class="form-label">Account Code</label>
              <input type="text" class="form-control" id="accountCode" name="accountCode" required>
            </div>
            <div class="col-md-6">
              <label for="accountName" class="form-label">Account Name</label>
              <input type="text" class="form-control" id="accountName" name="accountName" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="accountType" class="form-label">Account Type</label>
              <select class="form-select" id="accountType" name="accountType" required>
                <option value="">Select Type</option>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
                <option value="Income">Income</option>
                <option value="Expense">Expense</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="glCategory" class="form-label">GL Category</label>
              <input type="text" class="form-control" id="glCategory" name="glCategory">
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="" id="isActive" name="isActive" checked>
            <label class="form-check-label" for="isActive">
              Active
            </label>
          </div>

          <div class="d-flex justify-content-end">
            <button type="reset" class="btn btn-secondary me-2">Clear</button>
            <button type="submit" class="btn btn-primary">Create Account</button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection