@extends('layouts.app')
@section('title', 'Premium Payment Details')

@section('content')
<style>
    .section-title {
        font-size: .9rem;
        font-weight: 600;
        color: #000;
        padding-bottom: .35rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid #dee2e6;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .form-control[readonly],
    textarea[readonly] {
        background-color: #f8f9fa;
    }
</style>

<div class="container mt-4" style="max-width: 850px;">
    <div class="bg-white p-4 rounded-3 shadow-sm">

        {{-- POLICY & CUSTOMER --}}
        <h6 class="section-title">Policy & Customer</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Policy Number</label>
                <input type="text" class="form-control"
                       value="{{ $payment->policies->PolicyNumber ?? '-' }}" readonly>
            </div>

            <div class="col-md-8">
                <label class="form-label">Customer Name</label>
                <input type="text" class="form-control"
                       value="{{ $payment->CustomerID ?? '-' }}" readonly>
            </div>
        </div>

        {{-- PAYMENT SCHEDULE --}}
        <h6 class="section-title">Payment Schedule</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Payment Frequency</label>
                <input type="text" class="form-control"
                       value="{{ $payment->PaymentFrequency ?? '-' }}" readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Date</label>
                <input type="text" class="form-control"
                       value="{{ $payment->PaymentDate
                            ? \Carbon\Carbon::parse($payment->PaymentDate)->format('d M Y')
                            : '-' }}" readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label">Next Payment Date</label>
                <input type="text" class="form-control"
                       value="{{ $payment->NextPaymentDate
                            ? \Carbon\Carbon::parse($payment->NextPaymentDate)->format('d M Y')
                            : '-' }}" readonly>
            </div>
        </div>

        {{-- PAYMENT DETAILS --}}
        <h6 class="section-title">Payment Details</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Amount Paid</label>
                <input type="text" class="form-control"
                       value="{{ $payment->currency->SymbolNative ?? '' }} {{ number_format($payment->Amount, 2) }}"
                       readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label">Payment Mode</label>
                <input type="text" class="form-control"
                       value="{{ $payment->paymentModes->Description ?? '-' }}" readonly>
            </div>
        </div>

        {{-- REFERENCE --}}
        <h6 class="section-title">Reference</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Reference Number</label>
                <input type="text" class="form-control"
                       value="{{ $payment->ReferenceNumber ?? '-' }}" readonly>
            </div>
        </div>

        {{-- NOTES --}}
        <h6 class="section-title">Notes</h6>
        <div class="mb-4">
            <textarea class="form-control" rows="3" readonly>{{ $payment->Notes ?? '-' }}</textarea>
        </div>

        {{-- ACTIONS --}}
        <div class="text-end">
            <a href="{{ url()->previous() }}" class="btn btn-outline-dark px-4">
                ← Back
            </a>
        </div>

    </div>
</div>
@endsection
