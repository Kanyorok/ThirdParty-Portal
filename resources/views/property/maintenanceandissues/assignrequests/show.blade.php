@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Request Assignment Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">
    <h4 class="mb-3">Request Assignment Details</h4>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">

                <div class="col">
                    <strong>Property</strong>
                    <p class="mb-1">{{ $assignment->request->property->PropertyName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Request Number</strong>
                    <p class="mb-1">{{ $assignment->request->RequestNumber ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Block</strong>
                    <p class="mb-1">{{ $assignment->request->block->BlockName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Floor</strong>
                    <p class="mb-1">{{ $assignment->request->floor->FloorLabel ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Unit</strong>
                    <p class="mb-1">{{ $assignment->request->unit->UnitCode ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Assignment Date</strong>
                    <p class="mb-1">{{ $assignment->AssignmentDate ? Carbon::parse($assignment->AssignmentDate)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Assignment Type</strong>
                    <p class="mb-1">{{ $assignment->assignmentType->Description ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Internal Technician</strong>
                    <p class="mb-1">{{ $assignment->internalTechnician->JobTitle ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Prequalified Vendor</strong>
                    <p class="mb-1">{{ $assignment->prequalifiedVendor->SupplierName ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Expected Start Date</strong>
                    <p class="mb-1">{{ $assignment->ExpectedStartDate ? Carbon::parse($assignment->ExpectedStartDate)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Expected Completion</strong>
                    <p class="mb-1">{{ $assignment->ExpectedCompletion ? Carbon::parse($assignment->ExpectedCompletion)->format('d/m/Y') : '-' }}</p>
                </div>

                <div class="col">
                    <strong>Status</strong>
                    <p class="mb-1">{{ $assignment->Status->label() ?? '-' }}</p>
                </div>

                <div class="col">
                    <strong>Instructions / Notes</strong>
                    <p class="mb-1">{{ $assignment->InstructionNotes ?? '-' }}</p>
                </div>
            </div>

            <hr class="my-3">

            <h6 class="mb-2">Audit Information</h6>
            <div class="row row-cols-1 row-cols-md-2 g-2 fs-6">
                <div class="col">
                    <strong>Created By</strong>
                    <p class="mb-1">{{ $assignment->createdByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Created On</strong>
                    <p class="mb-1">{{ $assignment->CreatedOn ? Carbon::parse($assignment->CreatedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified By</strong>
                    <p class="mb-1">{{ $assignment->modifiedByUser->Name ?? '-' }}</p>
                </div>
                <div class="col">
                    <strong>Modified On</strong>
                    <p class="mb-1">{{ $assignment->ModifiedOn ? Carbon::parse($assignment->ModifiedOn)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('assignrequest.edit', $assignment->Id) }}" class="btn btn-sm btn-primary">✏ Edit</a>
            <a href="{{ route('assignrequest.index') }}" class="btn btn-sm btn-secondary">⬅ Back</a>
        </div>
    </div>
</div>
@endsection
