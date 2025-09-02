@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Termination Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">📄 Lease Termination Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Lease Number</strong>
                    <p class="mb-1">{{ $leasetermination->lease->LeaseNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Termination Date</strong>
                    <p class="mb-1">{{ $leasetermination->TerminationDate ? Carbon::parse($leasetermination->TerminationDate)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Termination Reason</strong>
                    <p class="mb-1">{{ $leasetermination->code->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Remarks</strong>
                    <p class="mb-1">{{ $leasetermination->Remarks ?? '—' }}</p>
                </div>

                @if($leasetermination->ClearanceDocument)
                <div class="col">
                    <strong>Clearance Document</strong>
                    <p class="mb-1">
                        <a href="{{ asset('storage/' . $leasetermination->ClearanceDocument) }}" target="_blank" class="btn btn-outline-info btn-sm">
                            View Uploaded Document
                        </a>
                    </p>
                </div>
                @endif
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $leasetermination->createdByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $leasetermination->CreatedOn ? Carbon::parse($leasetermination->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $leasetermination->modifiedByUser->Name ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $leasetermination->ModifiedOn ? Carbon::parse($leasetermination->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('terminatelease.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
        </div>
    </div>
</div>
@endsection
