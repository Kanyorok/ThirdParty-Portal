@extends('layouts.app')
@section('title', 'Add Payment Frequency')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">➕ Add Payment Frequency</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🛠 Frequency Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Frequency Name</label>
          <input type="text" class="form-control" placeholder="e.g. Monthly, Quarterly">
        </div>
        <div class="col-md-4">
          <label class="form-label">Frequency Code</label>
          <input type="text" class="form-control" placeholder="e.g. MTH, QTR, ANL">
        </div>
        <div class="col-md-4">
          <label class="form-label">Number of Months</label>
          <input type="number" class="form-control" placeholder="e.g. 1 for Monthly, 3 for Quarterly">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea class="form-control" rows="2" placeholder="Optional description..."></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Frequency</button>
      </div>
    </div>
  </div>
</div>
@endsection