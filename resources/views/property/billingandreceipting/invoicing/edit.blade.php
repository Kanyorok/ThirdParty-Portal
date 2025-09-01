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

<h1>Edit Rent Invoice</h1>

<form action="{{ route('rentinvoice.update', $invoices->Id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card shadow">
        <div class="card-header bg-light fw-bold">Lease Billing Details</div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <!-- Lease Display -->
                <div class="col-md-6">
                    <label class="form-label">Lease</label>
                    <input type="text" class="form-control" 
                           value="LSno: {{ $invoices->lease->LeaseNumber }} — Name: {{ $invoices->lease->tenant->TenantName }}" 
                           readonly>
                    <input type="hidden" name="Lease" value="{{ $invoices->Lease }}">
                </div>

                <!-- Tenant Display -->
                <div class="col-md-6">
                    <label class="form-label">Tenant</label>
                    <input type="text" class="form-control" 
                           value="{{ $invoices->lease->tenant->TenantName }}" 
                           readonly>
                    <input type="hidden" name="TenantId" value="{{ $invoices->TenantId }}">
                </div>

                <!-- Billing Month -->
                <div class="col-md-4">
                    <label class="form-label">Billing Month</label>
                    <input type="month" class="form-control" name="BillingMonth"
                           value="{{ old('BillingMonth', $invoices->BillingMonth ? Carbon::parse($invoices->BillingMonth)->format('Y-m') : '') }}">
                </div>

                <!-- Invoice Date -->
                <div class="col-md-3">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" class="form-control" name="InvoiceDate"
                           value="{{ old('InvoiceDate', $invoices->InvoiceDate ? Carbon::parse($invoices->InvoiceDate)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rent Amount</label>
                    <input type="number" class="form-control" name="RentAmount"
                           value="{{ old('RentAmount', $invoices->RentAmount) }}">
                </div>
            </div>

            <!-- Charges Summary -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Service Charge</label>
                    <input type="number" class="form-control" name="ServicesCharge"
                           value="{{ old('ServicesCharge', $invoices->ServicesCharge) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parking Fee</label>
                    <input type="number" class="form-control" name="ParkingFee"
                           value="{{ old('ParkingFee', $invoices->ParkingFee) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Other Charges</label>
                    <input type="number" class="form-control" name="OtherCharges"
                           value="{{ old('OtherCharges', $invoices->OtherCharges) }}">
                </div>
            </div>

            <!-- Optional Notes -->
            <div class="mb-3">
                <label class="form-label">Invoice Notes</label>
                <textarea class="form-control" rows="2" name="InvoiceNotes"
                          placeholder="Optional notes or remarks...">{{ old('InvoiceNotes', $invoices->InvoiceNotes) }}</textarea>
            </div>

            <!-- Form Buttons -->
            <button type="submit" class="btn btn-success">Update Invoice</button>
            <a href="{{ route('rentinvoice.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

@endsection
