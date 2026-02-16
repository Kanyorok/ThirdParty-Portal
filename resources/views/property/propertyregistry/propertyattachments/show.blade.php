@extends('layouts.app')
@section('title', 'View Property Attachment')
@section('content')

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary fw-bold d-flex justify-content-between align-items-center">
            <span>Property Attachment Details</span>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Property</label>
                    <p class="form-control-plaintext">
                        {{ $propertyattachments->property->PropertyName ?? 'N/A' }}
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Document Title</label>
                    <p class="form-control-plaintext">
                        {{ $propertyattachments->DocumentTitle }}
                    </p>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Document Type</label>
                    <p class="form-control-plaintext">
                        {{ $propertyattachments->documentType->Description ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Description / Notes</label>
                <p class="form-control-plaintext">
                    {{ $propertyattachments->Description ?? 'No notes provided.' }}
                </p>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Documents</label>
                <div class="p-2 border rounded bg-light">
                    @forelse($propertyattachments->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                        {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                    @empty
                        <span class="text-muted">No documents attached.</span>
                    @endforelse
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4">
                <a href="{{ route('attachments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <div>
                    <form action="{{ route('attachments.destroy', $propertyattachments->Id) }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Are you sure you want to delete this attachment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
 @include('snippets.actions.preview-files')
@endsection
