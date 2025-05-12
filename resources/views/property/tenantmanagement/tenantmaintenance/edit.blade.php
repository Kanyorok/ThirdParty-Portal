@extends('layouts.app')
@section('title', 'Edit Tenant')
@section('content')
<div class="container mt-4">
  <h4 class="fw-bold mb-3">✏️ Edit Tenant Details</h4>

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">🛠 Tenant Information</div>
    <div class="card-body">

      <!-- Basic Info -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Tenant Type</label>
          <select class="form-select">
            <option selected>Individual</option>
            <option>Corporate</option>
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label">Tenant Name</label>
          <input type="text" class="form-control" value="Moses K.">
        </div>
        <div class="col-md-4">
          <label class="form-label">ID/Registration No.</label>
          <input type="text" class="form-control" value="ID12345678">
        </div>
      </div>

      <!-- Contact Info -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Phone Number</label>
          <input type="text" class="form-control" value="+254712345678">
        </div>
        <div class="col-md-4">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" value="moses@example.com">
        </div>
        <div class="col-md-4">
          <label class="form-label">Nationality</label>
          <input type="text" class="form-control" value="Kenyan">
        </div>
      </div>

      <!-- Address & Notes -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Postal Address</label>
          <input type="text" class="form-control" value="P.O. Box 1234 - Nairobi">
        </div>
        <div class="col-md-6">
          <label class="form-label">Remarks</label>
          <input type="text" class="form-control" value="Preferred payment via bank transfer">
        </div>
      </div>

      <!-- Documents -->
      <div class="mb-3">
        <label class="form-label">Update or Upload New Documents</label>
        <input type="file" class="form-control" multiple>
        <small class="text-muted">Leave blank if no changes to existing documents.</small>
      </div>

      <!-- Status -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select">
            <option selected>Active</option>
            <option>Inactive</option>
            <option>Blacklisted</option>
          </select>
        </div>
      </div>

      <div class="text-end">
        <button class="btn btn-primary">💾 Update Tenant</button>
        <button class="btn btn-secondary">↩ Cancel</button>
      </div>
    </div>
  </div>
</div>
@endsection