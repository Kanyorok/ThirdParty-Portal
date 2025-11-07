@php
    /** @var \App\Models\Finance\BankBranch|null $branch */
    /** @var \App\Models\Finance\Bank|null $bank */
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Fix the following:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Bank <span class="text-danger">*</span></label>
        @if(isset($bank))
            <input type="hidden" name="BankID" value="{{ $bank->BankID }}">
            <input class="form-control" value="{{ $bank->BankName }}" disabled>
        @else
            <input type="number" name="BankID" class="form-control" value="{{ old('BankID', $branch->BankID ?? '') }}"
                   required>
            {{-- Replace with a select of Banks if desired --}}
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label">Branch Name <span class="text-danger">*</span></label>
        <input name="BranchName" class="form-control" value="{{ old('BranchName', $branch->BranchName ?? '') }}"
               required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Branch Code</label>
        <input name="BranchCode" class="form-control" value="{{ old('BranchCode', $branch->BranchCode ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">City ID</label>
        <input type="number" name="CityID" class="form-control" value="{{ old('CityID', $branch->CityID ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Country ID</label>
        <input type="number" name="CountryID" class="form-control"
               value="{{ old('CountryID', $branch->CountryID ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Address 1</label>
        <input name="Address1" class="form-control" value="{{ old('Address1', $branch->Address1 ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Address 2</label>
        <input name="Address2" class="form-control" value="{{ old('Address2', $branch->Address2 ?? '') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Zip Code</label>
        <input name="ZipCode" class="form-control" value="{{ old('ZipCode', $branch->ZipCode ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input name="Phone" class="form-control" value="{{ old('Phone', $branch->Phone ?? '') }}" pattern="^\+[1-9]\d{7,14}$" inputmode="tel" placeholder="e.g., +12025550123">
        <div class="form-text">Use international format (E.164), starting with + and country code.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="EmailID" class="form-control" value="{{ old('EmailID', $branch->EmailID ?? '') }}">
    </div>

    <div class="col-md-12 d-flex align-items-center">
        <div class="form-check">
            <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                {{ old('IsActive', $branch->IsActive ?? 1) ? 'checked' : '' }}>
            <label for="IsActive" class="form-check-label">Active</label>
        </div>
    </div>
</div>
