@extends('layouts.app')
@section('title', 'Add Pricing Rule')

@section('content')

{{-- ================= ERRORS ================= --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
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
                <i class="bi bi-calculator me-2"></i> Pricing Rule Configuration
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.pricing.store') }}">
                @csrf

                {{-- ================= PRODUCT ASSOCIATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Product Association</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Insurance Provider <span class="text-danger">*</span>
                            </label>
                            <select name="InsuranceProviderId"
                                    id="Provider-select"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Provider --</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->Id }}">{{ $provider->Name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Product <span class="text-danger">*</span>
                            </label>
                            <select name="Product"
                                    id="Product-select"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Product --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Rule Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="RuleName"
                                   class="form-control form-control-sm"
                                   maxlength="150"
                                   placeholder="Enter rule name"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= COVERAGE & PREMIUM ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Coverage & Premium Parameters</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">
                                Currency <span class="text-danger">*</span>
                            </label>
                            <select name="CurrencyId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Currency --</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->Id }}">
                                        {{ $currency->Code }} - {{ $currency->SymbolNative }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Min Coverage Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="CoverageAmountMin"
                                   class="form-control form-control-sm text-end"
                                   step="0.01"
                                   placeholder="0.00"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Max Coverage Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="CoverageAmountMax"
                                   class="form-control form-control-sm text-end"
                                   step="0.01"
                                   placeholder="0.00"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small ">
                                Premium Rate (%) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="PremiumRate"
                                   class="form-control form-control-sm text-end"
                                   step="0.01"
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= ELIGIBILITY ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Eligibility Criteria</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Minimum Age <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="AgeMin"
                                   class="form-control form-control-sm text-end"
                                   placeholder="Years"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Maximum Age <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="AgeMax"
                                   class="form-control form-control-sm text-end"
                                   placeholder="Years"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Min Tenure (Months) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="TenureMin"
                                   class="form-control form-control-sm text-end"
                                   placeholder="Months"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Max Tenure (Months) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="TenureMax"
                                   class="form-control form-control-sm text-end"
                                   placeholder="Months"
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
                               checked>
                        <label class="form-check-label " for="IsActive">
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
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-sm btn-success px-4"
                            onclick="this.disabled=true; this.innerHTML='Saving…'; this.form.submit();">
                        <i class="bi bi-check-circle me-1"></i> Save Pricing Rule
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

    provider.addEventListener('change', function () {
        product.innerHTML = '<option value="">-- Select Product --</option>';

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
                        product.appendChild(option);
                    });
                });
        }
    });
});
</script>

@endsection
