@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Maintenance Request Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">Maintenance Request Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Request Number</strong>
                    <p class="mb-1">{{ $maintenancerequest->RequestNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Property</strong>
                    <p class="mb-1">{{ $maintenancerequest->property->PropertyName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Block</strong>
                    <p class="mb-1">{{ $maintenancerequest->block->BlockName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Floor</strong>
                    <p class="mb-1">{{ $maintenancerequest->floor->FloorLabel ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Unit</strong>
                    <p class="mb-1">{{ $maintenancerequest->unit->UnitCode ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Reported By</strong>
                    <p class="mb-1">{{ $maintenancerequest->ReportedBy ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Issue Type</strong>
                    <p class="mb-1">{{ $maintenancerequest->issueType->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Priority</strong>
                    <p class="mb-1">{{ $maintenancerequest->priority->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Issue Description</strong>
                    <p class="mb-1">{{ $maintenancerequest->IssueDescription ?? '-' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $maintenancerequest->createdByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $maintenancerequest->CreatedOn ? Carbon::parse($maintenancerequest->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $maintenancerequest->modifiedByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $maintenancerequest->ModifiedOn ? Carbon::parse($maintenancerequest->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('maintenancerequest.edit', $maintenancerequest->Id) }}" class="btn btn-sm btn-primary">✏ Edit</a>
            <a href="{{ route('maintenancerequest.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
        </div>
    </div>
</div>
@endsection
