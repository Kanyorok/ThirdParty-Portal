@extends('layouts.app')
@section('title', 'Add Rider & Add On')
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

<div class="container mt-5" style="max-width: 850px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-puzzle me-2"></i>Rider & Add-On Information
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.riders.store') }}">
                @csrf

                {{-- Provider & Product Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-link-45deg me-2"></i>Associated Product
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="Provider-select" class="form-label fw-semibold">
                                Insurance Provider <span class="text-danger">*</span>
                            </label>
                            <select name="InsuranceProviderId" id="Provider-select" class="form-select rounded-pill shadow-sm" required>
                                <option value="">-- Select Insurance Provider --</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->Id }}">{{ $provider->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="Product-select" class="form-label fw-semibold">
                                Product <span class="text-danger">*</span>
                            </label>
                            <select name="Product" id="Product-select" class="form-select rounded-pill shadow-sm" required>
                                <option value="">-- Select Product --</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Rider Details Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-info-circle me-2"></i>Rider Details
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="RiderName" class="form-label fw-semibold">
                                Rider Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="RiderName" id="RiderName" 
                                class="form-control rounded-pill shadow-sm" 
                                placeholder="Enter rider name" required>
                        </div>
                        <div class="col-md-5">
                            <label for="AdditionalPremium" class="form-label fw-semibold">
                                Additional Premium <span class="text-danger">*</span>
                            </label>
                            <div class="input-group shadow-sm rounded-pill">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="bi bi-currency-dollar"></i>
                                </span>
                                <input type="number" name="AdditionalPremium" id="AdditionalPremium"
                                    class="form-control border-0 rounded-end-pill text-end" 
                                    step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="Description" class="form-label fw-semibold">Description</label>
                        <textarea name="Description" id="Description" 
                            class="form-control rounded-4 shadow-sm" rows="4"
                            placeholder="Enter rider description and coverage details (optional)"></textarea>
                        <small class="text-muted">Describe what this rider covers and its benefits</small>
                    </div>
                </div>

                {{-- Configuration Section --}}
                <div class="mb-4">
                    <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                        <i class="bi bi-gear me-2"></i>Rider Configuration
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="IsOptional" value="1" 
                                    id="IsOptional" checked style="cursor: pointer;">
                                <label class="form-check-label fw-semibold" for="IsOptional" style="cursor: pointer;">
                                    Optional Rider
                                    <small class="text-muted d-block">Customers can choose to add this rider</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="IsActive" value="1" 
                                    id="IsActive" checked style="cursor: pointer;">
                                <label class="form-check-label fw-semibold" for="IsActive" style="cursor: pointer;">
                                    Active Rider
                                    <small class="text-muted d-block">Enable this rider for selection</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                    <a href="{{ route('bancassurance.riders.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                        <i class="bi bi-check-circle me-2"></i>Save Rider
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
