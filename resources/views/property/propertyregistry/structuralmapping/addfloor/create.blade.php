@extends('layouts.app')
@section('title', 'Add Floor to Block')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏬 Add Floor to Block</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Floor Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Block</label>
          <select class="form-select">
            <option>Block A - Sunset Plaza</option>
            <option>Tower 1 - Mountain View Estate</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Floor Label</label>
          <input type="text" class="form-control" placeholder="e.g. Ground Floor, 1st Floor">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Floor Notes</label>
        <textarea class="form-control" rows="2" placeholder="Optional floor notes"></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Floor</button>
      </div>
    </div>
  </div>
</div>
@endsection