@extends('layouts.app')
@section('title', 'Edit Premium Payment')

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
        <div class="card-header bg-light border-bottom rounded-top-4">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-pencil-square me-2"></i>
                Edit Premium Payment
            </h5>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.premiums.update', $payment->Id) }}">
                @csrf
                @method('PUT')

                {{-- ================= POLICY ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Policy Information</h6>

                    <label class="form-label small fw-semibold">Select Policy</label>
                    <select id="request-select"
                            name="PolicyID"
                            class="form-select form-select-sm"
                            required>
                        <option value="">-- Select Policy --</option>
                        @foreach ($policies as $policy)
                            <option
                                value="{{ $policy->Id }}"
                                data-paymentfrequency="{{ $policy->paymentfrequency->Description }}"
                                data-customerid="{{ $policy->CustomerID }}"
                                data-customername="{{ $policy->customer->thirdParty->ThirdPartyName }}"
                                {{ $payment->PolicyID == $policy->Id ? 'selected' : '' }}>
                                {{ $policy->PolicyNumber }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ================= AUTO DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Auto-filled Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Customer</label>
                            <input type="text"
                                   id="customer-id-display"
                                   class="form-control form-control-sm bg-light"
                                   readonly>
                            <input type="hidden"
                                   name="CustomerID"
                                   id="customer-id"
                                   value="{{ old('CustomerID', $payment->CustomerID) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Frequency</label>
                            <input type="text"
                                   id="payment-frequency-display"
                                   class="form-control form-control-sm bg-light"
                                   readonly>
                            <input type="hidden"
                                   name="PaymentFrequency"
                                   id="payment-frequency-id"
                                   value="{{ old('PaymentFrequency', $payment->PaymentFrequency) }}">
                        </div>
                    </div>
                </div>

                {{-- ================= DATES ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payment Dates</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Date</label>
                            <input type="date"
                                   name="PaymentDate"
                                   id="payment-date"
                                   class="form-control form-control-sm"
                                   value="{{ \Carbon\Carbon::parse($payment->PaymentDate)->format('Y-m-d') }}"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Next Payment Date</label>
                            <input type="date"
                                   name="NextPaymentDate"
                                   id="next-payment-date"
                                   class="form-control form-control-sm"
                                   value="{{ \Carbon\Carbon::parse($payment->NextPaymentDate)->format('Y-m-d') }}"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= PAYMENT ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Payment Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Currency</label>
                            <select name="CurrencyId" class="form-select form-select-sm">
                                <option value="">-- Select Currency --</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->Id }}"
                                        {{ $payment->CurrencyId == $currency->Id ? 'selected' : '' }}>
                                        {{ $currency->Code ?? '' }} - {{ $currency->SymbolNative }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Amount Paid</label>
                            <input type="number"
                                   name="Amount"
                                   step="0.01"
                                   class="form-control form-control-sm text-end"
                                   value="{{ $payment->Amount }}"
                                   required>
                        </div>
                    </div>
                </div>

                {{-- ================= MODE & REFERENCE ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Mode & Reference</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Payment Mode</label>
                            <select name="PaymentMode" class="form-select form-select-sm">
                                <option value="">-- Select Payment Mode --</option>
                                @foreach ($paymentModes as $paymentMode)
                                    <option value="{{ $paymentMode->ID }}"
                                        {{ $payment->PaymentMode == $paymentMode->ID ? 'selected' : '' }}>
                                        {{ $paymentMode->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Reference Number</label>
                            <input type="text"
                                   name="ReferenceNumber"
                                   class="form-control form-control-sm"
                                   value="{{ $payment->ReferenceNumber }}">
                        </div>
                    </div>
                </div>

                {{-- ================= NOTES ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Notes</h6>

                    <textarea name="Notes"
                              class="form-control form-control-sm"
                              rows="3">{{ $payment->Notes }}</textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="submit"
                            class="btn btn-sm btn-success px-4">
                        <i class="bi bi-save me-1"></i>
                        Update Payment
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

    document.addEventListener('DOMContentLoaded', function () {
        const selected = document.querySelector('#request-select option:checked');
        if (selected) {
            document.getElementById('customer-id-display').value =
                selected.getAttribute('data-customername') || '';
            document.getElementById('payment-frequency-display').value =
                selected.getAttribute('data-paymentfrequency') || '';
            document.getElementById('customer-id').value =
                selected.getAttribute('data-customerid') || '';
            document.getElementById('payment-frequency-id').value =
                selected.getAttribute('data-paymentfrequency') || '';
        }
    });

    document.getElementById('request-select').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];

        document.getElementById('customer-id-display').value =
            selected.getAttribute('data-customername') || '';
        document.getElementById('customer-id').value =
            selected.getAttribute('data-customerid') || '';
        document.getElementById('payment-frequency-display').value =
            selected.getAttribute('data-paymentfrequency') || '';
        document.getElementById('payment-frequency-id').value =
            selected.getAttribute('data-paymentfrequency') || '';

        autoCalculateNextPaymentDate();
    });

    document.getElementById('payment-date').addEventListener('change', autoCalculateNextPaymentDate);

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
