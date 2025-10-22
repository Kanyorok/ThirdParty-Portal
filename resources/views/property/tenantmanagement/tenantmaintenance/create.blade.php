@extends('layouts.app')
@section('title', 'Property Management')
@section('content')
<div class="container mt-4">
    <form action="{{ route('addtenant.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
  <div class="card shadow">
      <div class="card-header bg-light fw-bold">Tenant Registration</div>
    <div class="card-body">
      <!-- Tenant Type -->

      <div class="row g-3 mb-3">
          <div class="col-md-5">
              <label class="form-label">Tenant<span class="text-danger">*</span></label>
              <select class="form-select" name="ThirdPartyId">
                  <option value="">-- Select the tenant --</option>
                  @foreach ($tenants as $tenant)
                      <option value="{{ $tenant->Id }}"> Name: {{ $tenant->ThirdPartyName }} &nbsp;
                          Phone: {{ $tenant->Phone }}</option>
                  @endforeach
              </select>
          </div>
        <div class="col-md-3">
          <label class="form-label">Tenant Type<span class="text-danger">*</span></label>
            <select class="form-select" name="TenantType">
                <option value="">-- Select Tenent Type --</option>
                @foreach ($tenantTypes as $tenanttype)
                    <option value="{{ $tenanttype->ID }}">{{ $tenanttype->Description }}</option>
                @endforeach
          </select>
        </div>
      </div>

      <!-- Address and Notes -->
      <div class="row g-3 mb-3">
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
        <a href="{{ route('addtenant.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-success"
                onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Create Tenant
        </button>
    </form>
    </div>
  </div>
</div>
@endsection
