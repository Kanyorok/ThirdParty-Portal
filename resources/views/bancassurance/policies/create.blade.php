@extends('layouts.app')
@section('title', 'Create Policy Proposal')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">📝 New Policy Proposal</h4>
    
<div class="mb-3">
    <label class="form-label">Referral (optional)</label>
    <select name="ReferralID" class="form-select">
        <option value="">-- None --</option>
        @foreach($referrals as $ref)
            <option value="{{ $ref->Id }}" {{ old('ReferralID') == $ref->Id ? 'selected' : '' }}>
                Referral #{{ $ref->Id }} – {{ $ref->CustomerName ?? 'Customer' }}
            </option>
        @endforeach
    </select>
</div>

    @if(isset($referral))
        <div class="alert alert-info">
            <strong>Referral Linked:</strong> Based on Referral #{{ $referral->Id }} by Staff ID: {{ $referral->ReferredBy }}
        </div>
        <input type="hidden" name="ReferralID" value="{{ $referral->Id }}">
    @endif

    <form method="POST" action="{{ route('bancassurance.policies.store') }}">
        @csrf

        <input type="hidden" name="ReferralID" value="{{ $referral->Id ?? '' }}">

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Customer</label>
                <select name="CustomerID" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $cust)
                        <option value="{{ $cust->Id }}" {{ old('CustomerID', $prefilled['CustomerID'] ?? '') == $cust->Id ? 'selected' : '' }}>
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
                        <option value="{{ $prod->Id }}" {{ old('ProductID', $prefilled['ProductID'] ?? '') == $prod->Id ? 'selected' : '' }}>
                            {{ $prod->Name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Preferred Insurer</label>
            <select name="InsurerID" class="form-select">
                <option value="">-- Optional --</option>
                @foreach($insurers as $ins)
                    <option value="{{ $ins->Id }}" {{ old('InsurerID', $prefilled['InsurerID'] ?? '') == $ins->Id ? 'selected' : '' }}>
                        {{ $ins->Name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Sum Assured</label>
                <input type="number" name="SumAssured" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Premium Amount</label>
                <input type="number" name="PremiumAmount" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Frequency</label>
                <select name="PaymentFrequency" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Quarterly">Quarterly</option>
                    <option value="Annually">Annually</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Start Date</label>
                <input type="date" name="PolicyStartDate" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">End Date</label>
                <input type="date" name="PolicyEndDate" class="form-control" required>
            </div>
        </div>

        <div class="text-end">
            <button class="btn btn-success">📩 Submit Proposal</button>
        </div>
    </form>
</div>
@endsection
