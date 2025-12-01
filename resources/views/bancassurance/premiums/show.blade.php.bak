@extends('layouts.app')
@section('title', 'Premium Payment Details')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <p class="mb-0">Payment Info</p>
        </div>

        <div class="card-body p-4">
            <form>
                <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Policy Number</label>
                    <input type="text" class="form-control" value="{{ $payment->policies->PolicyNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-semibold">Customer Name</label>
                    <input type="text" class="form-control" value="{{ $payment->CustomerID ?? '-' }}" readonly>
                </div>
                </div>

                {{-- Row: Payment Frequency, Payment Date, Next Payment Date --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Payment Frequency</label>
                        <input type="text" class="form-control" value="{{ $payment->PaymentFrequency ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Payment Date</label>
                        <input type="text" class="form-control" 
                            value="{{ $payment->PaymentDate ? \Carbon\Carbon::parse($payment->PaymentDate)->format('d/m/Y') : '-' }}" 
                            readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Next Payment Date</label>
                        <input type="text" class="form-control" 
                            value="{{ $payment->NextPaymentDate ? \Carbon\Carbon::parse($payment->NextPaymentDate)->format('d/m/Y') : '-' }}" 
                            readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Amount</label>
                    <input type="text" class="form-control" value="{{ number_format($payment->Amount, 2) }}" readonly>
                </div>

                {{-- Row: Payment Mode and Reference Number --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Mode</label>
                        <input type="text" class="form-control" value="{{ $payment->paymentModes->Description ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reference Number</label>
                        <input type="text" class="form-control" value="{{ $payment->ReferenceNumber ?? '-' }}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea class="form-control" rows="3" readonly>{{ $payment->Notes ?? '-' }}</textarea>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
