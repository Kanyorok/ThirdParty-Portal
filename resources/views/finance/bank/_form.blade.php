@php
    /** @var \App\Models\Finance\Bank|null $bank */
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Fix the following:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
        <input name="BankName" class="form-control" value="{{ old('BankName', $bank->BankName ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Short Name</label>
        <input name="ShortName" class="form-control" value="{{ old('ShortName', $bank->ShortName ?? '') }}"  required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Bank Code</label>
        <input name="BankCode" class="form-control" value="{{ old('BankCode', $bank->BankCode ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">SWIFT Code</label>
        <input name="SwiftCode" class="form-control" value="{{ old('SwiftCode', $bank->SwiftCode ?? '') }}" >
    </div>
    <div class="col-md-4">
        <label class="form-label">Clearing Code</label>
        <input name="ClearingCode" class="form-control" value="{{ old('ClearingCode', $bank->ClearingCode ?? '') }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Country ID</label>
        <input type="number" name="CountryID" class="form-control" value="{{ old('CountryID', $bank->CountryID ?? '') }}" required>
        {{-- Replace with a select if you have t_Countries --}}
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="EmailID" class="form-control" value="{{ old('EmailID', $bank->EmailID ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input name="Phone" class="form-control" value="{{ old('Phone', $bank->Phone ?? '') }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Website</label>
        <input type="url" name="Website" class="form-control" value="{{ old('Website', $bank->Website ?? '') }}" required>
    </div>
{{--    <div class="col-md-6 d-flex align-items-end">--}}
{{--        <div class="form-check">--}}
{{--            <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"--}}
{{--                   {{ old('IsActive', $bank->IsActive ?? 1) ? 'checked' : '' }}>--}}
{{--            <label for="IsActive" class="form-check-label">Active</label>--}}
{{--        </div>--}}
{{--    </div>--}}
</div>
