@php
$supplierMaster = isset($supplier) ? \App\Models\ThirdParty\SupplierMaster::where('ThirdPartyId', $supplier->Id)->first() : null;
@endphp
<div class="row g-4">
    <div class="col-md-6">
        <label for="ThirdPartyName" class="form-label">Legal Name <span class="text-danger">*</span></label>
        <input type="text" name="ThirdPartyName" id="ThirdPartyName" class="form-control" required
            value="{{ old('ThirdPartyName', optional($supplier)->ThirdPartyName) }}">
    </div>

    {{-- Trading Name --}}
    <div class="col-md-6">
        <label for="TradingName" class="form-label">Trading Name</label>
        <input type="text" name="TradingName" id="TradingName" class="form-control"
            value="{{ old('TradingName', optional($supplier)->TradingName) }}">
    </div>

    {{-- Business Type --}}
    <div class="col-md-6">
        <label for="BusinessType" class="form-label">Business Type <span class="text-danger">*</span></label>
        <select name="BusinessType" id="BusinessType" class="form-select" required>
            <option value="">-- Select Business Type --</option>
            @foreach ($businessTypes ?? [] as $type)
            <option value="{{ $type->Id }}"
                {{ old('BusinessType', optional($supplier)->BusinessType) == $type->Id ? 'selected' : '' }}>
                {{ $type->Description }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Registration Number --}}
    <div class="col-md-6">
        <label for="RegistrationNumber" class="form-label">Registration Number</label>
        <input type="text" name="RegistrationNumber" id="RegistrationNumber" class="form-control"
            value="{{ old('RegistrationNumber', optional($supplier)->RegistrationNumber) }}">
    </div>

    {{-- Tax PIN --}}
    <div class="col-md-6">
        <label for="TaxPIN" class="form-label">Tax PIN</label>
        <input type="text" name="TaxPIN" id="TaxPIN" class="form-control"
            value="{{ old('TaxPIN', optional($supplier)->TaxPIN) }}">
    </div>

    {{-- VAT Number --}}
    <div class="col-md-6">
        <label for="VATNumber" class="form-label">VAT Number</label>
        <input type="text" name="VATNumber" id="VATNumber" class="form-control"
            value="{{ old('VATNumber', optional($supplier)->VATNumber) }}">
    </div>

    {{-- Country --}}
    <div class="col-md-6">
        <label for="Country" class="form-label">Country <span class="text-danger">*</span></label>
        <select name="Country" id="Country" class="form-select" required>
            <option value="">-- Select Country --</option>
            @foreach($countries ?? [] as $country)
            <option value="{{ $country->Id }}"
                {{ old('Country', optional($supplier)->CountryId) == $country->Id ? 'selected' : '' }}>
                {{ $country->Name }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Physical Address --}}
    <div class="col-md-6">
        <label for="PhysicalAddress" class="form-label">Physical Address</label>
        <input type="text" name="PhysicalAddress" id="PhysicalAddress" class="form-control"
            value="{{ old('PhysicalAddress', optional($supplier)->PhysicalAddress) }}">
    </div>

    {{-- Email --}}
    <div class="col-md-6">
        <label for="Email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="Email" id="Email" class="form-control" required
            value="{{ old('Email', optional($supplier)->Email) }}">
    </div>

    {{-- Phone --}}
    <div class="col-md-6">
        <label for="Phone" class="form-label">Phone</label>
        <input type="tel" name="Phone" id="Phone" class="form-control" pattern="^\+[1-9]\d{7,14}$" inputmode="tel"
            placeholder="e.g., +12025550123" value="{{ old('Phone', optional($supplier)->Phone) }}">
        <div class="form-text">Use international format (E.164), starting with + and country code.</div>
    </div>

    {{-- Website --}}
    <div class="col-md-6">
        <label for="Website" class="form-label">Website</label>
        <input type="url" name="Website" id="Website" class="form-control"
            value="{{ old('Website', optional($supplier)->Website) }}">
    </div>

    {{-- Categories --}}
    <div class="col-md-6">
        <label for="category_ids" class="form-label">Categories <span class="text-danger">*</span></label>
        <select id="category_ids_disabled" class="form-select" multiple disabled>
            @php
            $selectedCategories = old(
            'category_ids',
            optional($supplier)->supplierCategories ? $supplier->supplierCategories->pluck('SupplierCategoryID')->toArray() : []
            );
            @endphp
            @foreach ($supplierCategories ?? [] as $category)
            <option value="{{ $category->SupplierCategoryID }}"
                {{ in_array($category->SupplierCategoryID, $selectedCategories) ? 'selected' : '' }}>
                {{ $category->CategoryName }}
            </option>
            @endforeach
        </select>
        {{-- Hidden inputs to maintain current categories on submit since editing is disabled --}}
        @foreach($selectedCategories as $catId)
        <input type="hidden" name="category_ids[]" value="{{ $catId }}">
        @endforeach
        <div class="form-text text-muted">Category modification is disabled.</div>
    </div>

    {{-- Suspended Toggle --}}
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check form-switch">
            <input type="checkbox" name="Suspended" value="1" class="form-check-input" role="switch"
                id="Suspended"
                {{ old('Suspended', optional($supplierMaster)->ApprovalStatus?->value === \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Suspended->value) ? 'checked' : '' }}>
            <label class="form-check-label ms-2" for="Suspended">Suspended</label>
            <div class="form-text">If suspended, the supplier cannot login or be used in the system.</div>
        </div>
    </div>
    <input type="hidden" name="ApprovalStatus" value="{{ optional($supplierMaster?->ApprovalStatus)->value ?? \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Pending->value }}">



</div>