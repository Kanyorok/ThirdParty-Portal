@extends('layouts.app')
@section('title', 'Add Pricing Rule')
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
    <div class="card shadow-lg rounded-3">
        <div class="card-body">
            <form method="POST" action="{{ route('bancassurance.pricing.store') }}">
                @csrf

                {{-- Provider, Product & Rule Name (same row) --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="Provider-select" class="form-label">
                            Select Provider <span class="text-danger">*</span>
                        </label>
                        <select name="InsuranceProviderId" id="Provider-select" class="form-select" required>
                            <option value="">-- Select Provider --</option>
                            @foreach($providers as $provider)
                                <option value="{{ $provider->Id }}">{{ $provider->Name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="Product-select" class="form-label">
                            Product <span class="text-danger">*</span>
                        </label>
                        <select name="Product" id="Product-select" class="form-select" required>
                            <option value="">-- Select Product --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="RuleName" class="form-label">
                            Pricing Rule Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="RuleName" id="RuleName" 
                               class="form-control" maxlength="150" required>
                    </div>
                </div>

                {{-- Coverage & Premium --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Max Coverage <span class="text-danger">*</span></label>
                        <input type="number" name="CoverageAmountMax" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Min Coverage <span class="text-danger">*</span></label>
                        <input type="number" name="CoverageAmountMin" class="form-control" step="0.01" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Premium Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" name="PremiumRate" class="form-control" step="0.01" required>
                    </div>
                </div>

                {{-- Age --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Min Age <span class="text-danger">*</span></label>
                        <input type="number" name="AgeMin" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Max Age <span class="text-danger">*</span></label>
                        <input type="number" name="AgeMax" class="form-control" required>
                    </div>
                </div>

                {{-- Tenure --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Min Tenure (Months) <span class="text-danger">*</span></label>
                        <input type="number" name="TenureMin" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Max Tenure (Months) <span class="text-danger">*</span></label>
                        <input type="number" name="TenureMax" class="form-control" required>
                    </div>
                </div>

                {{-- Is Active --}}
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="IsActive" value="1" id="primaryCheck" checked>
                    <label class="form-check-label" for="primaryCheck">Active</label>
                </div>

                {{-- Submit --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-success px-4">Save Pricing Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script --}}
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
                    .catch(error => console.error('Error loading Product:', error));
            }
        });
    });
</script>
@endsection
