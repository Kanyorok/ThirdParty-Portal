@extends('layouts.app')
@section('title', 'Property Management')

@section('content')
<div class="container mt-5" style="max-width: 850px;">
    <h4 class="fw-bold mb-4">✏️ Edit Tenant Details</h4>

    <div class="card shadow border-0">
        <div class="card-header bg-light fw-semibold text-primary">
            🛠 Tenant Information
        </div>

        <form method="POST" action="{{ route('addtenant.update', $newtenant->Id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body">
                <!-- Basic Info -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tenant Type</label>
                        <select name="TenantType" class="form-select">
                            @foreach($tenantTypes as $type)
                                <option value="{{ $type->ID }}" {{ $newtenant->TenantType == $type->ID ? 'selected' : '' }}>
                                    {{ $type->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Tenant Name</label>
                        <input type="text" name="TenantName" class="form-control" value="{{ old('TenantName', $newtenant->TenantName) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ID/Registration No.</label>
                        <input type="text" name="IDRegistrationNo" class="form-control" value="{{ old('IDRegistrationNo', $newtenant->IDRegistrationNo) }}">
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="PhoneNumber" class="form-control" value="{{ old('PhoneNumber', $newtenant->PhoneNumber) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="EmailAddress" class="form-control" value="{{ old('EmailAddress', $newtenant->EmailAddress) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nationality</label>
                        <input type="text" name="Nationality" class="form-control" value="{{ old('Nationality', $newtenant->Nationality) }}">
                    </div>
                </div>

                <!-- Address & Notes -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Postal Address</label>
                        <input type="text" name="PostalAddress" class="form-control" value="{{ old('PostalAddress', $newtenant->PostalAddress) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="Remarks" class="form-control" value="{{ old('Remarks', $newtenant->Remarks) }}">
                    </div>
                </div>


                <!-- Documents Upload -->
                <div class="mb-3">
                    <label class="form-label">Upload New Documents (optional)</label>
                    <input type="file" name="Documents[]" class="form-control" multiple>
                    <small class="text-muted">Leave blank if no changes are needed.</small>
                </div>

                <!-- Status -->
                <div class="row g-3 mb-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="IsActive">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                        </select>
                </div>

                <!-- Buttons -->
                <div class="text-end">
                    <button type="submit" class="btn btn-success">💾 Update Tenant</button>
                    <a href="{{ route('addtenant.index') }}" class="btn btn-outline-secondary">↩ Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
