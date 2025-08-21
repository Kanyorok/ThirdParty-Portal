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
                <select name="CustomerID" class="form-select" required>
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
                        <option value="{{ $freq->ID }}" {{ old('PaymentFrequency') == $freq->Id ? 'selected' : '' }}>
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
            </div>

            <div class="col-md-6">
                <label class="form-label">Policy End Date <span class="text-danger">*</span></label>
                <input type="date" name="PolicyEndDate" class="form-control" value="{{ old('PolicyEndDate') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">
                    Issued Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="IssuedDate" class="form-control" value="{{ old('IssuedDate') }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">
                    Expiry Date <span class="text-danger">*</span>
                </label>
                <input type="date" name="ExpiryDate" class="form-control" value="{{ old('ExpiryDate') }}" required>
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
    const productsRoute = @json(route('bancassurance.referrals.referrals.products', ['insurerId' => 'INSURER_ID']));

    insurerSelect.addEventListener('change', function () {
        const insurerId = this.value;
        productSelect.innerHTML = '<option value="">Loading...</option>';

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
                });
        } else {
            productSelect.innerHTML = '<option value="">-- Select Product --</option>';
        }
    });

    @if(old('InsurerID'))
        insurerSelect.value = "{{ old('InsurerID') }}";
        insurerSelect.dispatchEvent(new Event('change'));
    @endif
});
</script>
@endpush
@endsection
