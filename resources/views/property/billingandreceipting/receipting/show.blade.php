@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Rent Receipt Details')
@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">Rent Receipt Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Tenant Name</strong>
                    <p class="mb-1">{{ $receipt->invoice->lease->tenant->TenantName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Invoice Number</strong>
                    <p class="mb-1">{{ $receipt->invoice->InvoiceNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Billing Month</strong>
                    <p class="mb-1">{{ $receipt->BillingMonth ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Invoice Date</strong>
                    <p class="mb-1">{{ $receipt->InvoiceDate ? Carbon::parse($receipt->InvoiceDate)->format('d M Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Payment Date</strong>
                    <p class="mb-1">{{ $receipt->PaymentDate ? Carbon::parse($receipt->PaymentDate)->format('d M Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Rent Amount</strong>
                    <p class="mb-1">{{ number_format($receipt->RentAmount ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Service Charge</strong>
                    <p class="mb-1">{{ number_format($receipt->ServicesCharge ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Parking Fee</strong>
                    <p class="mb-1">{{ number_format($receipt->ParkingFee ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Other Charges</strong>
                    <p class="mb-1">{{ number_format($receipt->OtherCharges ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Total Due</strong>
                    <p class="mb-1">
                        {{ number_format(
                            ($receipt->RentAmount ?? 0) +
                            ($receipt->ServicesCharge ?? 0) +
                            ($receipt->ParkingFee ?? 0) +
                            ($receipt->OtherCharges ?? 0), 2) }}
                    </p>
                </div>

                <div class="col">
                    <strong>Amount Paid </strong>
                    <p class="mb-1">{{ number_format($receipt->AmountPaidNow ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Balance</strong>
                    <p class="mb-1">
                        {{ number_format(
                            (($receipt->RentAmount ?? 0) +
                            ($receipt->ServicesCharge ?? 0) +
                            ($receipt->ParkingFee ?? 0) +
                            ($receipt->OtherCharges ?? 0)) -
                            ($receipt->AmountPaidNow ?? 0), 2) }}
                    </p>
                </div>

                <div class="col">
                    <strong>Payment Method</strong>
                    <p class="mb-1">{{ $receipt->code->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Reference Number</strong>
                    <p class="mb-1">{{ $receipt->ReferenceNo ?? '-' }}</p>
                </div>

                <div class="col-12">
                    <strong>Remarks</strong>
                    <p class="mb-1">{{ $receipt->Remarks ?? '-' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $receipt->createdByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $receipt->CreatedOn ? Carbon::parse($receipt->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $receipt->modifiedByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $receipt->ModifiedOn ? Carbon::parse($receipt->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('rentreceipt.edit', $receipt->Id) }}" class="btn btn-sm btn-primary">✏ Edit</a>
            <a href="{{ route('rentreceipt.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
        </div>
    </div>
</div>
@endsection
