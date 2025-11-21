@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Maintenance Request Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            {{-- Maintenance Request Info --}}
            <h6 class="mb-3 text-dark">Maintenance Request Information</h6>
            <hr>
            <div class="row g-3 text-dark">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Request Number</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $maintenancerequest->RequestNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Property</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $maintenancerequest->property->PropertyName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Block</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $maintenancerequest->block->BlockName ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Floor</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $maintenancerequest->floor->FloorLabel ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Unit</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $maintenancerequest->unit->UnitCode ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Reported By</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $maintenancerequest->ReportedBy ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Issue Type</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $maintenancerequest->issueType->Description ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Priority</label>
                    <input type="text" class="form-control bg-light text-dark" 
                           value="{{ $maintenancerequest->priority->Description ?? '-' }}" readonly>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Issue Description</label>
                    <textarea class="form-control bg-light text-dark" rows="3" readonly>{{ $maintenancerequest->IssueDescription ?? '-' }}</textarea>
                </div>
                    <div class="col-12">
                        <label class="form-label">Attached Documents</label>
                        <div class="p-2 border rounded bg-light">
                            @forelse($maintenancerequest->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                                {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                            @empty
                                <span class="text-muted">No documents attached.</span>
                            @endforelse
                        </div>
                    </div>
            </div>
        </div>

        {{-- Footer with Audit Info + Actions --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-dark">
            <div>
                Created by <strong>{{ $maintenancerequest->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $maintenancerequest->CreatedOn ? Carbon::parse($maintenancerequest->CreatedOn)->format('d M Y') : '-' }}</strong>
                | Modified by <strong>{{ $maintenancerequest->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $maintenancerequest->ModifiedOn ? Carbon::parse($maintenancerequest->ModifiedOn)->format('d M Y') : '-' }}</strong>
            </div>
            <div>
                <a href="{{ route('maintenancerequest.edit', $maintenancerequest->Id) }}" class="btn btn-sm btn-dark">Edit</a>
                <a href="{{ route('maintenancerequest.index') }}" class="btn btn-sm btn-outline-dark">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
