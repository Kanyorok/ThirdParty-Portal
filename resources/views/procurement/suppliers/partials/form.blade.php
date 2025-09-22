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
            @foreach (\App\Enums\BusinessTypeEnum::cases() as $type)
                <option value="{{ $type->value }}"
                    {{ old('BusinessType', optional($supplier)->BusinessType->value ?? '') == $type->value ? 'selected' : '' }}>
                    {{ $type->label() }}
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
        <input type="text" name="Country" id="Country" class="form-control" required
               value="{{ old('Country', optional($supplier)->Country) }}">
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
        <input type="text" name="Phone" id="Phone" class="form-control"
               value="{{ old('Phone', optional($supplier)->Phone) }}">
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
        <select name="category_ids[]" id="category_ids" class="form-select" multiple required>
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
    </div>

    {{-- Prequalified Switch --}}
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check form-switch">
            <input type="checkbox" name="IsPrequalified" value="1" class="form-check-input" role="switch"
                   id="IsPrequalified"
                {{ old('IsPrequalified', optional($supplier)->IsPrequalified ?? false) ? 'checked' : '' }}>
            <label class="form-check-label ms-2" for="IsPrequalified">Prequalified</label>
        </div>
    </div>

    {{-- Approval Status (edit only) --}}
    @if(isset($supplier))
        <div class="col-md-6">
            <label for="ApprovalStatus" class="form-label">Approval Status</label>
            <select name="ApprovalStatus" id="ApprovalStatus" class="form-select">
                @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                    <option value="{{ $status->value }}"
                        {{ old('ApprovalStatus', optional($supplier)->ApprovalStatus->value) == $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

</div>
