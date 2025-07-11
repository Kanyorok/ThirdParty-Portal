@extends('layouts.app')

@section('title', 'Lease Schedule Details')

@section('content')
<div class="container mt-5" style="max-width: 700px;">
    <h3 class="mb-4">📄 Lease Schedule Details</h3>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            
            <div class="mb-3">
                <label class="form-label">Property Leased</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->lease->property->PropertyName ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Lease Number</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->lease->LeaseNumber ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Tenant Name</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->lease->tenant->TenantName ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Payment Frequency</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->paymentFrequency->Description ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Start Date</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->StartDate ? \Carbon\Carbon::parse($leaseschedule->StartDate)->format('d/m/Y') : '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">End Date</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->EndDate ? \Carbon\Carbon::parse($leaseschedule->EndDate)->format('d/m/Y') : '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Base Rent</label>
                <input type="text" class="form-control" value="{{ number_format($leaseschedule->BaseRent, 2) }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Service Charge</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->ServiceCharge ? number_format($leaseschedule->ServiceCharge, 2) : '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Parking Fee</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->ParkingFee ? number_format($leaseschedule->ParkingFee, 2) : '-' }}" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Other Charges</label>
                <input type="text" class="form-control" value="{{ $leaseschedule->OtherCharges ? number_format($leaseschedule->OtherCharges, 2) : '-' }}" readonly>
            </div>

        </div>

        <div class="card-footer d-flex justify-content-between bg-light">
            <a href="{{ route('schedulelease.edit', $leaseschedule->Id) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('schedulelease.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
