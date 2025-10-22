@extends('layouts.app')
@section('title', 'Add Rider & Add On')
@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Add Rider</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('bancassurance.riders.store') }}">
                @csrf

                <!-- Provider & Product (same row) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="Provider-select" class="form-label">
                            Select Provider <span class="text-danger">*</span>
                        </label>
                        <select name="InsuranceProviderId" id="Provider-select" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->Id }}">{{ $provider->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="Product-select" class="form-label">
                            Product <span class="text-danger">*</span>
                        </label>
                        <select name="Product" id="Product-select" class="form-select" required>
                            <option value="">-- Select --</option>
                        </select>
                    </div>
                </div>

                <!-- Rider Name & Additional Premium (same row) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="RiderName" class="form-label">
                            Rider Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="RiderName" id="RiderName" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="AdditionalPremium" class="form-label">
                            Additional Premium <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="AdditionalPremium" id="AdditionalPremium"
                               class="form-control" step="0.01" min="0" required>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="Description" class="form-label">Description</label>
                    <textarea name="Description" id="Description" class="form-control" rows="2"></textarea>
                </div>

                <!-- Checkboxes -->
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="IsOptional" value="1" id="IsOptional" checked>
                    <label class="form-check-label" for="IsOptional">Is Optional</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="IsActive" checked>
                    <label class="form-check-label" for="IsActive">Is Active</label>
                </div>

                <!-- Submit -->
                <div class="text-end">
                    <a href="{{ route('bancassurance.riders.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Save Rider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ProviderSelect = document.getElementById('Provider-select');
        const ProductSelect = document.getElementById('Product-select');

        ProviderSelect.addEventListener('change', function () {
            const ProviderId = this.value;

            // Reset Product dropdown
            ProductSelect.innerHTML = '<option value="">-- Select a Product --</option>';

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
                    .catch(error => console.error('Error loading Product:', error));
            }
        });
    });
</script>
@endsection
