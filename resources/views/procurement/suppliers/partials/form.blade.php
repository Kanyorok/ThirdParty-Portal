{{-- This partial is used for both create and edit forms --}}
{{-- For a new supplier, $supplier will be null --}}
<div class="row g-4">
    <div class="col-md-6">
        <div class="form-group">
            <label for="ThirdPartyName" class="form-label">Legal Name <span class="text-danger">*</span></label>
            <input type="text" name="ThirdPartyName" id="ThirdPartyName" class="form-control" required
                value="{{ old('ThirdPartyName', $supplier->ThirdPartyName ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="TradingName" class="form-label">Trading Name</label>
            <input type="text" name="TradingName" id="TradingName" class="form-control"
                value="{{ old('TradingName', $supplier->TradingName ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="BusinessType" class="form-label">Business Type <span class="text-danger">*</span></label>
            <select name="BusinessType" id="BusinessType" class="form-select" required>
                <option value="">-- Select Business Type --</option>
                @foreach (\App\Enums\BusinessTypeEnum::cases() as $type)
                <option value="{{ $type->value }}"
                    {{ old('BusinessType', $supplier?->BusinessType->value ?? '') == $type->value ? 'selected' : '' }}>
                    {{ $type->label() }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="RegistrationNumber" class="form-label">Registration Number</label>
            <input type="text" name="RegistrationNumber" id="RegistrationNumber" class="form-control"
                value="{{ old('RegistrationNumber', $supplier->RegistrationNumber ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="TaxPIN" class="form-label">Tax PIN</label>
            <input type="text" name="TaxPIN" id="TaxPIN" class="form-control"
                value="{{ old('TaxPIN', $supplier->TaxPIN ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="VATNumber" class="form-label">VAT Number</label>
            <input type="text" name="VATNumber" id="VATNumber" class="form-control"
                value="{{ old('VATNumber', $supplier->VATNumber ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="Country" class="form-label">Country <span class="text-danger">*</span></label>
            <input type="text" name="Country" id="Country" class="form-control" required
                value="{{ old('Country', $supplier->Country ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="PhysicalAddress" class="form-label">Physical Address</label>
            <input type="text" name="PhysicalAddress" id="PhysicalAddress" class="form-control"
                value="{{ old('PhysicalAddress', $supplier->PhysicalAddress ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="Email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="Email" id="Email" class="form-control" required
                value="{{ old('Email', $supplier->Email ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="Phone" class="form-label">Phone</label>
            <input type="text" name="Phone" id="Phone" class="form-control"
                value="{{ old('Phone', $supplier->Phone ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="Website" class="form-label">Website</label>
            <input type="url" name="Website" id="Website" class="form-control"
                value="{{ old('Website', $supplier->Website ?? '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="category_ids" class="form-label">Categories <span class="text-danger">*</span></label>
            <select name="category_ids[]" id="category_ids" class="form-select" multiple required>
                @php
                // Safely get selected categories for both new and existing suppliers
                $selectedCategories = old('category_ids', $supplier ? $supplier->supplierCategories->pluck('SupplierCategoryID')->toArray() : []);
                @endphp
                @foreach ($categories as $category)
                <option value="{{ $category->SupplierCategoryID }}"
                    {{ in_array($category->SupplierCategoryID, $selectedCategories) ? 'selected' : '' }}>
                    {{ $category->CategoryName }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch mt-4">
            <input type="checkbox" name="IsPrequalified" value="1" class="form-check-input" role="switch" id="IsPrequalified"
                {{ old('IsPrequalified', $supplier->IsPrequalified ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="IsPrequalified">Prequalified</label>
        </div>
    </div>
    <div class="col-md-6">
        @if (isset($supplier))
        <div class="form-group">
            <label for="ApprovalStatus" class="form-label">Approval Status</label>
            <select name="ApprovalStatus" id="ApprovalStatus" class="form-select">
                @foreach (\App\Enums\ThirdPartyApprovalStatusEnum::cases() as $status)
                <option value="{{ $status->value }}"
                    {{ old('ApprovalStatus', $supplier->ApprovalStatus->value) == $status->value ? 'selected' : '' }}>
                    {{ $status->label() }}
                </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>
</div>