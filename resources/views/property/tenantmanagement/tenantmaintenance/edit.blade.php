@extends('layouts.app')
@section('title', 'Property Management')
@section('content')
    <div class="container mt-4">
        <h4 class="fw-bold mb-3">Edit Tenant Details</h4>

        <form method="POST" action="{{ route('addtenant.update', $newtenant->Id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">🛠 Tenant Information</div>
                <div class="card-body">

                    <!-- Tenant Type & Basic Info -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Tenant Type<span class="text-danger">*</span></label>
                            <select name="TenantType" class="form-select">
                                @foreach($tenantTypes as $type)
                                    <option value="{{ $type->ID }}"
                                        {{ $newtenant->TenantType == $type->ID ? 'selected' : '' }}>
                                        {{ $type->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Tenant Name<span class="text-danger">*</span></label>
                            <input type="text" name="TenantName" class="form-control"
                                   value="{{ old('TenantName', $newtenant->TenantName) }}"
                                   placeholder="e.g. Moses K. or Acme Ltd.">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">ID/Registration No.<span class="text-danger">*</span></label>
                            <input type="text" name="IDRegistrationNo" class="form-control"
                                   value="{{ old('IDRegistrationNo', $newtenant->IDRegistrationNo) }}"
                                   placeholder="e.g. ID12345678 / BRN0021">
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Phone Number<span class="text-danger">*</span></label>
                            <input type="text" name="PhoneNumber" class="form-control"
                                   value="{{ old('PhoneNumber', $newtenant->PhoneNumber) }}"
                                   placeholder="e.g. +254712345678">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email Address<span class="text-danger">*</span></label>
                            <input type="email" name="EmailAddress" class="form-control"
                                   value="{{ old('EmailAddress', $newtenant->EmailAddress) }}"
                                   placeholder="e.g. tenant@email.com">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nationality<span class="text-danger">*</span></label>
                            <input type="text" name="Nationality" class="form-control"
                                   value="{{ old('Nationality', $newtenant->Nationality) }}"
                                   placeholder="e.g. Kenyan">
                        </div>
                    </div>

                    <!-- Address & Status -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Postal Address<span class="text-danger">*</span></label>
                            <input type="text" name="PostalAddress" class="form-control"
                                   value="{{ old('PostalAddress', $newtenant->PostalAddress) }}"
                                   placeholder="e.g. P.O. Box 1234 - Nairobi">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status<span class="text-danger">*</span></label>
                            <select class="form-select" name="IsActive">
                                <option value="1" {{ $newtenant->IsActive ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$newtenant->IsActive ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Documents Upload -->
                    <div class="mb-3">
                        <label class="form-label">Upload New Documents (optional)</label>
                        <input type="file" name="Documents[]" class="form-control" multiple>
                        <small class="text-muted">Leave blank if no changes are needed.</small>
                    </div>

                    <!-- Remarks -->
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="Remarks" class="form-control" rows="3"
                                  placeholder="Optional">{{ old('Remarks', $newtenant->Remarks) }}</textarea>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="card-footer text-end py-2">
                    <button type="submit" class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">
                        💾 Update Tenant
                    </button>
                    <a href="{{ route('addtenant.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
</div>
@endsection
