@extends('layouts.app')
@section('title', 'Record Premium Payment')

@section('content')
<div class="container mt-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary rounded-top-4">
            <p class="mb-0 fw-semibold text-white">
                <i class="bi bi-cash-coin me-1"></i> Record Premium Payment
            </p>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.premiums.store') }}">
                @csrf

                {{-- Policy Selection --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        Select Policy <span class="text-danger">*</span>
                    </label>
                    <select id="request-select" name="PolicyID" class="form-select" required>
                        <option value="">-- Select Policy --</option>
                        @foreach ($policies as $policy)
                            <option
                                value="{{ $policy->Id }}"
                                data-paymentfrequency="{{ $policy->paymentfrequency->Description ?? '-' }}"
                                data-customerid="{{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}"
                                data-balance="{{ $balances[$policy->Id] ?? 0 }}">
                                {{ $policy->PolicyNumber }}
                                (Pending: {{ number_format($balances[$policy->Id] ?? 0, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Auto-filled Details --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Customer</label>
                        <input type="text" id="customer-id-display" class="form-control" readonly>
                        <input type="hidden" name="CustomerID" id="customer-id">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Frequency</label>
                        <input type="text" id="payment-frequency-display" class="form-control" readonly>
                        <input type="hidden" name="PaymentFrequency" id="payment-frequency-id">
                    </div>
                </div>

                {{-- Dates --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Payment Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="PaymentDate" id="payment-date" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Next Payment Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="NextPaymentDate" id="next-payment-date" class="form-control" required>
                    </div>
                </div>

                {{-- Amount --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        Amount Paid <span class="text-danger">*</span>
                        <small class="text-muted ms-2" id="pending-balance-label"></small>
                    </label>
                    <input type="number" step="0.01" name="Amount" class="form-control" placeholder="Enter amount paid" required>
                </div>

                {{-- Payment Mode --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Payment Mode</label>
                    <select name="PaymentMode" class="form-select">
                        <option value="">-- Select Payment Mode --</option>
                        @foreach ($paymentModes as $paymentMode)
                            <option value="{{ $paymentMode->ID }}">
                                {{ $paymentMode->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Reference --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Reference Number</label>
                    <input type="text" name="ReferenceNumber" class="form-control" placeholder="Transaction / Receipt number">
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Notes (optional)</label>
                    <textarea name="Notes" class="form-control" rows="3" placeholder="Additional remarks..."></textarea>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    const frequencyMap = {
        'Monthly': 1,
        'Quarterly': 3,
        'Semi-Annually': 6,
        'Annually': 12
    };

    document.getElementById('request-select').addEventListener('change', function () {
        const selected  = this.options[this.selectedIndex];
        const frequency = selected.dataset.paymentfrequency;
        const customer  = selected.dataset.customerid;
        const balance   = selected.dataset.balance;

        document.getElementById('customer-id-display').value = customer || '';
        document.getElementById('customer-id').value = customer || '';

        document.getElementById('payment-frequency-display').value = frequency || '';
        document.getElementById('payment-frequency-id').value = frequency || '';

        document.getElementById('pending-balance-label').innerText =
            balance ? `Pending: ${parseFloat(balance).toFixed(2)}` : '';

        autoCalculateNextPaymentDate();
    });

    document.getElementById('payment-date').addEventListener('change', autoCalculateNextPaymentDate);

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('request-select').dispatchEvent(new Event('change'));
    });

    function autoCalculateNextPaymentDate() {
        const paymentDate = document.getElementById('payment-date').value;
        const frequency   = document.getElementById('payment-frequency-display').value;
        const nextDate    = document.getElementById('next-payment-date');

        if (!paymentDate || !frequencyMap[frequency]) {
            nextDate.value = '';
            return;
        }

        const date = new Date(paymentDate);
        date.setMonth(date.getMonth() + frequencyMap[frequency]);
        nextDate.value = date.toISOString().split('T')[0];
    }
</script>
@endsection
