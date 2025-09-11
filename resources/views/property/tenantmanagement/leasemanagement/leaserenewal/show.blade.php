@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Renewal Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">📄 Lease Renewal Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Lease Number</strong>
                    <p class="mb-1">{{ $leaserenewal->lease->LeaseNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Tenant Name</strong>
                    <p class="mb-1">{{ $leaserenewal->lease->tenant->TenantName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Property Leased</strong>
                    <p class="mb-1">{{ $leaserenewal->lease->property->PropertyName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Payment Frequency</strong>
                    <p class="mb-1">{{ $leaserenewal->paymentFrequency->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>End Date of Current Lease</strong>
                    <p class="mb-1">{{ $leaserenewal->EndDateCurrentLease ? Carbon::parse($leaserenewal->EndDateCurrentLease)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>New Start Date</strong>
                    <p class="mb-1">{{ $leaserenewal->NewStartDate ? Carbon::parse($leaserenewal->NewStartDate)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>New End Date</strong>
                    <p class="mb-1">{{ $leaserenewal->NewEndDate ? Carbon::parse($leaserenewal->NewEndDate)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>New Monthly Rent</strong>
                    <p class="mb-1">{{ $leaserenewal->NewMonthlyRent ? number_format($leaserenewal->NewMonthlyRent, 2) : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Service Charge</strong>
                    <p class="mb-1">{{ $leaserenewal->ServiceCharge ? number_format($leaserenewal->ServiceCharge, 2) : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Parking Fee</strong>
                    <p class="mb-1">{{ $leaserenewal->ParkingFee ? number_format($leaserenewal->ParkingFee, 2) : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Other Charges</strong>
                    <p class="mb-1">{{ $leaserenewal->OtherCharges ? number_format($leaserenewal->OtherCharges, 2) : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Remarks</strong>
                    <p class="mb-1">{{ $leaserenewal->Remarks ?? '-' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $leaserenewal->createdByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $leaserenewal->CreatedOn ? Carbon::parse($leaserenewal->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $leaserenewal->modifiedByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $leaserenewal->ModifiedOn ? Carbon::parse($leaserenewal->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('renewlease.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
            <a href="{{ route('renewlease.edit', $leaserenewal->Id) }}" class="btn btn-sm btn-primary">✏ Edit</a>
        </div>
    </div>
</div>
@endsection
