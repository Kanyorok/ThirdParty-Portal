@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">🏢 Add New Property</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Property Registration</div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Property Name</label>
          <input type="text" class="form-control" placeholder="e.g. Sunset Plaza">
        </div>
        <div class="col-md-4">
          <label class="form-label">Property Code</label>
          <input type="text" class="form-control" placeholder="e.g. PROP-001">
        </div>
        <div class="col-md-4">
          <label class="form-label">Property Type</label>
          <select class="form-select">
            <option>Building</option>
            <option>Land</option>
            <option>Complex</option>
            <option>Apartment Block</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Category</label>
          <select class="form-select">
            <option>Residential</option>
            <option>Commercial</option>
            <option>Mixed-Use</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Owner</label>
          <input type="text" class="form-control" placeholder="e.g. ABC Holdings Ltd.">
        </div>
        <div class="col-md-4">
          <label class="form-label">Acquisition Date</label>
          <input type="date" class="form-control" value="2025-05-02">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Country</label>
          <input type="text" class="form-control" placeholder="e.g. Kenya">
        </div>
        <div class="col-md-4">
          <label class="form-label">Town / City</label>
          <input type="text" class="form-control" placeholder="e.g. Nairobi">
        </div>
        <div class="col-md-4">
          <label class="form-label">Area / Locality</label>
          <input type="text" class="form-control" placeholder="e.g. Westlands">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">GPS Coordinates</label>
          <input type="text" class="form-control" placeholder="e.g. -1.265, 36.800">
        </div>
        <div class="col-md-6">
          <label class="form-label">Upload Documents (PDF, JPG)</label>
          <input type="file" class="form-control" multiple>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Property Description</label>
        <textarea class="form-control" rows="3" placeholder="Enter any additional details..."></textarea>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Property</button>
      </div>
    </div>
  </div>
</div>
@endsection