@extends('layouts.app')
@section('title', 'View Loan Security')

@section('content')
    <div class="card shadow rounded-4 border-0">
        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center">
            <h5 class="text-info mb-0"><i class="fas fa-shield-alt"></i> Loan Security</h5>
            <a href="{{ route('legal.securities.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-long-arrow-alt-left"></i> Back
            </a>
        </div>

        <div class="card-body">
            <p class="text-muted">Overview of the registered loan security or collateral.</p>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Security Type:</h6>
                        <p class="mb-0 fw-semibold">{{ $security->SecurityType ?? '—' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Owner Name:</h6>
                        <p class="mb-0 fw-semibold">{{ $security->OwnerName ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Owner ID Number:</h6>
                        <p class="mb-0 fw-semibold">{{ $security->OwnerIDNumber ?? '—' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Loan Account Number:</h6>
                        <p class="mb-0 fw-semibold">{{ $security->LoanAccountNumber ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Value:</h6>
                        <p class="mb-0 fw-semibold">{{ number_format($security->Value, 2) ?? '—' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Institution:</h6>
                        <p class="mb-0 fw-semibold">{{ $security->Institution ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Registration Details:</h6>
                        <p class="mb-0 fw-semibold">{{ $security->RegistrationDetails ?? '—' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <h6 class="text-info mb-1">Location</h6>
                        <p class="mb-0 fw-semibold">{{ $security->Locations ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-light rounded-3 mb-3">
                <h6 class="text-info mb-1">Remarks</h6>
                <p class="fw-semibold mb-0">{{ $security->Remarks ?? '—' }}</p>
            </div>

            {{-- Attachments Section --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-muted"><i class="far fa-paperclip me-2"></i>Attachments</h6>
                </div>
                <div class="card-body" id="securityAttachments">
                    @php
                    $documents = $security->documents()
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
