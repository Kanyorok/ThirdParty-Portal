@extends('layouts.app')
@section('title', 'Create Policy Proposal')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">New Policy Proposal</h4>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

        <form method="POST" action="{{ route('bancassurance.policies.store') }}">
            @csrf

            {{-- Referral Dropdown --}}
            <div class="mb-3">
                <label class="form-label">Referral (optional)</label>
                <select name="ReferralID" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($referrals as $ref)
                        <option value="{{ $ref->Id }}" {{ old('ReferralID') == $ref->Id ? 'selected' : '' }}>
                            Referral #{{ $ref->ClientIDNumber }} – {{ $ref->ClientName ?? 'Customer' }}
                        </option>
                    @endforeach
                </select>
            </div>

        {{-- Referral Alert (if present) --}}
        @if(isset($referral))
            <div class="alert alert-info">
                <strong>Referral Linked:</strong>
                Based on Referral #{{ $referral->Id }} by Staff ID: {{ $referral->ReferredBy }}
            </div>
            <input type="hidden" name="ReferralID" value="{{ $referral->Id }}">
        @endif

        {{-- Customer & Product --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Customer <span class="text-danger">*</span></label>
                <select name="CustomerID" id="customer-select" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->Id }}" {{ old('CustomerID') == $cust->Id ? 'selected' : '' }}>
                            {{ $cust->FullName }} ({{ $cust->NationalID }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Preferred Insurer <span class="text-danger">*</span></label>
                <select name="InsurerID" id="insurer-select" class="form-select" required>
                    <option value="">-- Optional --</option>
                    @foreach($insurers as $ins)
                        <option value="{{ $ins->Id }}">{{ $ins->Name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Product <span class="text-danger">*</span></label>
                <select id="product-select" name="ProductID" class="form-select" required>
                    <option value="">-- Select Product --</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Rider AddOns <span class="text-danger">*</span></label>
                <select id="rideraddon-select" name="RiderAddOn" class="form-select" required>
                    <option value="">-- Select Rider AddOns --</option>
                </select>
            </div>
        </div>

        {{-- Financials & Frequency --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Sum Assured <span class="text-danger">*</span></label>
                <input type="number" name="SumAssured" class="form-control" value="{{ old('SumAssured') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Premium Amount <span class="text-danger">*</span></label>
                <input type="number" name="PremiumAmount" class="form-control" value="{{ old('PremiumAmount') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Frequency <span class="text-danger">*</span></label>
                <select name="PaymentFrequency" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($paymentfrequencys as $freq)
                        <option value="{{ $freq->ID }}">
                            {{ $freq->Description }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Dates --}}

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Policy Start Date <span class="text-danger">*</span></label>
                <input type="date" name="PolicyStartDate" class="form-control" value="{{ old('PolicyStartDate') }}" required>
                <div class="invalid-feedback" id="error-PolicyStartDate"></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Policy End Date <span class="text-danger">*</span></label>
                <input type="date" name="PolicyEndDate" class="form-control" value="{{ old('PolicyEndDate') }}" required>
                <div class="invalid-feedback" id="error-PolicyEndDate"></div>
            </div>
        </div>


        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">
                    Issued Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="IssuedDate" class="form-control" value="{{ old('IssuedDate') }}" required>
                <div class="invalid-feedback" id="error-IssuedDate"></div>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    Expiry Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="ExpiryDate" class="form-control" value="{{ old('ExpiryDate') }}" required>
                <div class="invalid-feedback" id="error-ExpiryDate"></div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="text-end">
            <button class="btn btn-success">Submit Proposal</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const insurerSelect = document.getElementById('insurer-select');
    const productSelect = document.getElementById('product-select');
    const customerSelect = document.getElementById('customer-select');
    const referralSelect = document.querySelector('select[name="ReferralID"]');
    const productsRoute = @json(route('bancassurance.referrals.referrals.products', ['insurerId' => 'INSURER_ID']));
    const referralDefaultsRoute = @json(route('bancassurance.customers.referral-defaults', ['customerId' => 'CUSTOMER_ID']));
    const riderAddOnsRoute = @json(route('bancassurance.policies.policy.rideraddons', ['productId' => 'PRODUCT_ID']));

    // Store intended product to select after loading
    let intendedProductId = null;

    customerSelect.addEventListener('change', function () {
        const customerId = this.value;
        if (!customerId) return;
        const url = referralDefaultsRoute.replace('CUSTOMER_ID', customerId);
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Set referral
                if (data.referral && referralSelect) {
                    referralSelect.value = data.referral.Id;
                } else if (referralSelect) {
                    referralSelect.value = '';
                }
                // Always set insurer and trigger change
                if (insurerSelect) {
                    insurerSelect.value = data.preferredInsurer || '';
                    intendedProductId = data.product || null;
                    insurerSelect.dispatchEvent(new Event('change'));
                } else {
                    intendedProductId = null;
                }
            });
    });

    insurerSelect.addEventListener('change', function () {
        const insurerId = this.value;
        productSelect.innerHTML = '<option value="">Loading...</option>';
        // Clear Rider AddOns
        document.getElementById('rideraddon-select').innerHTML = '<option value="">-- Select Rider AddOns --</option>';

        if (insurerId) {
            const url = productsRoute.replace('INSURER_ID', insurerId);
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">-- Select Product --</option>';
                    data.forEach(product => {
                        options += `<option value="${product.Id}">${product.Name}</option>`;
                    });
                    productSelect.innerHTML = options;
                    // If we have an intended product to select, do it now
                    if (intendedProductId) {
                        productSelect.value = intendedProductId;
                        productSelect.dispatchEvent(new Event('change'));
                        intendedProductId = null;
                    }
                });
        } else {
            productSelect.innerHTML = '<option value="">-- Select Product --</option>';
        }
    });

    // Rider AddOns: Load and sort when product changes
    productSelect.addEventListener('change', function () {
        const productId = this.value;
        const riderAddOnSelect = document.getElementById('rideraddon-select');
        riderAddOnSelect.innerHTML = '<option value="">Loading...</option>';
        if (productId) {
            const url = riderAddOnsRoute.replace('PRODUCT_ID', productId);
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // Sort alphabetically by Name
                    data.sort((a, b) => a.Name.localeCompare(b.Name));
                    let options = '<option value="">-- Select Rider AddOns --</option>';
                    data.forEach(rider => {
                        options += `<option value="${rider.Id}">${rider.RiderName}</option>`;
                    });
                    riderAddOnSelect.innerHTML = options;
                });
        } else {
            riderAddOnSelect.innerHTML = '<option value="">-- Select Rider AddOns --</option>';
        }
    });


    // Date validation with inline error display
    const form = document.querySelector('form[action*="bancassurance.policies.store"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Clear previous errors
            ['PolicyStartDate','PolicyEndDate','IssuedDate','ExpiryDate'].forEach(function(name) {
                const input = document.querySelector('input[name="'+name+'"]');
                const errorDiv = document.getElementById('error-'+name);
                if (input) input.classList.remove('is-invalid');
                if (errorDiv) errorDiv.textContent = '';
            });

            const start = document.querySelector('input[name="PolicyStartDate"]');
            const end = document.querySelector('input[name="PolicyEndDate"]');
            const issued = document.querySelector('input[name="IssuedDate"]');
            const expiry = document.querySelector('input[name="ExpiryDate"]');

            let hasError = false;
            const startVal = start.value;
            const endVal = end.value;
            const issuedVal = issued.value;
            const expiryVal = expiry.value;

            if (startVal && endVal && endVal <= startVal) {
                hasError = true;
                end.classList.add('is-invalid');
                document.getElementById('error-PolicyEndDate').textContent = 'The policy end date field must be a date after policy start date.';
            }
            if (issuedVal && startVal && issuedVal > startVal) {
                hasError = true;
                issued.classList.add('is-invalid');
                document.getElementById('error-IssuedDate').textContent = 'The issued date field must be a date before or equal to policy start date.';
            }
            if (expiryVal && endVal && expiryVal < endVal) {
                hasError = true;
                expiry.classList.add('is-invalid');
                document.getElementById('error-ExpiryDate').textContent = 'The expiry date field must be a date after or equal to policy end date.';
            }
            if (hasError) {
                e.preventDefault();
            }
        });
    }

    @if(old('InsurerID'))
        insurerSelect.value = "{{ old('InsurerID') }}";
        insurerSelect.dispatchEvent(new Event('change'));
    @endif
});
</script>
@endpush
@endsection
