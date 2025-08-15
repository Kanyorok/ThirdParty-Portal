@extends('layouts.app')
@section('title', 'Add Pricing Rule')

@section('content')
<div class="container mt-4">
    <h4>➕ Add Pricing Rule</h4>

    <form method="POST" action="{{ route('bancassurance.pricing.store') }}">
        @csrf

<div class="mb-3">
            <label class="form-label">Select Provider </label>
            <select name="InsuranceProviderId" id='Provider-select' class="form-select" required>
                <option value="">-- Select --</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider->Id }}">{{ $provider->InsuranceProviderNO }}</option>
                @endforeach
              </select>
        </div> 

        <div class="mb-3">
             <label class="form-label">Product</label>
            <select name="Product" id='Product-select' class="form-select" required>
                <option value="">-- Select --</option>
            </select>
        </div>

        <div class="mb-3">
                <label class="form-label"> Rule Name</label>
                <input type="text" name="RuleName" class="form-control" required maxlength="150">
            </div>

            <div class="col-md-3 mb-3">
                <label for="CoverageAmountMax" class="form-label">Max Coverage </label>
                <input type="number" name="CoverageAmountMax" class="form-control" required step="0.01">
            </div>

            <div class="col-md-3 mb-3">
                <label for="CoverageAmountMin" class="form-label">Min Coverage </label>
                <input type="number" name="CoverageAmountMin" class="form-control" required step="0.01">
            </div>

            <div class="col-md-3 mb-3">
                <label for="PremiumRate" class="form-label">Premium Rate (%)</label>
                <input type="number" name="PremiumRate" class="form-control" step="0.01" required>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="AgeMin" class="form-label">Min Age</label>
                <input type="number" name="AgeMin" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="AgeMax" class="form-label">Max Age</label>
                <input type="number" name="AgeMax" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="TenureMin" class="form-label">Min Tenure (Months)</label>
                <input type="number" name="TenureMin" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="TenureMax" class="form-label">Max Tenure (Months)</label>
                <input type="number" name="TenureMax" class="form-control" required>
            </div>
       <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck">
            <label class="form-check-label" for="primaryCheck">IsActive </label>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">💾 Save Pricing Rule</button>
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ProviderSelect = document.getElementById('Provider-select');
        const ProductSelect = document.getElementById('Product-select');

        ProviderSelect.addEventListener('change', function () {
            const ProviderId = this.value;

            // Reset Block dropdown
            ProductSelect.innerHTML = '<option value="">-- Select a Product--</option>';

            if (ProviderId) {
                // Construct the URL from the named route
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
                    .catch(error => console.error('Error loading Product:', error));
            }
        });
    });
</script>
@endsection
