@extends('layouts.app')
@section('title', 'Edit Pricing Rule')

@section('content')

{{-- ================= ERRORS ================= --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="container mt-4" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary text-white rounded-top-4 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-pencil-square me-2"></i> Edit Pricing Rule
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.pricing.update', $rule->Id) }}">
                @csrf
                @method('PUT')

                {{-- ================= PRODUCT ASSOCIATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Product Association</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Insurance Provider <span class="text-danger">*</span>
                            </label>
                            <select name="InsuranceProviderId"
                                    id="Provider-select"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select --</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->Id }}"
                                        {{ $provider->Id == $rule->InsuranceProviderId ? 'selected' : '' }}>
                                        {{ $provider->InsuranceProviderNO }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Product <span class="text-danger">*</span>
                            </label>
                            <select name="Product"
                                    id="Product-select"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= ELIGIBILITY ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Eligibility Criteria</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Minimum Age <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="MinAge"
                                   class="form-control form-control-sm text-end"
                                   value="{{ $rule->AgeMin }}"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Maximum Age <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="MaxAge"
                                   class="form-control form-control-sm text-end"
                                   value="{{ $rule->AgeMax }}"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= COVERAGE & PREMIUM ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Coverage & Premium</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Currency <span class="text-danger">*</span>
                            </label>
                            <select name="CurrencyId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Currency --</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->Id }}"
                                        {{ $currency->Id == $rule->CurrencyId ? 'selected' : '' }}>
                                        {{ $currency->Code }} - {{ $currency->SymbolNative }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Premium Rate (%) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="PremiumRate"
                                   class="form-control form-control-sm text-end"
                                   step="0.01"
                                   value="{{ $rule->PremiumRate }}"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Min Coverage Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="MinCoverage"
                                   class="form-control form-control-sm text-end"
                                   step="0.01"
                                   value="{{ $rule->CoverageAmountMin }}"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Max Coverage Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="MaxCoverage"
                                   class="form-control form-control-sm text-end"
                                   step="0.01"
                                   value="{{ $rule->CoverageAmountMax }}"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= TENURE ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Tenure</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Min Tenure (Years) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="MinTenure"
                                   class="form-control form-control-sm text-end"
                                   value="{{ $rule->TenureMin }}"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Max Tenure (Years) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="MaxTenure"
                                   class="form-control form-control-sm text-end"
                                   value="{{ $rule->TenureMax }}"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Rule Status</h6>

                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="IsActive"
                               id="IsActive"
                               value="1"
                               {{ $rule->IsActive ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="IsActive">
                            Active Pricing Rule
                            <small class="text-muted d-block">
                                Enable this pricing rule for calculations
                            </small>
                        </label>
                    </div>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('bancassurance.pricing.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-sm btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i> Update Rule
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const provider = document.getElementById('Provider-select');
    const product  = document.getElementById('Product-select');
    const selectedProductId = "{{ $rule->Product }}";

    provider.addEventListener('change', function () {
        product.innerHTML = '<option value="">-- Select --</option>';

        if (this.value) {
            const url = `{{ route('bancassurance.riders.getProductByProvider', ':Id') }}`
                .replace(':Id', this.value);

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.Id;
                        option.textContent = item.Name;
                        if (item.Id == selectedProductId) option.selected = true;
                        product.appendChild(option);
                    });
                });
        }
    });

    if (provider.value) {
        provider.dispatchEvent(new Event('change'));
    }
});
</script>

@endsection
