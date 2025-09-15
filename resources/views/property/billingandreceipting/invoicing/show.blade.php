@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Rent Invoice')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            {{-- Invoice Information --}}
            <h6 class="mb-3 text-dark">Property Invoice Information</h6>
            <hr>
            <div class="row g-3 text-dark">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Invoice Number</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $invoice->InvoiceNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tenant</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $invoice->lease->tenant->TenantName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lease</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $invoice->lease->LeaseNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Billing Period</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $invoice->BillingMonth ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Invoice Date</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $invoice->InvoiceDate ? Carbon::parse($invoice->InvoiceDate)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rent Amount</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ number_format($invoice->RentAmount ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Service Charge</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ number_format($invoice->ServicesCharge ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Parking Fee</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ number_format($invoice->ParkingFee ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Other Charges</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ number_format($invoice->OtherCharges ?? 0, 2) }}" readonly>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Invoice Notes</label>
                    <textarea class="form-control bg-light text-dark" rows="3" readonly>{{ $invoice->InvoiceNotes ?? '—' }}</textarea>
                </div>
            </div>
        </div>

        {{-- Footer with Audit Info + Actions --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-dark">
            <div>
                Created by <strong>{{ $invoice->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $invoice->CreatedOn ? Carbon::parse($invoice->CreatedOn)->format('d/m/Y') : '-' }}</strong>
                | Modified by <strong>{{ $invoice->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $invoice->ModifiedOn ? Carbon::parse($invoice->ModifiedOn)->format('d/m/Y') : '-' }}</strong>
            </div>
            <div>
                <a href="{{ route('rentinvoice.edit', $invoice->Id) }}" class="btn btn-sm btn-dark">Edit</a>
                <a href="{{ route('rentinvoice.index') }}" class="btn btn-sm btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
