@extends('layouts.app')
@section('title', 'Claim Assessment Details')

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

<div class="container mt-4" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header bg-primary border-bottom rounded-top-4">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-clipboard-data me-2"></i>
                Claim Assessment Details
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form>

                {{-- ================= CLAIM INFORMATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Claim Information</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">Policy Number</label>
                            <input type="text"
                                   class="form-control form-control-sm bg-light"
                                   value="{{ $assessment->claim->policy->PolicyNumber ?? '-' }}"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">Decision</label>
                            <input type="text"
                                   class="form-control form-control-sm bg-light"
                                   value="{{ $assessment->decision->Description ?? '-' }}"
                                   readonly>
                        </div>
                    </div>
                </div>

                {{-- ================= ASSESSMENT INFORMATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Assessment Information</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small ">Assessed By</label>
                            <input type="text"
                                   class="form-control form-control-sm bg-light"
                                   value="{{ $assessment->assessedby->Name ?? '-' }}"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">Assessment Date</label>
                            <input type="text"
                                   class="form-control form-control-sm bg-light"
                                   value="{{ $assessment->AssessmentDate
                                        ? \Carbon\Carbon::parse($assessment->AssessmentDate)->format('d M Y')
                                        : '-' }}"
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small ">Assessment Amount</label>
                            <input type="text"
                                   class="form-control form-control-sm bg-light text-end"
                                   value="{{ number_format($assessment->AssessmentAmount, 2) }}"
                                   readonly>
                        </div>
                    </div>
                </div>

                {{-- ================= SUPPORTING DOCUMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Submitted Documents</h6>

                    <div class="p-3 border rounded-3 bg-light">
                        @forelse($assessment->claim->documents()->get([
                            't_Documents.Id',
                            't_Documents.DocumentId',
                            'MimeType',
                            'Name'
                        ]) as $document)
                            {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
                        @empty
                            <span class="text-muted small">No documents attached.</span>
                        @endforelse
                    </div>
                </div>

                {{-- ================= COMMENTS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Assessment Comments</h6>

                    <textarea class="form-control form-control-sm bg-light"
                              rows="3"
                              readonly>{{ $assessment->AssessmentComments ?? 'N/A' }}</textarea>
                </div>

            </form>
        </div>

        {{-- Footer --}}
        <div class="card-footer bg-light rounded-bottom-4 px-4 py-3">

            {{-- Actions --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('bancassurance.claims.index') }}"
                   class="btn btn-sm btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left-circle me-1"></i> Back
                </a>

                <a href="{{ route('bancassurance.claims.assessment_edit', $assessment->Id) }}"
                   class="btn btn-sm btn-primary px-4">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                </a>
            </div>

            {{-- Audit Trail --}}
            <div class="border-top pt-3 text-muted small">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Created By:</strong> {{ $assessment->CreatedBy ?? '-' }}
                    </div>
                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                        <strong>Modified By:</strong> {{ $assessment->ModifiedBy ?? '-' }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
    @include('snippets.actions.preview-files')
@endsection
