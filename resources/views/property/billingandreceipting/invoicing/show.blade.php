@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'View Rent Invoice')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

  <div class="card shadow">
    <div class="card-header bg-light fw-bold">Lease Billing Details</div>
    <div class="card-body">

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Invoice Number</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ $invoice->InvoiceNumber ?? '-' }}" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tenant</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ $invoice->lease->tenant->thirdParty->TradingName ?? '-' }}" readonly>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label">Lease</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ $invoice->lease->LeaseNumber ?? '-' }}" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Billing Month</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ $invoice->BillingMonth ?? '-' }}" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Invoice Date</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ $invoice->InvoiceDate ? Carbon::parse($invoice->InvoiceDate)->format('d/m/Y') : '-' }}" readonly>
        </div>
      </div>

      <!-- Charges Section -->
      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">Rent Amount</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ number_format($invoice->RentAmount ?? 0, 2) }}" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">Service Charge</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ number_format($invoice->ServicesCharge ?? 0, 2) }}" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">Parking Fee</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ number_format($invoice->ParkingFee ?? 0, 2) }}" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">Other Charges</label>
          <input type="text" class="form-control bg-light" 
                 value="{{ number_format($invoice->OtherCharges ?? 0, 2) }}" readonly>
        </div>
      </div>

      <!-- Total -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label fw-bold">Total Amount</label>
          <input type="text" class="form-control bg-light fw-bold" 
                 value="{{ number_format(
                    ($invoice->RentAmount ?? 0) +
                    ($invoice->ServicesCharge ?? 0) +
                    ($invoice->ParkingFee ?? 0) +
                    ($invoice->OtherCharges ?? 0), 2
                 ) }}" readonly>
        </div>
      </div>

      <!-- Notes -->
      <div class="mb-3">
        <label class="form-label">Invoice Notes</label>
        <textarea class="form-control bg-light" rows="3" readonly>{{ $invoice->InvoiceNotes ?? '—' }}</textarea>
      </div>

    </div>

    <!-- Footer -->
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
