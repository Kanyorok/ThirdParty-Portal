@extends('layouts.app')
@section('title', 'IProperty Management')
@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-3">Add New Tenant</h4>

    <form action="{{ route('addtenant.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
  <div class="card shadow">
      <div class="card-header bg-light fw-bold">Tenant Registration</div>
    <div class="card-body">
      <!-- Tenant Type -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Tenant Type<span class="text-danger">*</span></label>
            <select class="form-select" name="TenantType">
                <option value="">-- Select Tenent Type --</option>
                @foreach ($tenantTypes as $tenanttype)
                    <option value="{{ $tenanttype->ID }}">{{ $tenanttype->Description }}</option>
                @endforeach
          </select>
        </div>
        <div class="col-md-5">
          <label class="form-label">Tenant Name<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="TenantName" placeholder="e.g. Moses K. or Acme Ltd.">
        </div>
        <div class="col-md-4">
          <label class="form-label">ID/Registration No.<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="IDRegistrationNo" placeholder="e.g. ID12345678 / BRN0021">
        </div>
      </div>

      <!-- Contact Details -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Phone Number<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="PhoneNumber" placeholder="e.g. +254712345678">
        </div>
        <div class="col-md-4">
          <label class="form-label">Email Address<span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="EmailAddress" placeholder="e.g. tenant@email.com">
        </div>
        <div class="col-md-4">
          <label class="form-label">Nationality<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="Nationality" placeholder="e.g. Kenyan">
        </div>
      </div>

      <!-- Address and Notes -->
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Postal Address<span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="PostalAddress" placeholder="e.g. P.O. Box 1234 - Nairobi">
        </div>
        <div class="col-md-6">
            <label class="form-label">Status<span class="text-danger">*</span></label>
              <select class="form-select" name="IsActive">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
              </select>
      </div>
      <!-- Document Upload -->
      <div class="mb-3">
        <label class="form-label">Upload Supporting Documents</label>
        <input type="file" name="Document" class="form-control" multiple>
        <small class="text-muted">e.g. ID copy, Certificate of Incorporation</small>
      </div>
      <div>
          <label class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control" placeholder="Optional" rows="3"></textarea>
          </div>
      </div>
        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Save Tenant</button>
    </form>
    </div>
  </div>
</div>
@endsection
