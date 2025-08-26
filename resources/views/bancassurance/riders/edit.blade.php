@extends('layouts.app')
@section('title', 'Edit Rider')
@section('content')
<div class="container mt-4">
    <form method="POST" action="{{ route('bancassurance.riders.update', $rider->Id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Select Provider <span class="text-danger">*</span></label>
            <input type="text" class="form-control" value="{{ optional($providers->firstWhere('Id', $rider->InsuranceProviderId))->InsuranceProviderNO ?? 'N/A' }}" readonly>
            <input type="hidden" name="InsuranceProviderId" value="{{ $rider->InsuranceProviderId }}">
        </div>

        <div class="mb-3">
             <label class="form-label">Product <span class="text-danger">*</span></label>
            <select name="Product" id='Product-select' class="form-select" required>
                <option value="">-- Select --</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Rider Name <span class="text-danger">*</span></label>
            <input type="text" name="RiderName" class="form-control" value="{{ $rider->RiderName }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description (optional)</label>
            <textarea name="Description" class="form-control" rows="2">{{ $rider->Description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Additional Premium <span class="text-danger">*</span></label>
            <input type="number" name="AdditionalPremium" class="form-control" step="0.01" min="0" value="{{ $rider->AdditionalPremium }}">
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="IsOptional" value="1" id="optionalCheck" {{ $rider->IsOptional ? 'checked' : '' }}>
            <label class="form-check-label" for="optionalCheck">Is Optional</label>
        </div>

        <div class="mb-3 form-check">
            <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="activeCheck" {{ $rider->IsActive ? 'checked' : '' }}>
            <label class="form-check-label" for="activeCheck">Is Active</label>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">💾 Update Rider</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ProviderSelect = document.getElementById('Provider-select');
        const ProductSelect = document.getElementById('Product-select');
        const selectedProductId = "{{ $rider->Product }}";

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
