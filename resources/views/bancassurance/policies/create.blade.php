@extends('layouts.app')
@section('title', 'Create Policy Proposal')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📝 New Policy Proposal</h4>

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
                <strong>Referral Linked:</strong> Based on Referral #{{ $referral->Id }} by Staff ID: {{ $referral->ReferredBy }}
            </div>
            <input type="hidden" name="ReferralID" value="{{ $referral->Id }}">
        @endif

        {{-- Customer & Product --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Customer</label>
                <select name="CustomerID" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->Id }}" {{ old('CustomerID') == $cust->Id ? 'selected' : '' }}>
                            {{ $cust->FullName }} ({{ $cust->NationalID }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Product</label>
                <select name="ProductID" class="form-select" required>
                    <option value="">-- Select Product --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->ID }}">
                            {{ $prod->Description }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>


        {{-- Insurer --}}
        <div class="mb-3">
            <label class="form-label">Preferred Insurer</label>
            <select name="InsurerID" class="form-select">
                <option value="">-- Optional --</option>
                @foreach($insurers as $ins)
                    <option value="{{ $ins->ID }}">
                        {{ $ins->Description }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Policy Number
        <div class="mb-3">
            <label class="form-label">Policy Number</label>
            <input type="text" name="PolicyNumber" class="form-control" value="{{ old('PolicyNumber') }}" required>
        </div> --}}

        {{-- Financials & Frequency --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Sum Assured</label>
                <input type="number" name="SumAssured" class="form-control" value="{{ old('SumAssured') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Premium Amount</label>
                <input type="number" name="PremiumAmount" class="form-control" value="{{ old('PremiumAmount') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Frequency</label>
                <select name="PaymentFrequency" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($paymentfrequencys as $freq)
                        <option value="{{ $freq->ID }}" {{ old('PaymentFrequency') == $freq->Id ? 'selected' : '' }}>
                            {{ $freq->Description }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- <div class="col-md-4">
                <label class="form-label">Policy Status</label>
                <select name="Status" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach(App\Enums\Insurance\InsurancePolicyStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ old('Status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div> --}}
        </div>
        {{-- Dates --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Policy Start Date</label>
                <input type="date" name="PolicyStartDate" class="form-control" value="{{ old('PolicyStartDate') }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Policy End Date</label>
                <input type="date" name="PolicyEndDate" class="form-control" value="{{ old('PolicyEndDate') }}" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Issued Date <small class="text-muted">(optional)</small></label>
                <input type="date" name="IssuedDate" class="form-control" value="{{ old('IssuedDate') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Expiry Date <small class="text-muted">(optional)</small></label>
                <input type="date" name="ExpiryDate" class="form-control" value="{{ old('ExpiryDate') }}">
            </div>
        </div>

        {{-- Submit --}}
        <div class="text-end">
            <button class="btn btn-success">📩 Submit Proposal</button>
        </div>
    </form>
</div>
@endsection
