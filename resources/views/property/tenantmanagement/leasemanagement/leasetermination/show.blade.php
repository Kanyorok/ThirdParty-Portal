@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Lease Termination Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">

            {{-- Termination Information --}}
            <h6 class="mb-3 text-dark">Lease Termination Information</h6>
            <hr>
            <div class="row g-3 text-dark">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Lease Number</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leasetermination->lease->LeaseNumber ?? '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Termination Date</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leasetermination->TerminationDate ? Carbon::parse($leasetermination->TerminationDate)->format('d/m/Y') : '-' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Termination Reason</label>
                    <input type="text" class="form-control bg-light text-dark" 
                        value="{{ $leasetermination->code->Description ?? '-' }}" readonly>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Remarks</label>
                    <textarea class="form-control bg-light text-dark" rows="3" readonly>{{ $leasetermination->Remarks ?? '—' }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Attached Documents</label>
                    <div class="p-3 border rounded bg-light text-dark">
                        @forelse($leasetermination->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        @empty
                            <span class="text-muted">No documents attached.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer with Audit Info + Back --}}
        <div class="card-footer d-flex justify-content-between align-items-center py-2 bg-light small text-dark">
            <div>
                Created by <strong>{{ $leasetermination->createdByUser->Name ?? '-' }}</strong>
                on <strong>{{ $leasetermination->CreatedOn ? Carbon::parse($leasetermination->CreatedOn)->format('d/m/Y') : '-' }}</strong>
                | Modified by <strong>{{ $leasetermination->modifiedByUser->Name ?? '-' }}</strong>
                on <strong>{{ $leasetermination->ModifiedOn ? Carbon::parse($leasetermination->ModifiedOn)->format('d/m/Y') : '-' }}</strong>
            </div>
            <div>
                <a href="{{ route('terminatelease.index') }}" class="btn btn-sm btn-dark">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
