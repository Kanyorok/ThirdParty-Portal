@extends('layouts.app')
@section('title', 'Rent Invoice')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Property Invoice Details</h3>
        <div class="card">
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control" value="{{ $invoice->InvoiceNumber ?? '-' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <input type="text" class="form-control" value="{{ $invoice->lease->tenant->TenantName ?? '-' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lease</label>
                        <input type="text" class="form-control" value="{{ $invoice->lease->LeaseNumber ?? '-' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Billing Period</label>
                        <input type="month" class="form-control" value="{{ $invoice->BillingMonth ?? '' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Invoice Date</label>
                        <input type="date" class="form-control" value="{{ $invoice->InvoiceDate ?? '' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rent Amount</label>
                        <input type="number" class="form-control" value="{{ $invoice->RentAmount ?? 0 }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service Charge</label>
                        <input type="number" class="form-control" value="{{ $invoice->ServicesCharge ?? 0 }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parking Fee</label>
                        <input type="number" class="form-control" value="{{ $invoice->ParkingFee ?? 0 }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Other Charges</label>
                        <input type="number" class="form-control" value="{{ $invoice->OtherCharges ?? 0 }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Invoice Notes</label>
                        <textarea class="form-control" rows="3" readonly>{{ $invoice->InvoiceNotes ?? '-' }}</textarea>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <a href="{{ route('rentinvoice.edit', $invoice->Id) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('rentinvoice.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
