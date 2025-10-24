@extends('layouts.app')
@section('title', 'Initiate Claim Payment')

@section('content')
<div class="container mt-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between">
            <p class="mb-0"><b>Payment Information</b></p>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form action="{{ route('bancassurance.claims.payments.store') }}" method="POST">
                @csrf

                {{-- Claim Selection --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Select Claim <span class="text-danger">*</span></label>
                    <select name="ClaimId" id="ClaimId" class="form-select" required onchange="populateClaimDetails(this)">
                        <option value="">-- Choose Unpaid Claim --</option>
                        @foreach($unpaidClaims as $claim)
                            <option 
                                value="{{ $claim->ClaimId }}"
                                data-customer="{{ $claim->claim->policy->customer->ThirdParty->ThirdPartyName ?? '-' }}"
                                data-policy="{{ $claim->claim->policy->PolicyNumber ?? '-' }}"
                                data-amount="{{ number_format($claim->AssessmentAmount ?? 0, 2, '.', ',') }}"
                            >
                                {{ $claim->claim->policy->PolicyNumber ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Claim Details --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Policy Number</label>
                        <input type="text" id="PolicyNumber" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Customer Name</label>
                        <input type="text" id="CustomerName" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Claim Amount</label>
                        <input type="text" id="ClaimAmount" class="form-control bg-light text-end" readonly>
                    </div>
                </div>

                {{-- Payment Info --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Amount to Pay <span class="text-danger">*</span></label>
                        <input type="text" id="PaymentAmountDisplay" class="form-control text-end" required>
                        <input type="hidden" name="PaymentAmount" id="PaymentAmount" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="PaymentDate" class="form-control" required>
                    </div>
                </div>

                {{-- Method & Reference --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="PaymentMethod" class="form-select" required>
                            <option value="">-- Select Payment Method --</option>
                            @foreach ($payments as $payment)
                                <option value="{{ $payment->ID }}">{{ $payment->Description ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Reference <span class="text-danger">*</span></label>
                        <input type="text" name="PaymentReference" id="PaymentReference" class="form-control" required>
                    </div>
                </div>

                {{-- Paid By & Note --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Paid By <span class="text-danger">*</span></label>
                        <input type="text" name="PaidBy" id="PaidBy" class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Note</label>
                    <textarea class="form-control" name="Note" id="Note" rows="2" placeholder="Optional additional details..."></textarea>
                </div>

                {{-- Submit --}}
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('bancassurance.claims.index') }}" class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i> Process Payment
                    </button>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="card-footer bg-light text-muted text-center rounded-bottom-4 py-2 small">
            <i class="bi bi-person-circle me-1"></i>
            Created by: <strong>{{ auth()->user()->name ?? 'System' }}</strong> |
            Modified by: <strong>{{ auth()->user()->name ?? 'System' }}</strong>
        </div>
    </div>
</div>

{{-- JS --}}
<script>
    // Format numbers with commas
    function formatWithCommas(num) {
        if (!num) return '';
        num = num.toString().replace(/,/g, '');
        const parts = num.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    // Populate Claim Details
    function populateClaimDetails(select) {
        const selected = select.options[select.selectedIndex];
        const amount = selected.getAttribute('data-amount')?.replace(/,/g, '') || 0;

        document.getElementById('CustomerName').value = selected.getAttribute('data-customer') || '';
        document.getElementById('PolicyNumber').value = selected.getAttribute('data-policy') || '';
        document.getElementById('ClaimAmount').value = formatWithCommas(parseFloat(amount).toFixed(2));
        document.getElementById('PaymentAmountDisplay').value = formatWithCommas(parseFloat(amount).toFixed(2));
        document.getElementById('PaymentAmount').value = parseFloat(amount).toFixed(2);
    }

    // Keep PaymentAmount numeric while displaying commas
    document.getElementById('PaymentAmountDisplay').addEventListener('input', function(e) {
        const raw = e.target.value.replace(/,/g, '');
        const num = parseFloat(raw);
        document.getElementById('PaymentAmount').value = isNaN(num) ? '' : num.toFixed(2);
        e.target.value = formatWithCommas(raw);
    });
</script>
@endsection
