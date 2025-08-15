@extends('layouts.app')
@section('title', 'Add Rider')
@section('content')
<div class="container mt-4">
    <h4>➕ Add Rider / Add-on</h4>

    <form method="POST" action="{{ route('bancassurance.riders.store') }}">
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
            <label class="form-label">Rider Name</label>
            <input type="text" name="RiderName" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description (optional)</label>
            <textarea name="Description" class="form-control" rows="2"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Additional Premium </label>
            <input type="number" name="AdditionalPremium" class="form-control" step="0.01" min="0">
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="IsOptional" value="1" id="primaryCheck">
            <label class="form-check-label" for="primaryCheck">IsOptional </label>
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck">
            <label class="form-check-label" for="primaryCheck">IsActive </label>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">✅ Save Rider</button>
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


