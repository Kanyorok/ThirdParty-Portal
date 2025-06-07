@extends('layouts.app')
@section('title', 'New Budget Line & GL Mapping')
@section('content')
<div class="card mb-4">
  <div class="card-header bg-primary text-white">➕ Add Budget Line & GL Mapping</div>
  <div class="card-body">
    <form>
      <!-- 🧾 Budget Line Entry -->
      <div class="mb-3">
        <label class="form-label">Budget Line Name</label>
        <input type="text" class="form-control" placeholder="e.g. Interest Income, Loan Fees" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" rows="2" placeholder="Describe this budget line..."></textarea>
      </div>

      <hr class="my-4">

      <!-- 🔗 CBS GL Mapping -->
      <h6>🔗 CBS GL Accounts (Multiple)</h6>
      <div class="mb-3">
        <label class="form-label">Select CBS GLs</label>
        <select multiple class="form-select" required>
          <option value="GL101">GL101 - Interest Income (Loan Product A)</option>
          <option value="GL102">GL102 - Interest Income (Loan Product B)</option>
          <option value="GL103">GL103 - Interest Income (Loan Product C)</option>
        </select>
        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple GLs.</div>
      </div>

      <!-- 🔗 ERP GL Mapping -->
      {{-- <div class="mb-3">
        <label class="form-label">ERP GL Account (Optional)</label>
        <select class="form-select">
          <option selected disabled>-- Select ERP GL --</option>
          <option value="ERP001">ERP001 - Interest Revenue</option>
          <option value="ERP002">ERP002 - Other Income</option>
        </select>
      </div> --}}

      <!-- 🔘 Primary Flag -->
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="primaryCheck">
        <label class="form-check-label" for="primaryCheck">
          Mark as Primary Mapping
        </label>
      </div>

      <button class="btn btn-success">💾 Save Budget Line & Mapping</button>
    </form>
  </div>
</div>
@endsection