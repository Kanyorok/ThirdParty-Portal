@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Edit Rent Invoice')

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

<form action="{{ route('rentinvoice.update', $invoice->Id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card shadow">
        <div class="card-header bg-light fw-bold">Lease Billing Details</div>
        <div class="card-body">

            <!-- Lease + Tenant -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Lease</label>
                    <select name="Lease" class="form-select" required>
                        @foreach($leases as $lease)
                            <option value="{{ $lease->Id }}" {{ $lease->Id == $invoice->Lease ? 'selected' : '' }}>
                                LSno: {{ $lease->LeaseNumber }} — {{ $lease->tenant->thirdParty->ThirdPartyName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tenant</label>
                    <input type="text" class="form-control" 
                           value="{{ $invoice->lease->tenant->thirdParty->ThirdPartyName }}" readonly>
                </div>
            </div>

            <!-- Billing Month + Invoice Date -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Billing Month</label>
                    <input type="month" class="form-control" name="BillingMonth"
                           value="{{ old('BillingMonth', Carbon::parse($invoice->BillingMonth)->format('Y-m')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" class="form-control" name="InvoiceDate"
                           value="{{ old('InvoiceDate', Carbon::parse($invoice->InvoiceDate)->format('Y-m-d')) }}">
                </div>
            </div>

            <!-- Charges -->
            <div class="row g-3 mb-3">
                @php
                    $charges = ['RentAmount' => 'Rent', 'ServicesCharge' => 'Service Charge', 'ParkingFee' => 'Parking Fee', 'OtherCharges' => 'Other Charges'];
                @endphp
                @foreach($charges as $field => $label)
                <div class="col-md-3">
                    <label class="form-label">{{ $label }}</label>
                    <input type="number" class="form-control charge-field" name="{{ $field }}"
                           value="{{ old($field, $invoice->$field) }}" step="0.01" min="0">
                </div>
                @endforeach
            </div>

            <!-- Total Amount -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Total Amount</label>
                    <input type="text" id="TotalAmount" class="form-control fw-bold bg-light" readonly
                           value="{{ number_format($invoice->RentAmount + $invoice->ServicesCharge + $invoice->ParkingFee + $invoice->OtherCharges, 2) }}">
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-3">
                <label class="form-label">Invoice Notes</label>
                <textarea class="form-control" rows="2" name="InvoiceNotes">{{ old('InvoiceNotes', $invoice->InvoiceNotes) }}</textarea>
            </div>

            <!-- Buttons -->
            <button type="submit" class="btn btn-success">Update Invoice</button>
            <a href="{{ route('rentinvoice.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.charge-field').forEach(input => {
        input.addEventListener('input', updateTotal);
    });

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.charge-field').forEach(el => {
            total += parseFloat(el.value || 0);
        });
        document.getElementById('TotalAmount').value = total.toFixed(2);
    }
</script>

@endsection
