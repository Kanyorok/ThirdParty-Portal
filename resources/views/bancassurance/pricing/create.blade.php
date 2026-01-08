@extends('layouts.app')
@section('title', 'Add Pricing Rule')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="container mt-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-calculator me-2"></i>Pricing Rule Configuration
            </h5>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.pricing.store') }}">
                @csrf

                {{-- Product Association Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-link-45deg me-2"></i>Product Association
                    </h6>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="Provider-select" class="form-label fw-semibold">
                                Insurance Provider <span class="text-danger">*</span>
                            </label>
                            <select name="InsuranceProviderId" id="Provider-select" class="form-select rounded-pill shadow-sm" required>
                                <option value="">-- Select Provider --</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->Id }}">{{ $provider->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="Product-select" class="form-label fw-semibold">
                                Product <span class="text-danger">*</span>
                            </label>
                            <select name="Product" id="Product-select" class="form-select rounded-pill shadow-sm" required>
                                <option value="">-- Select Product --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="RuleName" class="form-label fw-semibold">
                                Rule Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="RuleName" id="RuleName" 
                                class="form-control rounded-pill shadow-sm" 
                                placeholder="Enter rule name" maxlength="150" required>
                        </div>
                    </div>
                </div>

                {{-- Coverage & Premium Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-cash-stack me-2"></i>Coverage & Premium Parameters
                    </h6>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Min Coverage Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group shadow-sm rounded-pill">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="bi bi-currency-dollar"></i>
                                </span>
                                <input type="number" name="CoverageAmountMin" 
                                    class="form-control border-0 rounded-end-pill text-end" 
                                    step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Max Coverage Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group shadow-sm rounded-pill">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="bi bi-currency-dollar"></i>
                                </span>
                                <input type="number" name="CoverageAmountMax" 
                                    class="form-control border-0 rounded-end-pill text-end" 
                                    step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Premium Rate (%) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group shadow-sm rounded-pill">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="bi bi-percent"></i>
                                </span>
                                <input type="number" name="PremiumRate" 
                                    class="form-control border-0 rounded-end-pill text-end" 
                                    step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Eligibility Criteria Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-filter me-2"></i>Eligibility Criteria
                    </h6>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>Minimum Age <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="AgeMin" 
                                class="form-control rounded-pill shadow-sm text-end" 
                                placeholder="Years" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i>Maximum Age <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="AgeMax" 
                                class="form-control rounded-pill shadow-sm text-end" 
                                placeholder="Years" required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-range me-1"></i>Min Tenure (Months) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="TenureMin" 
                                class="form-control rounded-pill shadow-sm text-end" 
                                placeholder="Months" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-range me-1"></i>Max Tenure (Months) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="TenureMax" 
                                class="form-control rounded-pill shadow-sm text-end" 
                                placeholder="Months" required>
                        </div>
                    </div>
                </div>

                {{-- Rule Status Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-toggles me-2"></i>Rule Status
                    </h6>
                    
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="IsActive" 
                            id="primaryCheck" value="1" checked style="cursor: pointer;">
                        <label class="form-check-label fw-semibold" for="primaryCheck" style="cursor: pointer;">
                            Active Pricing Rule
                            <small class="text-muted d-block">Enable this pricing rule for calculations</small>
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                    <a href="{{ route('bancassurance.pricing.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm"
                        onclick="this.disabled=true; this.innerHTML='<i class=\'bi bi-arrow-clockwise me-1\'></i>Saving...'; this.form.submit();">
                        <i class="bi bi-check-circle me-2"></i>Save Pricing Rule
                    </button>
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
