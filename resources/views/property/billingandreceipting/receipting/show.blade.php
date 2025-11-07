@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Rent Receipt Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            {{-- Receipt Information --}}
            <h6 class="mb-3 text-dark">Rent Receipt Information</h6>
            <hr>
            <div class="row g-3 text-dark">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tenant Name</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $receipt->invoice->lease->tenant->thirdParty->ThirdPartyName  ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Invoice Number</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ $receipt->invoice->InvoiceNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Billing Month</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ $receipt->BillingMonth ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Invoice Date</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ $receipt->InvoiceDate ? Carbon::parse($receipt->InvoiceDate)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Payment Date</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ $receipt->PaymentDate ? Carbon::parse($receipt->PaymentDate)->format('d/m/Y') : '-' }}" readonly>
                </div>

                {{-- Financials --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rent Amount</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ number_format($receipt->RentAmount ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Service Charge</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ number_format($receipt->ServicesCharge ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Parking Fee</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ number_format($receipt->ParkingFee ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Other Charges</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ number_format($receipt->OtherCharges ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Total Due</label>
                    <input type="text" class="form-control bg-light text-dark fw-bold"
                        value="{{ number_format(
                            ($receipt->RentAmount ?? 0) +
                            ($receipt->ServicesCharge ?? 0) +
                            ($receipt->ParkingFee ?? 0) +
                            ($receipt->OtherCharges ?? 0), 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Amount Paid</label>
                    <input type="text" class="form-control bg-light text-dark fw-bold"
                        value="{{ number_format($receipt->AmountPaidNow ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Balance</label>
                    <input type="text" class="form-control bg-light text-dark fw-bold"
                        value="{{ number_format(
                            (($receipt->RentAmount ?? 0) +
                            ($receipt->ServicesCharge ?? 0) +
                            ($receipt->ParkingFee ?? 0) +
                            ($receipt->OtherCharges ?? 0)) -
                            ($receipt->AmountPaidNow ?? 0), 2) }}" readonly>
                </div>

                {{-- Payment Info --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Payment Method</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ $receipt->code->Description ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Reference Number</label>
                    <input type="text" class="form-control bg-light text-dark"
                        value="{{ $receipt->ReferenceNo ?? '-' }}" readonly>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Remarks</label>
                    <textarea class="form-control bg-light text-dark" rows="2" readonly>{{ $receipt->Remarks ?? '—' }}</textarea>
                </div>
            </div>
        </div>

        {{-- Footer with Audit Info + Actions --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-dark">
            <div>
                Created by <strong>{{ $receipt->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $receipt->CreatedOn ? Carbon::parse($receipt->CreatedOn)->format('d/m/Y') : '-' }}</strong>
                | Modified by <strong>{{ $receipt->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $receipt->ModifiedOn ? Carbon::parse($receipt->ModifiedOn)->format('d/m/Y') : '-' }}</strong>
            </div>
            <div>
                <a href="{{ route('rentreceipt.index') }}" class="btn btn-sm btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
