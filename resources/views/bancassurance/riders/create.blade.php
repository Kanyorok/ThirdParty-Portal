@extends('layouts.app')
@section('title', 'Add Rider & Add On')

@section('content')

{{-- ================= ERRORS ================= --}}
@if ($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0 small">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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

<div class="container mt-4" style="max-width: 850px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary text-white rounded-top-4 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-puzzle me-2"></i> Add Rider
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.riders.store') }}">
                @csrf

                {{-- ================= BASIC INFORMATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Basic Information</h6>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="small fw-semibold">
                                Rider Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="RiderName"
                                   class="form-control form-control-sm"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= PROVIDER & PRODUCT ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Provider & Product</h6>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small fw-semibold">
                                Provider <span class="text-danger">*</span>
                            </label>
                            <select name="InsuranceProviderId"
                                    id="Provider-select"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select --</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->Id }}">{{ $provider->Name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-2">
                            <label class="small fw-semibold">
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

                {{-- ================= PRICING ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Pricing</h6>

                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="small fw-semibold">
                                Additional Premium <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="AdditionalPremium"
                                   class="form-control form-control-sm"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="small fw-semibold">
                                Currency <span class="text-danger">*</span>
                            </label>
                            <select name="CurrencyId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select --</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->Id }}">
                                        {{ $currency->Code }} - {{ $currency->SymbolNative }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= STATUS & DESCRIPTION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Status & Description</h6>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="IsOptional"
                                       value="1"
                                       id="IsOptional"
                                       checked>
                                <label class="form-check-label small" for="IsOptional">
                                    Optional Rider
                                </label>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="IsActive"
                                       value="1"
                                       id="IsActive"
                                       checked>
                                <label class="form-check-label fw-semibold" for="IsActive">
                                    Active Rider
                                    <small class="text-muted d-block">
                                        Enable this rider for selection
                                    </small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <label class="small fw-semibold">Description</label>
                            <textarea name="Description"
                                      class="form-control form-control-sm"
                                      rows="2"></textarea>
                        </div>
                    </div>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('bancassurance.riders.index') }}"
                       class="btn btn-sm btn-outline-secondary">
                        Back
                    </a>

                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-check-circle me-1"></i> Save Rider
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
                        product.appendChild(option);
                    });
                });
        }
    });
});
</script>

@endsection
