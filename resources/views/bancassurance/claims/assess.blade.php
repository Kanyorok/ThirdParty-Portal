@extends('layouts.app')
@section('title', 'Claims Assessment')

@section('content')

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="container mt-4" style="max-width: 850px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary border-bottom rounded-top-4">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-clipboard-check me-2"></i>
                Claims Assessment
            </h5>
            <small class="text-muted">
                Policy No: <span class="">#{{ $claim->policy->PolicyNumber }}</span>
            </small>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST"
                  action="{{ route('bancassurance.claims.assess', $claim->Id) }}"
                  enctype="multipart/form-data">
                @csrf

                {{-- ================= CLAIM INFORMATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Claim Information</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">Claim Type</label>
                            <input type="text"
                                   class="form-control form-control-sm bg-light"
                                   value="{{ $claim->claimtype->Description }}"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">Claim Amount</label>
                            <input type="text"
                                   class="form-control form-control-sm bg-light text-end"
                                   value="{{ $claim->currency->SymbolNative }} {{ number_format($claim->ClaimAmount, 2) }}"
                                   readonly>
                        </div>
                    </div>
                </div>

                {{-- ================= SUPPORTING DOCUMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Submitted Documents</h6>

                    <div class="p-3 border rounded-3 bg-light">
                        @forelse($claim->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        @empty
                            <span class="text-muted small">No documents attached.</span>
                        @endforelse
                    </div>
                </div>

                {{-- ================= CLAIM REASON ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Claim Reason</h6>

                    <textarea class="form-control form-control-sm bg-light"
                              rows="3"
                              readonly>{{ $claim->ClaimReason }}</textarea>
                </div>

                {{-- ================= ASSESSMENT ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Assessment Details</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">
                                Assessed Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   name="AssessmentAmount"
                                   class="form-control form-control-sm text-end"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">
                                Decision <span class="text-danger">*</span>
                            </label>
                            <select name="Decision"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Decision --</option>
                                @foreach($decisions as $type)
                                    <option value="{{ $type->ID }}">
                                        {{ $type->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= ASSESSMENT DOCUMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Assessment Documents</h6>

                    <div class="col-md-8">
                        <label class="form-label small ">
                            Upload File
                        </label>
                        <small class="text-muted d-block mb-1">
                            PDF, JPG, PNG, DOCX, XLSX · Max 25MB
                        </small>
                        <input type="file"
                               name="file[]"
                               class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx">
                    </div>
                </div>

                {{-- ================= COMMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Assessment Comments <span class="text-danger">*</span></h6>

                    <textarea name="AssessmentComments"
                              class="form-control form-control-sm"
                              rows="3"
                              placeholder="Provide justification or notes for this assessment..."
                              required></textarea>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('bancassurance.claims.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Back
                    </a>
                    <button type="submit"
                            class="btn btn-sm btn-primary px-4">
                        <i class="bi bi-check2-circle me-1"></i> Submit Assessment
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
