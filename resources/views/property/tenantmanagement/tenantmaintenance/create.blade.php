@extends('layouts.app')
@section('title', 'Tenant Registration')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">👤 Add New Tenant</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">➕ Tenant Registration</div>
    <div class="card-body">
      <!-- Tenant Type -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Tenant Type</label>
          <select class="form-select">
            <option>Individual</option>
            <option>Corporate</option>
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label">Tenant Name</label>
          <input type="text" class="form-control" placeholder="e.g. Moses K. or Acme Ltd.">
        </div>
        <div class="col-md-4">
          <label class="form-label">ID/Registration No.</label>
          <input type="text" class="form-control" placeholder="e.g. ID12345678 / BRN0021">
        </div>
      </div>

      <!-- Contact Details -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Phone Number</label>
          <input type="text" class="form-control" placeholder="e.g. +254712345678">
        </div>
        <div class="col-md-4">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" placeholder="e.g. tenant@email.com">
        </div>
        <div class="col-md-4">
          <label class="form-label">Nationality</label>
          <input type="text" class="form-control" placeholder="e.g. Kenyan">
        </div>
      </div>

      <!-- Address and Notes -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Postal Address</label>
          <input type="text" class="form-control" placeholder="e.g. P.O. Box 1234 - Nairobi">
        </div>
        <div class="col-md-6">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" placeholder="Optional">
        </div>
      </div>

      <!-- Document Upload -->
      <div class="mb-3">
        <label class="form-label">Upload Supporting Documents</label>
        <input type="file" class="form-control" multiple>
        <small class="text-muted">e.g. ID copy, Certificate of Incorporation</small>
      </div>

      <div class="text-end">
        <button class="btn btn-success">💾 Save Tenant</button>
      </div>
    </div>
  </div>
</div>
@endsection