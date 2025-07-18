@extends('layouts.app')
@section('title', 'Patent Details')
@section('content')
    <div class="container mt-5" style="max-width: 700px;">
        <h3 class="mb-4">Patent Details</h3>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Property</dt>
                    <dd class="col-sm-8">{{ $assignment->Property ?? '-' }}</dd>

                    <dt class="col-sm-4">Request Number</dt>
                    <dd class="col-sm-8">{{ $assignment->request->RequestNumber ?? '-' }}</dd>  

                    <dt class="col-sm-4">Block</dt>
                    <dd class="col-sm-8">{{ $assignment->Block ?? '-' }}</dd>

                    <dt class="col-sm-4">Floor</dt>
                    <dd class="col-sm-8">{{ $assignment->Floor ?? '-' }}</dd>

                    <dt class="col-sm-4">Unit</dt>
                    <dd class="col-sm-8">{{ $assignment->Unit ?? '-' }}</dd>

                    <dt class="col-sm-4">Assignment Date</dt>
                    <dd class="col-sm-8">{{ $assignment->AssignmentDate ?? '-' }}</dd>

                    <dt class="col-sm-4">Assignment Type</dt>
                    <dd class="col-sm-8">{{ $assignment->assignmentType->Description ?? '-' }}</dd>

                    <dt class="col-sm-4">Internal Technician</dt>
                    <dd class="col-sm-8">{{ $assignment->internalTechnician->JobTitle ?? '-' }}</dd>

                    <dt class="col-sm-4">Prequalified Vendor</dt>
                    <dd class="col-sm-8">{{ $assignment->prequalifiedVendor->SupplierName ?? '-' }}</dd> 

                    <dt class="col-sm-4">Expected Start Date</dt>
                    <dd class="col-sm-8">{{ $assignment->ExpectedStartDate ?? '-' }}</dd>

                    <dt class="col-sm-4">Expected Completion</dt>
                    <dd class="col-sm-8">{{ $assignment->ExpectedCompletion ?? '-' }}</dd>

                    <dt class="col-sm-4">Priority Level</dt>
                    <dd class="col-sm-8">{{ $assignment->priorityLevel->Description ?? '-' }}</dd>

                    <dt class="col-sm-4">Instructions / Notes</dt>
                    <dd class="col-sm-8">{{ $assignment->InstructionNotes ?? '-' }}</dd>

                </dl>
            </div>
            <div class="card-footer">
                <a href="#" class="btn btn-primary">Edit</a>
                <a href="#" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
