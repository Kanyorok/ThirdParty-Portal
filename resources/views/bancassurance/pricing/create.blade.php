@extends('layouts.app')
@section('title', 'Add Pricing Rule')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-pill ">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="container mt-4">
    <div class="card  border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-plus-circle me-1" style="font-size:0.9rem;"></i> Add Pricing Rule</h5>
            <a href="{{ route('bancassurance.pricing.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left me-1" style="font-size:0.85rem;"></i> Back
            </a>
        </div>

        <div class="card-body p-3">
            <form method="POST" action="{{ route('bancassurance.pricing.store') }}">
                @csrf

                {{-- Provider, Product & Rule Name --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label for="Provider-select" class="form-label">Select Provider <span class="text-danger">*</span></label>
                        <select name="InsuranceProviderId" id="Provider-select" class="form-select form-select-sm " required>
                            <option value="">-- Select Provider --</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->Id }}">{{ $provider->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="Product-select" class="form-label">Product <span class="text-danger">*</span></label>
                        <select name="Product" id="Product-select" class="form-select form-select-sm " required>
                            <option value="">-- Select Product --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="RuleName" class="form-label">Pricing Rule Name <span class="text-danger">*</span></label>
                        <input type="text" name="RuleName" id="RuleName" class="form-control form-control-sm " maxlength="150" required>
                    </div>
                </div>

                {{-- Coverage & Premium --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Max Coverage <span class="text-danger">*</span></label>
                        <input type="number" name="CoverageAmountMax" class="form-control form-control-sm " step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Min Coverage <span class="text-danger">*</span></label>
                        <input type="number" name="CoverageAmountMin" class="form-control form-control-sm " step="0.01" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Premium Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" name="PremiumRate" class="form-control form-control-sm " step="0.01" required>
                    </div>
                </div>

                {{-- Age --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Min Age <span class="text-danger">*</span></label>
                        <input type="number" name="AgeMin" class="form-control form-control-sm " required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Age <span class="text-danger">*</span></label>
                        <input type="number" name="AgeMax" class="form-control form-control-sm " required>
                    </div>
                </div>

                {{-- Tenure --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Min Tenure (Months) <span class="text-danger">*</span></label>
                        <input type="number" name="TenureMin" class="form-control form-control-sm " required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Tenure (Months) <span class="text-danger">*</span></label>
                        <input type="number" name="TenureMax" class="form-control form-control-sm " required>
                    </div>
                </div>

                {{-- Is Active --}}
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="IsActive" id="primaryCheck" value="1" checked>
                    <label class="form-check-label" for="primaryCheck">Active</label>
                </div>

                {{-- Submit --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-success btn-sm px-3 py-1 rounded-pill "
                        onclick="this.disabled=true; this.innerHTML='<i class=\'bi bi-arrow-clockwise me-1\'></i>Saving...'; this.form.submit();">
                        <i class="bi bi-check-circle me-1" style="font-size:0.85rem;"></i> Save
                    </button>
                    <a href="{{ route('bancassurance.pricing.index') }}" class="btn btn-secondary btn-sm px-3 py-1 rounded-pill">
                        <i class="bi bi-x-circle me-1" style="font-size:0.85rem;"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ProviderSelect = document.getElementById('Provider-select');
    const ProductSelect = document.getElementById('Product-select');

    ProviderSelect.addEventListener('change', function () {
        const ProviderId = this.value;
        ProductSelect.innerHTML = '<option value="">-- Select Product --</option>';

        if (ProviderId) {
            const url = `{{ route('bancassurance.riders.getProductByProvider', ':Id') }}`.replace(':Id', ProviderId);

            fetch(url)
                .then(response => response.json())
                .then(products => {
                    products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.Id;
                        option.textContent = product.Name;
                        ProductSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading products:', error));
        }
    });
});
</script>
@endsection
