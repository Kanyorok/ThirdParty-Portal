@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Details')

@section('content')
<div class="container mt-4" style="max-width:1100px;">

<div class="card shadow border-0 rounded-4 overflow-hidden">

{{-- ================= HEADER ================= --}}
<div class="card-header bg-white d-flex justify-content-between align-items-center fw-bold fs-5">
    Lease Details
    <a href="{{ route('addlease.offer', $newlease->Id) }}" class="btn btn-primary btn-sm">
        Generate Offer Letter
    </a>
</div>

<div class="card-body">

{{-- ================= TENANT & LEASE ================= --}}
<h6 class="fw-bold text-primary mb-3">Tenant & Lease</h6>

<div class="row g-3">
    <div class="col-md-6">
        <label class="small text-muted">Tenant</label>
        <div class="form-control bg-light">{{ $newlease->tenant->thirdParty->ThirdPartyName ?? '-' }}</div>
    </div>
    <div class="col-md-6">
        <label class="small text-muted">Lease Number</label>
        <div class="form-control bg-light">{{ $newlease->LeaseNumber ?? '-' }}</div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-md-3">
        <label class="small text-muted">Property</label>
        <div class="form-control bg-light">{{ $newlease->property->PropertyName ?? '-' }}</div>
    </div>
    <div class="col-md-3">
        <label class="small text-muted">Block</label>
        <div class="form-control bg-light">{{ $newlease->block->BlockName ?? '-' }}</div>
    </div>
    <div class="col-md-3">
        <label class="small text-muted">Floor</label>
        <div class="form-control bg-light">{{ $newlease->floor->FloorLabel ?? '-' }}</div>
    </div>
    <div class="col-md-3">
        <label class="small text-muted">Unit</label>
        <div class="form-control bg-light">{{ $newlease->unit->UnitCode ?? '-' }}</div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-md-3">
        <label class="small text-muted">Start Date</label>
        <div class="form-control bg-light">
            {{ $newlease->StartDate ? Carbon::parse($newlease->StartDate)->format('d M Y') : '-' }}
        </div>
    </div>
    <div class="col-md-3">
        <label class="small text-muted">End Date</label>
        <div class="form-control bg-light">
            {{ $newlease->EndDate ? Carbon::parse($newlease->EndDate)->format('d M Y') : '-' }}
        </div>
    </div>
    <div class="col-md-3">
        <label class="small text-muted">Payment Frequency</label>
        <div class="form-control bg-light">{{ $newlease->code->Description ?? '-' }}</div>
    </div>
    <div class="col-md-3">
        <label class="small text-muted">Payment Due Date</label>
        <div class="form-control bg-light">{{ $newlease->DueDay ?? '-' }}</div>
    </div>
</div>

<hr class="my-4">

{{-- ================= FINANCIAL ================= --}}
<h6 class="fw-bold text-primary mb-3">Financial Details</h6>

@php
    $rent = $newlease->MonthlyRent ?? 0;
    $service = $newlease->ServiceCharge ?? 0;
    $parking = $newlease->ParkingFee ?? 0;
    $other = $newlease->OtherCharges ?? 0;

    $subtotal = $rent + $service + $parking + $other;

    $taxRate = $newlease->taxRule->Rate ?? 0;
    $taxAmount = ($subtotal * ($taxRate) / 100);

    $totalInclusive = $subtotal + $taxAmount;
@endphp

<div class="row g-3">

    <div class="col-md-4">
        <label class="small text-muted">Rent</label>
        <div class="form-control bg-light fw-semibold">
            {{ number_format($rent,2) }} {{ $newlease->currency->Symbol ?? '' }}
        </div>
    </div>

    <div class="col-md-4">
        <label class="small text-muted">Deposit</label>
        <div class="form-control bg-light fw-semibold">
            {{ number_format($newlease->Deposit,2) }} {{ $newlease->currency->Symbol ?? '' }}
        </div>
    </div>

    <div class="col-md-4">
        <label class="small text-muted">Service Charge</label>
        <div class="form-control bg-light fw-semibold">
            {{ number_format($service,2) }} {{ $newlease->currency->Symbol ?? '' }}
        </div>
    </div>

    <div class="col-md-4">
        <label class="small text-muted">Parking Fee</label>
        <div class="form-control bg-light fw-semibold">
            {{ number_format($parking,2) }} {{ $newlease->currency->Symbol ?? '' }}
        </div>
    </div>

    <div class="col-md-4">
        <label class="small text-muted">Other Charges</label>
        <div class="form-control bg-light fw-semibold">
            {{ number_format($other,2) }} {{ $newlease->currency->Symbol ?? '' }}
        </div>
    </div>

    <div class="col-md-4">
        <label class="small text-muted">Tax ({{ $taxRate }}%)</label>
        <div class="form-control bg-light fw-semibold text-danger">
            {{ number_format($taxAmount,2) }} {{ $newlease->currency->Symbol ?? '' }}
        </div>
    </div>

</div>

<div class="mt-4 p-3 rounded-3 border bg-success bg-opacity-10 d-flex justify-content-between align-items-center">
    <span class="fw-bold text-success fs-6">Total Amount (Exclusive of Deposit)</span>
    <span class="fw-bold text-success fs-5">
        {{ number_format($totalInclusive,2) }} {{ $newlease->currency->Symbol ?? '' }}
    </span>
</div>

<hr class="my-4">

{{-- ================= SPECIAL TERMS ================= --}}
<h6 class="fw-bold text-primary mb-2">Special Terms</h6>
<div class="bg-light rounded-3 p-3 border">
    {{ $newlease->SpecialTerms ?: '— None provided —' }}
</div>

<hr class="my-4">

{{-- ================= DOCUMENTS ================= --}}
<h6 class="fw-bold text-primary mb-2">Attached Documents</h6>
<div class="bg-light rounded-3 p-3 border">
    @forelse($newlease->documents()->get(['t_Documents.Id','t_Documents.DocumentId','MimeType','Name']) as $document)
        {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
    @empty
        <span class="text-muted">No documents attached</span>
    @endforelse
</div>

</div>

{{-- ================= FOOTER ================= --}}
<div class="card-footer bg-white small d-flex justify-content-between align-items-center">

    <div class="text-muted">
        Created by <strong>{{ $newlease->createdByUser->Name ?? '-' }}</strong>
        • {{ $newlease->CreatedOn ? Carbon::parse($newlease->CreatedOn)->format('d M Y') : '-' }}
        &nbsp; | &nbsp;
        Modified by <strong>{{ $newlease->modifiedByUser->Name ?? '-' }}</strong>
        • {{ $newlease->ModifiedOn ? Carbon::parse($newlease->ModifiedOn)->format('d M Y') : '-' }}
    </div>

    <a href="{{ route('addlease.index') }}" class="btn btn-outline-dark btn-sm px-3">
        Back
    </a>

</div>

</div>
</div>
@endsection

@section('scripts')
@include('snippets.actions.preview-files')
@endsection
