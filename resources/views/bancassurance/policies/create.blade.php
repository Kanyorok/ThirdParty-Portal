@extends('layouts.app')
@section('title', 'New Policy Proposal')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i> Policy Proposal</h5>
        </div>

        <div class="card-body p-3">
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('bancassurance.policies.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Referral</label>
                    <select name="ReferralID" id="referral-select" class="form-select form-select-sm">
                        <option value="">-- None --</option>
                        @foreach($referrals as $ref)
                            <option value="{{ $ref->Id }}">
                                Name: {{ $ref->customerreferral?->thirdParty?->ThirdPartyName ?? 'Unknown Customer' }}  &nbsp;&nbsp;&nbsp;&nbsp;  Referral: {{ $ref->referredByEmployee->Name ?? 'Referred By' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(isset($referral))
                    <div class="alert alert-info small py-2 px-3 mb-3">
                        Linked to Referral #{{ $referral->Id }} by Staff {{ $referral->ReferredBy }}
                    </div>
                    <input type="hidden" name="ReferralID" value="{{ $referral->Id }}">
                @endif

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="CustomerID" id="customer-select" class="form-select form-select-sm" required>
                            <option value="">-- Select --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->Id }}" {{ old('CustomerID') == $cust->Id ? 'selected' : '' }}>
                                    {{ $cust->ThirdParty->ThirdPartyName ?? '-' }} ({{ $cust->ThirdParty->RegistrationNumber ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Insurer</label>
                        <select name="InsurerID" id="insurer-select" class="form-select form-select-sm">
                            <option value="">-- Select --</option>
                            @foreach($insurers as $ins)
                                <option value="{{ $ins->Id }}">{{ $ins->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select id="product-select" name="ProductID" class="form-select form-select-sm" required>
                            <option value="">-- Select --</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rider Add-On</label>
                    <select id="rideraddon-select" name="RiderAddOn" class="form-select form-select-sm">
                        <option value="">-- Select --</option>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Sum Assured <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="SumAssured"
                            class="form-control form-control-sm"
                            value="{{ old('SumAssured') !== null ? number_format((float) old('SumAssured'), 2, '.', '') : '' }}"
                            required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Premium <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="PremiumAmount"
                            class="form-control form-control-sm"
                            value="{{ old('PremiumAmount') !== null ? number_format((float) old('PremiumAmount'), 2, '.', '') : '' }}"
                            required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                        <select name="PaymentFrequency" class="form-select form-select-sm" required>
                            <option value="">-- Select --</option>
                            @foreach($paymentfrequencys as $freq)
                                <option value="{{ $freq->ID }}" {{ old('PaymentFrequency') == $freq->ID ? 'selected' : '' }}>
                                    {{ $freq->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="PolicyStartDate" class="form-control form-control-sm"
                            value="{{ old('PolicyStartDate') }}" required>
                        <div class="invalid-feedback" id="error-PolicyStartDate"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="PolicyEndDate" class="form-control form-control-sm"
                            value="{{ old('PolicyEndDate') }}" required>
                        <div class="invalid-feedback" id="error-PolicyEndDate"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Issued <span class="text-danger">*</span></label>
                        <input type="date" name="IssuedDate" class="form-control form-control-sm"
                            value="{{ old('IssuedDate') }}" required>
                        <div class="invalid-feedback" id="error-IssuedDate"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Expiry <span class="text-danger">*</span></label>
                        <input type="date" name="ExpiryDate" class="form-control form-control-sm"
                            value="{{ old('ExpiryDate') }}" required>
                        <div class="invalid-feedback" id="error-ExpiryDate"></div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <a href="{{ route('bancassurance.policies.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left-circle me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success btn-sm px-4"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        <i class="bi bi-send-check me-1"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const customerSelect = document.getElementById('customer-select');
    const referralSelect = document.getElementById('referral-select');
    const insurerSelect  = document.getElementById('insurer-select');
    const productSelect  = document.getElementById('product-select');
    const riderSelect    = document.getElementById('rideraddon-select');

    const referralsByCustomerRoute = @json(
        route('bancassurance.policies.customers.referrals', ['customerId' => 'CUSTOMER_ID'])
    );

    const productsRoute = @json(
        route('bancassurance.referrals.referrals.products', ['insurerId' => 'INSURER_ID'])
    );

    const riderAddOnsRoute = @json(
        route('bancassurance.policies.policy.rideraddons', ['productId' => 'PRODUCT_ID'])
    );

    /* ------------------------------
       CUSTOMER → REFERRALS
    -------------------------------*/
    customerSelect.addEventListener('change', function () {

        const customerId = this.value;
        referralSelect.innerHTML = '<option>Loading...</option>';

        if (!customerId) {
            referralSelect.innerHTML = '<option value="">-- None --</option>';
            return;
        }

        fetch(referralsByCustomerRoute.replace('CUSTOMER_ID', customerId))
            .then(res => res.json())
            .then(referrals => {
                let options = '<option value="">-- None --</option>';

                referrals.forEach(ref => {
                    options += `
                        <option value="${ref.Id}">
                            ${ref.customerreferral?.thirdParty?.ThirdPartyName ?? 'Customer'}
                            — Referred by ${ref.referredByEmployee?.Name ?? 'Staff'}
                        </option>
                    `;
                });

                referralSelect.innerHTML = options;
            });
    });

    /* ------------------------------
       INSURER → PRODUCTS
    -------------------------------*/
    insurerSelect.addEventListener('change', function () {

        const insurerId = this.value;
        productSelect.innerHTML = '<option>Loading...</option>';
        riderSelect.innerHTML = '<option value="">-- Select --</option>';

        if (!insurerId) {
            productSelect.innerHTML = '<option value="">-- Select --</option>';
            return;
        }

        fetch(productsRoute.replace('INSURER_ID', insurerId))
            .then(res => res.json())
            .then(products => {
                let options = '<option value="">-- Select --</option>';
                products.forEach(p =>
                    options += `<option value="${p.Id}">${p.Name}</option>`
                );
                productSelect.innerHTML = options;
            });
    });

    /* ------------------------------
       PRODUCT → RIDERS
    -------------------------------*/
    productSelect.addEventListener('change', function () {

        const productId = this.value;
        riderSelect.innerHTML = '<option>Loading...</option>';

        if (!productId) {
            riderSelect.innerHTML = '<option value="">-- Select --</option>';
            return;
        }

        fetch(riderAddOnsRoute.replace('PRODUCT_ID', productId))
            .then(res => res.json())
            .then(riders => {
                let options = '<option value="">-- Select --</option>';
                riders.forEach(r =>
                    options += `<option value="${r.Id}">${r.RiderName}</option>`
                );
                riderSelect.innerHTML = options;
            });
    });

});
</script>
@endpush
@endsection
