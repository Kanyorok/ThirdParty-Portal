@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Rent Invoice')
@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">Property Invoice Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Invoice Number</strong>
                    <p class="mb-1">{{ $invoice->InvoiceNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Tenant</strong>
                    <p class="mb-1">{{ $invoice->lease->tenant->TenantName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Lease</strong>
                    <p class="mb-1">{{ $invoice->lease->LeaseNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Billing Period</strong>
                    <p class="mb-1">{{ $invoice->BillingMonth ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Invoice Date</strong>
                    <p class="mb-1">{{ $invoice->InvoiceDate ? Carbon::parse($invoice->InvoiceDate)->format('d M Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Rent Amount</strong>
                    <p class="mb-1">{{ number_format($invoice->RentAmount ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Service Charge</strong>
                    <p class="mb-1">{{ number_format($invoice->ServicesCharge ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Parking Fee</strong>
                    <p class="mb-1">{{ number_format($invoice->ParkingFee ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Other Charges</strong>
                    <p class="mb-1">{{ number_format($invoice->OtherCharges ?? 0, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Invoice Notes</strong>
                    <p class="mb-1">{{ $invoice->InvoiceNotes ?? '-' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $invoice->createdByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $invoice->CreatedOn ? Carbon::parse($invoice->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $invoice->modifiedByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $invoice->ModifiedOn ? Carbon::parse($invoice->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('rentinvoice.edit', $invoice->Id) }}" class="btn btn-sm btn-primary">✏ Edit</a>
            <a href="{{ route('rentinvoice.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
        </div>
    </div>
</div>
@endsection
