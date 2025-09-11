@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">Lease Agreement Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Lease Number</strong>
                    <p class="mb-1">{{ $newlease->LeaseNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Tenant</strong>
                    <p class="mb-1">{{ $newlease->tenant->TenantName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Property</strong>
                    <p class="mb-1">{{ $newlease->property->PropertyName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Block</strong>
                    <p class="mb-1">{{ $newlease->block->BlockName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Floor</strong>
                    <p class="mb-1">{{ $newlease->floor->FloorLabel ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Unit</strong>
                    <p class="mb-1">{{ $newlease->unit->UnitCode ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Start Date</strong>
                    <p class="mb-1">{{ $newlease->StartDate ? Carbon::parse($newlease->StartDate)->format('d M Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>End Date</strong>
                    <p class="mb-1">{{ $newlease->EndDate ? Carbon::parse($newlease->EndDate)->format('d M Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Payment Frequency</strong>
                    <p class="mb-1">{{ $newlease->code->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Monthly Rent (KES)</strong>
                    <p class="mb-1">{{ number_format($newlease->MonthlyRent, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Deposit (KES)</strong>
                    <p class="mb-1">{{ number_format($newlease->Deposit, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Service Charge (KES)</strong>
                    <p class="mb-1">{{ number_format($newlease->ServiceCharge, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Parking Fee (KES)</strong>
                    <p class="mb-1">{{ number_format($newlease->ParkingFee, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Other Charges (KES)</strong>
                    <p class="mb-1">{{ number_format($newlease->OtherCharges, 2) }}</p>
                </div>

                <div class="col">
                    <strong>Due Day</strong>
                    <p class="mb-1">{{ $newlease->DueDay ?? '-' }}</p>
                </div>

                <div class="col-12">
                    <strong>Special Terms</strong>
                    <p class="mb-1">{{ $newlease->SpecialTerms ?? '—' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $newlease->createdByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $newlease->CreatedOn ? Carbon::parse($newlease->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $newlease->modifiedByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $newlease->ModifiedOn ? Carbon::parse($newlease->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('addlease.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
            <a href="{{ route('addlease.edit', $newlease->Id) }}" class="btn btn-sm btn-primary">✏ Edit</a>
        </div>
    </div>
</div>
@endsection
