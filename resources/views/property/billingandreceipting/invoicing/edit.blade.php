@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Rent Invoice')
@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('rentinvoice.update', $invoices->Id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card shadow">
        <div class="card-header bg-light fw-bold">Lease Billing Details</div>
        <div class="card-body">

            <!-- Lease + Tenant -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Lease</label>
                    <input type="text" class="form-control"
                           value="{{ $invoices->lease->LeaseNumber }}" readonly>
                    <input type="hidden" name="Lease" value="{{ $invoices->Lease }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tenant</label>
                    <input type="text" class="form-control"
                           value="{{ $invoices->lease->tenant->thirdParty->ThirdPartyName }}" readonly>
                    <input type="hidden" name="TenantId" value="{{ $invoices->TenantId }}">
                </div>
            </div>

            <!-- Billing Month + Invoice Date -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Billing Month</label>
                    <input type="month" class="form-control" name="BillingMonth"
                           value="{{ old('BillingMonth', $invoices->BillingMonth ? Carbon::parse($invoices->BillingMonth)->format('Y-m') : '') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" class="form-control" name="InvoiceDate"
                           value="{{ old('InvoiceDate', $invoices->InvoiceDate ? Carbon::parse($invoices->InvoiceDate)->format('Y-m-d') : '') }}">
                </div>
            </div>

            <!-- Charges -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">Rent Amount</label>
                    <input type="number" class="form-control charge-field" name="RentAmount"
                           value="{{ old('RentAmount', $invoices->RentAmount) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Service Charge</label>
                    <input type="number" class="form-control charge-field" name="ServicesCharge"
                           value="{{ old('ServicesCharge', $invoices->ServicesCharge) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parking Fee</label>
                    <input type="number" class="form-control charge-field" name="ParkingFee"
                           value="{{ old('ParkingFee', $invoices->ParkingFee) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Other Charges</label>
                    <input type="number" class="form-control charge-field" name="OtherCharges"
                           value="{{ old('OtherCharges', $invoices->OtherCharges) }}">
                </div>
            </div>

            <!-- Total Amount -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Total Amount</label>
                    <input type="text" id="TotalAmount" class="form-control fw-bold bg-light" readonly
                           value="{{ number_format(
                                (old('RentAmount', $invoices->RentAmount) ?? 0) +
                                (old('ServicesCharge', $invoices->ServicesCharge) ?? 0) +
                                (old('ParkingFee', $invoices->ParkingFee) ?? 0) +
                                (old('OtherCharges', $invoices->OtherCharges) ?? 0), 2) }}">
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-3">
                <label class="form-label">Invoice Notes</label>
                <textarea class="form-control" rows="2" name="InvoiceNotes"
                          placeholder="Optional notes or remarks...">{{ old('InvoiceNotes', $invoices->InvoiceNotes) }}</textarea>
            </div>

            <!-- Buttons -->
            <button type="submit" class="btn btn-success">Update Invoice</button>
            <a href="{{ route('rentinvoice.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

<!-- Auto-update Total -->
<script>
    document.querySelectorAll('.charge-field').forEach(input => {
        input.addEventListener('input', updateTotal);
    });

    function updateTotal() {
        let rent = parseFloat(document.querySelector('[name="RentAmount"]').value) || 0;
        let service = parseFloat(document.querySelector('[name="ServicesCharge"]').value) || 0;
        let parking = parseFloat(document.querySelector('[name="ParkingFee"]').value) || 0;
        let other = parseFloat(document.querySelector('[name="OtherCharges"]').value) || 0;

        let total = rent + service + parking + other;
        document.getElementById('TotalAmount').value = total.toFixed(2);
    }
</script>
@endsection
