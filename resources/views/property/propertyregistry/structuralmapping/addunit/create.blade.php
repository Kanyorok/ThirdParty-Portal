@extends('layouts.app')
@section('title', 'Add Unit to Floor')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏠 Add Unit to Floor</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Unit Setup</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Select Floor</label>
          <select class="form-select">
            <option>1st Floor - Block A - Sunset Plaza</option>
            <option>Ground Floor - Tower 1 - Mountain View</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Unit Code / Label</label>
          <input type="text" class="form-control" placeholder="e.g. Unit 101">
        </div>
        <div class="col-md-4">
          <label class="form-label">Unit Size (sq. ft)</label>
          <input type="number" class="form-control" placeholder="e.g. 1200">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Is Rentable?</label>
          <select class="form-select">
            <option>Yes</option>
            <option>No</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Current Status</label>
          <select class="form-select">
            <option>Vacant</option>
            <option>Occupied</option>
            <option>Reserved</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="Optional">
        </div>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Unit</button>
      </div>
    </div>
  </div>
</div>
@endsection