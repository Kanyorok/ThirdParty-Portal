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

{{-- ================= COMPACT STYLES ================= --}}
<style>
    fieldset {
        padding: .30rem;
        border-radius: .375rem;
    }
    legend {
        font-size: .85rem;
        padding: 0 .5rem;
        width: auto;
    }
    label {
        font-size: .85rem;
        /* margin-bottom: .25rem; */
    }
</style>

<div class="container mt-3">
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white py-2">
            <h6 class="mb-0">Add Rider</h6>
        </div>

        <div class="card-body py-3">
            <form method="POST" action="{{ route('bancassurance.riders.store') }}">
                @csrf

                {{-- ================= BASIC INFO ================= --}}
                <fieldset class="border mb-3">
                    <legend class="fw-semibold text-primary">Basic Info</legend>
                    <div class="row">
                        <div class="col-md-5 mb-2">
                            <label>Rider Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="RiderName"
                                   class="form-control form-control-sm"
                                   required>
                        </div>
                    </div>
                </fieldset>

                {{-- ================= PROVIDER & PRODUCT ================= --}}
                <fieldset class="border mb-3">
                    <legend class="fw-semibold text-info">Provider & Product</legend>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label>Provider <span class="text-danger">*</span></label>
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
                            <label>Product <span class="text-danger">*</span></label>
                            <select name="Product"
                                    id="Product-select"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select --</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                {{-- ================= PRICING ================= --}}
                <fieldset class="border mb-3">
                    <legend class="fw-semibold text-success">Pricing</legend>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label>Additional Premium *</label>
                            <input type="number"
                                   name="AdditionalPremium"
                                   class="form-control form-control-sm"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label>Currency *</label>
                            <select name="CurrencyId"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select --</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->Id }}">{{ $currency->Code }} - {{ $currency->SymbolNative }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                {{-- ================= STATUS & DESCRIPTION ================= --}}
                <fieldset class="border mb-3">
                    <legend class="fw-semibold text-secondary">Status & Notes</legend>
                    <div class="row align-items-start">
                        <div class="col-md-4">
                            <div class="form-check mb-1">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="IsOptional"
                                       value="1"
                                       id="IsOptional"
                                       checked>
                                <label class="form-check-label small">Optional Rider</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="IsActive"
                                       value="1"
                                       id="IsActive"
                                       checked>
                                <label class="form-check-label small">Active</label>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label>Description</label>
                            <textarea name="Description"
                                      class="form-control form-control-sm"
                                      rows="2"></textarea>
                        </div>
                    </div>
                </fieldset>

                {{-- ================= ACTIONS ================= --}}
                <div class="text-end">
                    <a href="{{ route('bancassurance.riders.index') }}"
                       class="btn btn-sm btn-outline-secondary me-2">
                        Back
                    </a>

                    <button type="submit" class="btn btn-sm btn-success">
                        Save Rider
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
    const product = document.getElementById('Product-select');

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
