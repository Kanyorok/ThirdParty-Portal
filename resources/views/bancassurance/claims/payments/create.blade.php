@extends('layouts.app')
@section('title', 'Initiate Claim Payment')

@section('content')
<div class="container mt-4">
    <h4>💳 Initiate Claim Payment</h4>

    <form action="{{ route('bancassurance.claims.payments.store') }}" method="POST">
        @csrf

        
        <div class="mb-3">
            <label class="form-label">Select Claim</label>
            <select name="ClaimId" id="ClaimId" class="form-select" required onchange="populateClaimDetails(this)">
                <option value="">-- Choose Unpaid Claim --</option>
                @foreach($unpaidClaims as $claim)
                    <option 
                        value="{{ $claim->Id }}"
                        data-amount="{{ $claim->ClaimAmount }}"
                        data-customer="{{ $claim->policy->customer->FullName }}"
                        data-policy="{{ $claim->policy->PolicyNumber }}"
                    >
                        {{ $claim->policy->PolicyNumber }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Customer Name</label>
            <input type="text" id="CustomerName" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Policy Number</label>
            <input type="text" id="PolicyNumber" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Amount to Pay</label>
            <input type="number" name="PaymentAmount" id="PaymentAmount" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Date</label>
            <input type="date" name="PaymentDate" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select name="PaymentMethod" class="form-select" required>
                <option value="#">-- Select Payment --</option>
                @foreach ($payments as $payment)
                    <option value="{{ $payment->ID }}">{{ $payment->Description }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Reference</label>
            <input type="text" name="PaymentReference" id="PaymentReference" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Paid by</label>
            <input type="text" name="PaidBy" id="PaidBy" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Note</label>
            <input type="text" name="Note" id="Note" class="form-control" step="0.01" required>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">Process Payment</button>
        </div>
    </form>
</div>

<script>
    function populateClaimDetails(select) {
        const selected = select.options[select.selectedIndex];
        document.getElementById('CustomerName').value = selected.getAttribute('data-customer') || '';
        document.getElementById('PolicyNumber').value = selected.getAttribute('data-policy') || '';
        document.getElementById('PaymentAmount').value = selected.getAttribute('data-amount') || '';
    }
</script>
@endsection
