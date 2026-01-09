@extends('layouts.app')
@section('title', 'Intellectual Property Details')

@section('content')
    <div class="card shadow rounded-4 border-0">
        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center">
            <h5 class="text-info mb-0"><i class="fas fa-brain"></i> Intellectual Property</h5>
            <a href="{{ route('legal.intellectual.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-long-arrow-alt-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <p class="text-muted">Overview of the registered intellectual property.</p>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Type:</h6>
                        <p class="mb-0 fw-semibold">{{ $record->IPType ?? '—' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Title:</h6>
                        <p class="mb-0 fw-semibold">{{ $record->Title ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Registration Body:</h6>
                        <p class="mb-0 fw-semibold">{{ $record->Owner ?? '—' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Status:</h6>
                        <p class="mb-0 fw-semibold">{{ $record->Status ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Registration Number:</h6>
                        <p class="mb-0 fw-semibold">{{ $record->RegistrationNumber ?? '—' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-info mb-1">Registration Date:</h6>
                                <p class="mb-0 fw-semibold">{{ $record->RegistrationDate ?? '—' }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <h6 class="text-info mb-1">Expiry Date:</h6>
                                <p class="mb-0 fw-semibold">{{ $record->ExpiryDate ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3 bg-light rounded-3 mb-3">
                <h6 class="text-info mb-1">Remarks:</h6>
                <p class="mb-0">{{ $record->Remarks ?? '—' }}</p>
            </div>

            @if($record->IsDisputed == 1)
                <div class="p-3 bg-light rounded-3 mb-3">
                    <h6 class="text-danger mb-1"><i class="fas fa-exclamation-triangle"></i> Disputed Reason:</h6>
                    <p class="mb-0 fw-semibold">{{ $record->DisputeReason ?? 'No reason provided' }}</p>
                </div>
            @endif

            {{-- Attachments Section --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-muted"><i class="far fa-paperclip me-2"></i>Attachments</h6>
                </div>
                <div class="card-body" id="ipAttachments">
                    @php
                    $documents = $record->documents()
                        ->get(['t_Documents.Id','t_Documents.DocumentId','MimeType','Name']);
                    @endphp
                    @forelse($documents as $document)
                    @php
                    $document->setRelations([]);
                    @endphp
                    {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                    @empty
                    <span class="text-muted">No attachments.</span>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
@includeIf('snippets.actions.preview-files')
@endsection
