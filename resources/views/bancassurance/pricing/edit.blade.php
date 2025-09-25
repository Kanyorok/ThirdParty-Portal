@extends('layouts.app')
@section('title', 'Edit Pricing Rule')
@section('content')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
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
    <form method="POST" action="{{ route('bancassurance.pricing.update', $rule->Id) }}">
        @csrf
        @method('PUT')

         <div class="mb-3">
            <label class="form-label">Select Provider <span class="text-danger">*</span></label>
            <select name="InsuranceProviderId" id="Provider-select" class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider->Id }}" {{ $provider->Id == $rule->InsuranceProviderId ? 'selected' : '' }}>
                        {{ $provider->InsuranceProviderNO }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
             <label class="form-label">Product <span class="text-danger">*</span></label>
            <select name="Product" id='Product-select' class="form-select" required>
                <option value="">-- Select --</option>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Min Age <span class="text-danger">*</span></label>
                <input type="number" name="MinAge" class="form-control" value="{{ $rule->AgeMin }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Max Age <span class="text-danger">*</span></label>
                <input type="number" name="MaxAge" class="form-control" value="{{ $rule->AgeMax }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Min Coverage <span class="text-danger">*</span></label>
                <input type="number" name="MinCoverage" class="form-control" step="0.01" value="{{ $rule->CoverageAmountMin }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Max Coverage <span class="text-danger">*</span></label>
                <input type="number" name="MaxCoverage" class="form-control" step="0.01" value="{{ $rule->CoverageAmountMax }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Min Tenure (Years) <span class="text-danger">*</span></label>
                <input type="number" name="MinTenure" class="form-control" value="{{ $rule->TenureMin }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Max Tenure (Years) <span class="text-danger">*</span></label>
                <input type="number" name="MaxTenure" class="form-control" value="{{ $rule->TenureMax }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Premium Rate (%) <span class="text-danger">*</span></label>
            <input type="number" name="PremiumRate" class="form-control" step="0.01" value="{{ $rule->PremiumRate }}" required>
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="activeCheck" {{ $rule->IsActive ? 'checked' : '' }}>
            <label class="form-check-label" for="activeCheck">Is Active</label>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">Update Rule</button>
            <a href="{{ route('bancassurance.pricing.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ProviderSelect = document.getElementById('Provider-select');
        const ProductSelect = document.getElementById('Product-select');
        const selectedProductId = "{{ $rule->Product }}";

        ProviderSelect.addEventListener('change', function () {
            const ProviderId = this.value;
            ProductSelect.innerHTML = '<option value="">-- Select a Product--</option>';

            if (ProviderId) {
                const url = `{{ route('bancassurance.riders.getProductByProvider', ':Id') }}`.replace(':Id', ProviderId);

                fetch(url)
                    .then(response => response.json())
                    .then(products => {
                        products.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.Id;
                            option.textContent = product.Name;
                            if (product.Id == selectedProductId) {
                                option.selected = true;
                            }
                            ProductSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error loading Product:', error));
            }
        });

        // Trigger change if editing to load products immediately
        if (ProviderSelect.value) {
            ProviderSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
