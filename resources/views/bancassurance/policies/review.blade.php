@extends('layouts.app')
@section('title', 'Review Proposal')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-2 px-3">
            <h5 class="mb-0">
                <i class="bi bi-search me-2"></i> Review Proposal – Policy #{{ $policy->Id }}
            </h5>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Customer:</strong> {{ $policy->customer->thirdParty->ThirdPartyName ?? '-' }}</p>
                    <p class="mb-1"><strong>Product:</strong> {{ $policy->product->Name ?? '-' }}</p>
                    <p class="mb-1"><strong>Sum Assured:</strong> {{ number_format($policy->SumAssured, 2) }}</p>
                    <p class="mb-1"><strong>Premium:</strong> {{ number_format($policy->PremiumAmount, 2) }}</p>
                    <p class="mb-0">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $policy->Status->badgeColor() }}">
                            {{ $policy->Status->label() }}
                        </span>
                    </p>
                </div>
            </div>

            @if($policy->documents()->count() > 0)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-light py-2 px-3 fw-semibold">
                    <i class="bi bi-paperclip me-2"></i> Attached Documents
                </div>
                <div class="card-body small" id="ticketsAttachementContents">
                    @foreach($policy->documents()->get(['t_Documents.Id', 't_Documents.DocumentId', 'MimeType', 'Name']) as $document)
                        {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                    @endforeach
                </div>
            </div>
            @endif

            <form action="{{ route('bancassurance.policies.submitUnderwriting', $policy->Id) }}" 
                  method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Upload Proposal Documents <span class="text-danger">*</span></label>
                    <input type="file" name="file[]" class="form-control" multiple required>
                    <div class="form-text">Accepted formats: PDF, images, etc.</div>
                </div>

                <div class="text-end mt-3">
                    <a href="{{ route('bancassurance.policies.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">
                        <i class="bi bi-send-check me-1"></i> Submit to Underwriter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('snippets.actions.preview-files')
@endsection
