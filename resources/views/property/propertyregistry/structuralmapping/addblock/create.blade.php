@extends('layouts.app')
@section('title', 'Add Block to Property')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏢 Add Block to Property</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Block Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Property</label>
          <select class="form-select">
            <option>Sunset Plaza</option>
            <option>Mountain View Estate</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Block Name / Label</label>
          <input type="text" class="form-control" placeholder="e.g. Block A, Tower 1">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Block Description</label>
        <textarea class="form-control" rows="2" placeholder="Optional description"></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Block</button>
      </div>
    </div>
  </div>
</div>
@endsection