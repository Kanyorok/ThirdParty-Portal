@extends('layouts.app')
@section('title', 'Initiate Claim Payment')

@section('content')

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="container mt-4" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary border-bottom rounded-top-4">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-cash-stack me-2"></i>
                Claim Payment
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form action="{{ route('bancassurance.claims.payments.store') }}" method="POST">
                @csrf

                {{-- ================= CLAIM SELECTION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Claim Selection</h6>

                    <label class="form-label small ">
                        Select Claim <span class="text-danger">*</span>
                    </label>
                    <select name="ClaimId"
                            id="ClaimId"
                            class="form-select form-select-sm"
                            required
                            onchange="populateClaimDetails(this)">
                        <option value="">-- Choose Unpaid Claim --</option>
                        @foreach($unpaidClaims as $claim)
                            <option
                                value="{{ $claim->ClaimId }}"
                                data-customer="{{ $claim->claim->policy->customer->ThirdParty->ThirdPartyName ?? '-' }}"
                                data-policy="{{ $claim->claim->policy->PolicyNumber ?? '-' }}"
                                data-amount="{{ number_format($claim->AssessmentAmount ?? 0, 2, '.', ',') }}">
                                {{ $claim->claim->policy->PolicyNumber ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ================= CLAIM DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Claim Details</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small ">Policy Number</label>
                            <input type="text"
                                   id="PolicyNumber"
                                   class="form-control form-control-sm bg-light"
                                   readonly>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small ">Customer Name</label>
                            <input type="text"
                                   id="CustomerName"
                                   class="form-control form-control-sm bg-light"
                                   readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small ">Claim Amount</label>
                            <input type="text"
                                   id="ClaimAmount"
                                   class="form-control form-control-sm bg-light text-end"
                                   readonly>
                        </div>
                    </div>
                </div>

                {{-- ================= PAYMENT DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payment Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Amount to Pay <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="PaymentAmountDisplay"
                                   class="form-control form-control-sm text-end"
                                   required>
                            <input type="hidden"
                                   name="PaymentAmount"
                                   id="PaymentAmount"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Payment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="PaymentDate"
                                   class="form-control form-control-sm"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= PAYMENT METHOD ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payment Method</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Payment Method <span class="text-danger">*</span>
                            </label>
                            <select name="PaymentMethod"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Payment Method --</option>
                                @foreach ($payments as $payment)
                                    <option value="{{ $payment->ID }}">
                                        {{ $payment->Description ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Payment Reference <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="PaymentReference"
                                   class="form-control form-control-sm"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= PAYEE & NOTES ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payee Information</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Paid To <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="PaidBy"
                                   class="form-control form-control-sm"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small ">Notes</label>
                    <textarea name="Note"
                              class="form-control form-control-sm"
                              rows="2"
                              placeholder="Optional additional details..."></textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('bancassurance.claims.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Back
                    </a>
                    <button type="submit"
                            class="btn btn-sm btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i> Process Payment
                    </button>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="card-footer bg-light text-muted small text-center rounded-bottom-4 py-2">
            <i class="bi bi-person-circle me-1"></i>
            Created by <strong>{{ auth()->user()->name ?? 'System' }}</strong> ·
            Modified by <strong>{{ auth()->user()->name ?? 'System' }}</strong>
        </div>

    </div>
</div>

{{-- ================= JS ================= --}}
<script>
function formatWithCommas(num) {
    if (!num) return '';
    num = num.toString().replace(/,/g, '');
    const parts = num.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return parts.join('.');
}

function populateClaimDetails(select) {
    const selected = select.options[select.selectedIndex];
    const amount = selected.getAttribute('data-amount')?.replace(/,/g, '') || 0;

    document.getElementById('CustomerName').value = selected.dataset.customer || '';
    document.getElementById('PolicyNumber').value = selected.dataset.policy || '';
    document.getElementById('ClaimAmount').value = formatWithCommas(parseFloat(amount).toFixed(2));
    document.getElementById('PaymentAmountDisplay').value = formatWithCommas(parseFloat(amount).toFixed(2));
    document.getElementById('PaymentAmount').value = parseFloat(amount).toFixed(2);
}

document.getElementById('PaymentAmountDisplay').addEventListener('input', function(e) {
    const raw = e.target.value.replace(/,/g, '');
    const num = parseFloat(raw);
    document.getElementById('PaymentAmount').value = isNaN(num) ? '' : num.toFixed(2);
    e.target.value = formatWithCommas(raw);
});
</script>
@endsection
