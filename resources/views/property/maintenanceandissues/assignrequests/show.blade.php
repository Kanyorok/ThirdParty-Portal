@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('title', 'Request Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Request Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Property</dt>
                    <dd class="col-sm-8">{{ $assignment->request->property->PropertyName ?? '-' }}</dd>

                    <dt class="col-sm-4">Request Number</dt>
                    <dd class="col-sm-8">{{ $assignment->request->RequestNumber ?? '-' }}</dd>  

                    <dt class="col-sm-4">Block</dt>
                    <dd class="col-sm-8">{{ $assignment->request->block->BlockName ?? '-' }}</dd>

                    <dt class="col-sm-4">Floor</dt>
                    <dd class="col-sm-8">{{ $assignment->request->floor->FloorLabel ?? '-' }}</dd>

                    <dt class="col-sm-4">Unit</dt>
                    <dd class="col-sm-8">{{ $assignment->request->unit->UnitCode ?? '-' }}</dd>

                    <dt class="col-sm-4">Assignment Date</dt>
                    <dd class="col-sm-8">{{ Carbon::parse( $assignment->AssignmentDate)->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Assignment Type</dt>
                    <dd class="col-sm-8">{{ $assignment->assignmentType->Description ?? '-' }}</dd>

                    <dt class="col-sm-4">Internal Technician</dt>
                    <dd class="col-sm-8">{{ $assignment->internalTechnician->JobTitle ?? '-' }}</dd>

                    <dt class="col-sm-4">Prequalified Vendor</dt>
                    <dd class="col-sm-8">{{ $assignment->prequalifiedVendor->SupplierName ?? '-' }}</dd> 

                    <dt class="col-sm-4">Expected Start Date</dt>
                    <dd class="col-sm-8">{{ Carbon::parse($assignment->ExpectedStartDate)->format('d/m/Y') }}</dd>

                    <dt class="col-sm-4">Expected Completion</dt>
                    <dd class="col-sm-8">{{ Carbon::parse($assignment->ExpectedCompletion)->format('d/m/Y') }}</dd>

                    <dt class="col-sm-4">Priority Level</dt>
                    <dd class="col-sm-8">{{ $assignment->Status->label() }}</dd>

                    <dt class="col-sm-4">Instructions / Notes</dt>
                    <dd class="col-sm-8">{{ $assignment->InstructionNotes ?? '-' }}</dd>

                </dl>
            </div>
            <div class="card-footer">
                <a href="{{ route('assignrequest.edit', $assignment->Id) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('assignrequest.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
