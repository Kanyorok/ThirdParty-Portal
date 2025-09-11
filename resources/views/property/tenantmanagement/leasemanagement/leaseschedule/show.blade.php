@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Schedule Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">📄 Lease Schedule Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Property Leased</strong>
                    <p class="mb-1">{{ $leaseschedule->lease->property->PropertyName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Lease Number</strong>
                    <p class="mb-1">{{ $leaseschedule->lease->LeaseNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Tenant Name</strong>
                    <p class="mb-1">{{ $leaseschedule->lease->tenant->TenantName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Payment Frequency</strong>
                    <p class="mb-1">{{ $leaseschedule->paymentFrequency->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Start Date</strong>
                    <p class="mb-1">{{ $leaseschedule->StartDate ? Carbon::parse($leaseschedule->StartDate)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>End Date</strong>
                    <p class="mb-1">{{ $leaseschedule->EndDate ? Carbon::parse($leaseschedule->EndDate)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Base Rent</strong>
                    <p class="mb-1">{{ number_format($leaseschedule->BaseRent, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Service Charge</strong>
                    <p class="mb-1">{{ $leaseschedule->ServiceCharge ? number_format($leaseschedule->ServiceCharge, 2) : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Parking Fee</strong>
                    <p class="mb-1">{{ $leaseschedule->ParkingFee ? number_format($leaseschedule->ParkingFee, 2) : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Other Charges</strong>
                    <p class="mb-1">{{ $leaseschedule->OtherCharges ? number_format($leaseschedule->OtherCharges, 2) : '-' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $leaseschedule->createdByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $leaseschedule->CreatedOn ? Carbon::parse($leaseschedule->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $leaseschedule->modifiedByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $leaseschedule->ModifiedOn ? Carbon::parse($leaseschedule->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('schedulelease.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
            <a href="{{ route('schedulelease.edit', $leaseschedule->Id) }}" class="btn btn-sm btn-primary">✏ Edit</a>
        </div>
    </div>
</div>
@endsection

