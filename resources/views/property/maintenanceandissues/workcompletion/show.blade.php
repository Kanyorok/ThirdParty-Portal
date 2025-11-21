@extends('layouts.app')

@section('title', 'Work Completion Details')

@section('content')
<div class="container mt-4" style="max-width: 1000px;">

    <div class="card shadow-sm border-0 rounded-3">

        {{-- Body --}}
        <div class="card-body">
            <div class="row g-3 text-dark fs-6">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Request Number</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $workCompletion->request->request->RequestNumber ?? '—' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Assignment Type</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $workCompletion->request->assignmentType->Description ?? '—' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Completion Date</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $workCompletion->CompletionDate ? \Carbon\Carbon::parse($workCompletion->CompletionDate)->format('d M Y') : '—' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Final Status</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $workCompletion->finalstatus->Description ?? '—' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Work Done Summary</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $workCompletion->WorkDoneSummary ?? '—' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Parts Used</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $workCompletion->PartsUsed ?? '—' }}" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Cost</label>
                    <input type="text" class="form-control bg-light text-dark"
                           value="{{ $workCompletion->Cost ? 'KES ' . number_format($workCompletion->Cost, 2) : '—' }}" readonly>
                </div>

                <div class="col-12">
                    <label class="form-label">Attached Documents</label>
                    <div class="p-2 border rounded bg-light">
                        @forelse($workCompletion->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        @empty
                            <span class="text-muted">No documents attached.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer with audit meta (small) --}}
        <div class="card-footer bg-light py-1 px-3 small">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div class="text-muted">
                    Created by <strong>{{ $workCompletion->CreatedBy->Name ?? '—' }}</strong>
                    on <strong>{{ $workCompletion->CreatedOn ? \Carbon\Carbon::parse($workCompletion->CreatedOn)->format('d M Y H:i') : '—' }}</strong>
                    | Modified by <strong>{{ $workCompletion->modifiedByUser->Name ?? '—' }}</strong>
                    on <strong>{{ $workCompletion->ModifiedOn ? \Carbon\Carbon::parse($workCompletion->ModifiedOn)->format('d M Y H:i') : '—' }}</strong>
                </div>
                <div class="text-md-end">
                    <a href="{{ route('workcompletion.index') }}" class="btn btn-sm btn-outline-secondary">⬅ Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
