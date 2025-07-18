@extends('layouts.app')
@section('title', 'Rent Receipt Details')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <h3 class="mb-4">Rent Receipt Details</h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <form>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tenant Name</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $receipt->invoice->lease->tenant->TenantName ?? '-' }}" 
                            disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Invoice Number</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $receipt->invoice->InvoiceNumber ?? '-' }}" 
                            disabled>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Billing Month</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ $receipt->BillingMonth ?? '-' }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Invoice Date</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ \Carbon\Carbon::parse($receipt->InvoiceDate)->format('d/m/Y') ?? '-' }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payment Date</label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ \Carbon\Carbon::parse($receipt->PaymentDate)->format('d/m/Y') ?? '-' }}" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rent Amount (KES)</label>
                    <input type="text" class="form-control" value="{{ number_format($receipt->RentAmount, 2) }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Service Charge (KES)</label>
                    <input type="text" class="form-control" value="{{ number_format($receipt->ServicesCharge, 2) }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Parking Fee (KES)</label>
                    <input type="text" class="form-control" value="{{ number_format($receipt->ParkingFee, 2) }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Other Charges (KES)</label>
                    <input type="text" class="form-control" value="{{ number_format($receipt->OtherCharges, 2) }}" disabled>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label"><strong>Total Due (KES)</strong></label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ number_format(($receipt->RentAmount ?? 0) + ($receipt->ServicesCharge ?? 0) + ($receipt->ParkingFee ?? 0) + ($receipt->OtherCharges ?? 0), 2) }}" 
                            disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label"><strong>Amount Paid (KES)</strong></label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ number_format($receipt->AmountPaidNow ?? 0, 2) }}" 
                            disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label"><strong>Balance (KES)</strong></label>
                        <input type="text" class="form-control form-control-sm" 
                            value="{{ number_format((($receipt->RentAmount ?? 0) + ($receipt->ServicesCharge ?? 0) + ($receipt->ParkingFee ?? 0) + ($receipt->OtherCharges ?? 0)) - ($receipt->AmountPaidNow ?? 0), 2) }}" 
                            disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <input type="text" class="form-control" value="{{ $receipt->code->Description ?? '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reference Number</label>
                    <input type="text" class="form-control" value="{{ $receipt->ReferenceNo ?? '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" rows="3" disabled>{{ $receipt->Remarks ?? '-' }}</textarea>
                </div>
            </form>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('rentreceipt.edit', $receipt->Id) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('rentreceipt.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
