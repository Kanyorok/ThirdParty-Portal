@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Request Assignment Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="">Work completion status:</label>
                <span class="badge bg-dark">
                    {{ $assignment->Status->label() ?? '—' }}
                </span>
            </div>
            <hr>

            {{-- Assignment Info --}}
            <div class="row g-3 text-dark">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Property</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->request->property->PropertyName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Request Number</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->request->RequestNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Block</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->request->block->BlockName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Floor</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->request->floor->FloorLabel ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Unit</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->request->unit->UnitCode ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Assignment Date</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->AssignmentDate ? Carbon::parse($assignment->AssignmentDate)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Assignment Type</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->assignmentType->Description ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Internal Technician</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->internalTechnician->JobTitle ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prequalified Vendor</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->prequalifiedVendor->SupplierName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Expected Start Date</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->ExpectedStartDate ? Carbon::parse($assignment->ExpectedStartDate)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Expected Completion</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $assignment->ExpectedCompletion ? Carbon::parse($assignment->ExpectedCompletion)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Instructions / Notes</label>
                    <textarea class="form-control bg-light text-dark" rows="3" readonly>{{ $assignment->InstructionNotes ?? '-' }}</textarea>
                </div>
            </div>
        </div>

        {{-- Footer with Audit Info + Actions --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-dark">
            <div>
                Created by <strong>{{ $assignment->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $assignment->CreatedOn ? Carbon::parse($assignment->CreatedOn)->format('d/m/Y') : '-' }}</strong>
                | Modified by <strong>{{ $assignment->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $assignment->ModifiedOn ? Carbon::parse($assignment->ModifiedOn)->format('d/m/Y') : '-' }}</strong>
            </div>
            <div>
                <a href="{{ route('assignrequest.edit', $assignment->Id) }}" class="btn btn-sm btn-dark">Edit</a>
                <a href="{{ route('assignrequest.index') }}" class="btn btn-sm btn-outline-dark">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
