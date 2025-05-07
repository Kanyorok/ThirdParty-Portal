@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏷️ Add Property Type</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Property Type Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Type Name</label>
          <input type="text" class="form-control" placeholder="e.g. Building">
        </div>
        <div class="col-md-4">
          <label class="form-label">Is Rentable?</label>
          <select class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Can Contain Units?</label>
          <select class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Description (Optional)</label>
        <textarea class="form-control" rows="2" placeholder="Add any notes..."></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Type</button>
      </div>
    </div>
  </div>
</div>
@endsection