@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('title', 'Request Details')

@section('content')
<div class="container mt-5" style="max-width: 700px;">
    <h3 class="mb-4">Request Assignment Details</h3>

    <div class="card">
        <div class="card-body">

            <div class="mb-3">
                <label class="form-label">Property</label>
                <input type="text" class="form-control" value="{{ $assignment->request->property->PropertyName ?? '-' }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Request Number</label>
                <input type="text" class="form-control" value="{{ $assignment->request->RequestNumber ?? '-' }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Block</label>
                <input type="text" class="form-control" value="{{ $assignment->request->block->BlockName ?? '-' }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Floor</label>
                <input type="text" class="form-control" value="{{ $assignment->request->floor->FloorLabel ?? '-' }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Unit</label>
                <input type="text" class="form-control" value="{{ $assignment->request->unit->UnitCode ?? '-' }}" disabled>
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label">Assignment Date</label>
                <input type="text" class="form-control"
                    value="{{ $assignment->AssignmentDate ? Carbon::parse($assignment->AssignmentDate)->format('d/m/Y') : '-' }}"
                    disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Assignment Type</label>
                <input type="text" class="form-control" value="{{ $assignment->assignmentType->Description ?? '-' }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Internal Technician</label>
                <input type="text" class="form-control" value="{{ $assignment->internalTechnician->JobTitle ?? '-' }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Prequalified Vendor</label>
                <input type="text" class="form-control" value="{{ $assignment->prequalifiedVendor->SupplierName ?? '-' }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Expected Start Date</label>
                <input type="text" class="form-control"
                    value="{{ $assignment->ExpectedStartDate ? Carbon::parse($assignment->ExpectedStartDate)->format('d/m/Y') : '-' }}"
                    disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Expected Completion</label>
                <input type="text" class="form-control"
                    value="{{ $assignment->ExpectedCompletion ? Carbon::parse($assignment->ExpectedCompletion)->format('d/m/Y') : '-' }}"
                    disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Priority Level</label>
                <input type="text" class="form-control" value="{{ $assignment->Status->label() ?? '-' }}" disabled>
            </div>

            <div class="mb-3">
                <label class="form-label">Instructions / Notes</label>
                <textarea class="form-control" rows="3" disabled>{{ $assignment->InstructionNotes ?? '-' }}</textarea>
            </div>

        </div>

        <div class="card-footer text-end">
            <a href="{{ route('assignrequest.edit', $assignment->Id) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('assignrequest.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
