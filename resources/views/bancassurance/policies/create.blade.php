@extends('layouts.app')
@section('title', 'New Policy Proposal')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4 py-2">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i> Policy Proposal</h5>
        </div>

        <div class="card-body p-3">
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 rounded-pill">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('bancassurance.policies.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Referral</label>
                    <select name="ReferralID" class="form-select form-select-sm rounded-pill">
                        <option value="">-- None --</option>
                        @foreach($referrals as $ref)
                            <option value="{{ $ref->Id }}" {{ old('ReferralID') == $ref->Id ? 'selected' : '' }}>
                                #{{ $ref->ClientIDNumber }} – {{ $ref->ClientName ?? 'Customer' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(isset($referral))
                    <div class="alert alert-info small py-2 px-3 mb-3 rounded-pill">
                        Linked to Referral #{{ $referral->Id }} by Staff {{ $referral->ReferredBy }}
                    </div>
                    <input type="hidden" name="ReferralID" value="{{ $referral->Id }}">
                @endif

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="CustomerID" id="customer-select" class="form-select form-select-sm rounded-pill" required>
                            <option value="">-- Select --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->Id }}" {{ old('CustomerID') == $cust->Id ? 'selected' : '' }}>
                                    {{ $cust->ThirdParty->ThirdPartyName ?? '-'}} ({{ $cust->NationalID ?? '-'}})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Insurer</label>
                        <select name="InsurerID" id="insurer-select" class="form-select form-select-sm rounded-pill">
                            <option value="">-- Select --</option>
                            @foreach($insurers as $ins)
                                <option value="{{ $ins->Id }}">{{ $ins->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select id="product-select" name="ProductID" class="form-select form-select-sm rounded-pill" required>
                            <option value="">-- Select --</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rider Add-On</label>
                    <select id="rideraddon-select" name="RiderAddOn" class="form-select form-select-sm rounded-pill">
                        <option value="">-- Select --</option>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Sum Assured *</label>
                        <input type="number" step="0.01" name="SumAssured"
                            class="form-control form-control-sm rounded-pill"
                            value="{{ old('SumAssured') !== null ? number_format((float) old('SumAssured'), 2, '.', '') : '' }}"
                            required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Premium *</label>
                        <input type="number" step="0.01" name="PremiumAmount"
                            class="form-control form-control-sm rounded-pill"
                            value="{{ old('PremiumAmount') !== null ? number_format((float) old('PremiumAmount'), 2, '.', '') : '' }}"
                            required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Frequency *</label>
                        <select name="PaymentFrequency" class="form-select form-select-sm rounded-pill" required>
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
                        <label class="form-label">Start Date *</label>
                        <input type="date" name="PolicyStartDate" class="form-control form-control-sm rounded-pill"
                            value="{{ old('PolicyStartDate') }}" required>
                        <div class="invalid-feedback" id="error-PolicyStartDate"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">End Date *</label>
                        <input type="date" name="PolicyEndDate" class="form-control form-control-sm rounded-pill"
                            value="{{ old('PolicyEndDate') }}" required>
                        <div class="invalid-feedback" id="error-PolicyEndDate"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Issued *</label>
                        <input type="date" name="IssuedDate" class="form-control form-control-sm rounded-pill"
                            value="{{ old('IssuedDate') }}" required>
                        <div class="invalid-feedback" id="error-IssuedDate"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Expiry *</label>
                        <input type="date" name="ExpiryDate" class="form-control form-control-sm rounded-pill"
                            value="{{ old('ExpiryDate') }}" required>
                        <div class="invalid-feedback" id="error-ExpiryDate"></div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4"
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
    const insurerSelect = document.getElementById('insurer-select');
    const productSelect = document.getElementById('product-select');
    const customerSelect = document.getElementById('customer-select');
    const referralSelect = document.querySelector('select[name="ReferralID"]');
    const riderSelect = document.getElementById('rideraddon-select');

    const productsRoute = @json(route('bancassurance.referrals.referrals.products', ['insurerId' => 'INSURER_ID']));
    const referralDefaultsRoute = @json(route('bancassurance.customers.referral-defaults', ['customerId' => 'CUSTOMER_ID']));
    const riderAddOnsRoute = @json(route('bancassurance.policies.policy.rideraddons', ['productId' => 'PRODUCT_ID']));

    document.querySelectorAll('input[type="number"][step="0.01"]').forEach(el => {
        el.addEventListener('blur', function () {
            if (this.value) this.value = parseFloat(this.value).toFixed(2);
        });
    });

    let intendedProductId = null;

    customerSelect.addEventListener('change', function () {
        const id = this.value;
        if (!id) return;
        fetch(referralDefaultsRoute.replace('CUSTOMER_ID', id))
            .then(r => r.json())
            .then(data => {
                referralSelect.value = data.referral?.Id || '';
                insurerSelect.value = data.preferredInsurer || '';
                intendedProductId = data.product || null;
                insurerSelect.dispatchEvent(new Event('change'));
            });
    });

    insurerSelect.addEventListener('change', function () {
        const id = this.value;
        productSelect.innerHTML = '<option>Loading...</option>';
        riderSelect.innerHTML = '<option>-- Select --</option>';
        if (!id) return productSelect.innerHTML = '<option>-- Select --</option>';
        fetch(productsRoute.replace('INSURER_ID', id))
            .then(r => r.json())
            .then(data => {
                let opts = '<option value="">-- Select --</option>';
                data.forEach(p => opts += `<option value="${p.Id}">${p.Name}</option>`);
                productSelect.innerHTML = opts;
                if (intendedProductId) {
                    productSelect.value = intendedProductId;
                    productSelect.dispatchEvent(new Event('change'));
                    intendedProductId = null;
                }
            });
    });

    productSelect.addEventListener('change', function () {
        const id = this.value;
        riderSelect.innerHTML = '<option>Loading...</option>';
        if (!id) return riderSelect.innerHTML = '<option>-- Select --</option>';
        fetch(riderAddOnsRoute.replace('PRODUCT_ID', id))
            .then(r => r.json())
            .then(data => {
                data.sort((a, b) => a.RiderName.localeCompare(b.RiderName));
                let opts = '<option value="">-- Select --</option>';
                data.forEach(r => opts += `<option value="${r.Id}">${r.RiderName}</option>`);
                riderSelect.innerHTML = opts;
            });
    });

    const form = document.querySelector('form');
    form.addEventListener('submit', e => {
        let hasErr = false;
        const start = form.PolicyStartDate, end = form.PolicyEndDate, issued = form.IssuedDate, expiry = form.ExpiryDate;

        if (end.value && start.value && end.value <= start.value) {
            end.classList.add('is-invalid');
            document.getElementById('error-PolicyEndDate').textContent = 'End must be after Start.';
            hasErr = true;
        }
        if (issued.value && start.value && issued.value > start.value) {
            issued.classList.add('is-invalid');
            document.getElementById('error-IssuedDate').textContent = 'Issued must be before Start.';
            hasErr = true;
        }
        if (expiry.value && end.value && expiry.value < end.value) {
            expiry.classList.add('is-invalid');
            document.getElementById('error-ExpiryDate').textContent = 'Expiry must be after End.';
            hasErr = true;
        }
        if (hasErr) e.preventDefault();
    });

    @if(old('InsurerID'))
        insurerSelect.value = "{{ old('InsurerID') }}";
        insurerSelect.dispatchEvent(new Event('change'));
    @endif
});
</script>
@endpush
@endsection
