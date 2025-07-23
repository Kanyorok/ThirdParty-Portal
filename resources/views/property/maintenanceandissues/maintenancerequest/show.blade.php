@extends('layouts.app')
@section('title', 'Maintenance request details')

@section('content')
<div class="container mt-5" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Lease Agreement Details</h3>
        <a href="{{ route('maintenancerequest.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    <form>
        <div class="card shadow-sm border-0">

            <div class="card-body">
                <h5 class="card-title">Maintenance Request Details</h5>
                <div class="mb-3">
                    <label class="form-label">Request Number</label>
                    <input type="text" class="form-control" value="{{ $maintenancerequest->RequestNumber ?? '-' }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Property</label>
                    <input type="text" class="form-control" value="{{ $maintenancerequest->property->PropertyName ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Block</label>
                    <input type="text" class="form-control" value="{{ $maintenancerequest->block->BlockName ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Floor</label>
                    <input type="text" class="form-control" value="{{ $maintenancerequest->floor->FloorLabel ?? '-' }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Unit</label>
                    <input type="text" class="form-control" value="{{ $maintenancerequest->unit->UnitCode ?? '-' }}" readonly>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ReportedBy</label>
                        <input type="text" class="form-control" value="{{ $maintenancerequest->ReportedBy ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"> IssueType</label>
                        <input type="text" class="form-control" value="{{ $maintenancerequest->issueType->Description ?? '-' }}" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label"> Priority</label>
                    <input type="text" class="form-control" value="{{ $maintenancerequest->priority->Description ?? '-' }}" readonly>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"> Issue Description</label>
                        <input type="text" class="form-control" value="{{ $maintenancerequest->IssueDescription ?? '-' }}" readonly>
                    </div>
                    
            <div class="card-footer bg-light d-flex justify-content-between">
                <a href="{{ route('maintenancerequest.edit', $maintenancerequest->Id) }}" class="btn btn-outline-primary"><i class="bi bi-pencil-square"></i> Edit</a>
                <a href="{{ route('maintenancerequest.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>
    </form>
</div>
@endsection
