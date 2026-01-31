@extends('layouts.app')
@section('title', 'Record Premium Payment')

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
                <i class="bi bi-cash-coin me-2"></i>
                Record Premium Payment
            </h5>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.premiums.store') }}">
                @csrf

                {{-- ================= POLICY ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Policy Information</h6>

                    <label class="form-label small ">
                        Select Policy <span class="text-danger">*</span>
                    </label>
                    <select id="request-select"
                            name="PolicyID"
                            class="form-select form-select-sm"
                            required>
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

                {{-- ================= AUTO DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Auto-filled Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">Customer</label>
                            <input type="text"
                                   id="customer-id-display"
                                   class="form-control form-control-sm bg-light"
                                   readonly>
                            <input type="hidden" name="CustomerID" id="customer-id">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">Payment Frequency</label>
                            <input type="text"
                                   id="payment-frequency-display"
                                   class="form-control form-control-sm bg-light"
                                   readonly>
                            <input type="hidden" name="PaymentFrequency" id="payment-frequency-id">
                        </div>
                    </div>
                </div>

                {{-- ================= DATES ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payment Dates</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Payment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="PaymentDate"
                                   id="payment-date"
                                   class="form-control form-control-sm"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Next Payment Date <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="NextPaymentDate"
                                   id="next-payment-date"
                                   class="form-control form-control-sm"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= PAYMENT ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payment Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">Currency</label>
                            <select name="CurrencyId" class="form-select form-select-sm">
                                <option value="">-- Select Currency --</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->Id }}">
                                        {{ $currency->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Amount Paid <span class="text-danger">*</span>
                                <small class="text-muted ms-2" id="pending-balance-label"></small>
                            </label>
                            <input type="number"
                                   step="0.01"
                                   name="Amount"
                                   class="form-control form-control-sm text-end"
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= MODE & REFERENCE ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Mode & Reference</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">Payment Mode</label>
                            <select name="PaymentMode" class="form-select form-select-sm">
                                <option value="">-- Select Payment Mode --</option>
                                @foreach ($paymentModes as $paymentMode)
                                    <option value="{{ $paymentMode->ID }}">
                                        {{ $paymentMode->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">Reference Number</label>
                            <input type="text"
                                   name="ReferenceNumber"
                                   class="form-control form-control-sm"
                                   placeholder="Transaction / Receipt number">
                        </div>
                    </div>
                </div>

                {{-- ================= NOTES ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Notes</h6>

                    <textarea name="Notes"
                              class="form-control form-control-sm"
                              rows="3"
                              placeholder="Additional remarks..."></textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ url()->previous() }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Back
                    </a>

                    <button type="submit"
                            class="btn btn-sm btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i>
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= JS (unchanged) ================= --}}
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
