@extends('layouts.app')
@section('title', 'Document Details')
@section('content')
    <div class="container">
        <div class="card shadow rounded-4 border-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-info">
                    <i class="fas fa-file-contract me-2"></i> Document Details
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('legal.documents.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <a href="{{ route('legal.documents.edit', $doc->Id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <h4 class="mb-1">{{ $doc->DocumentTitle }}</h4>
                        <p class="text-muted mb-3">{{ $doc->SourceModule }} • {{ $doc->DocumentType }}</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        @php
                            $reviewClass = match($doc->ReviewStatus) {
                                'Approved' => 'success',
                                'In Review' => 'warning',
                                'Rejected'  => 'danger',
                                default     => 'secondary'
                            };
                            $execClass = match($doc->ExecutionStatus) {
                                'Signed'   => 'success',
                                'Archived' => 'dark',
                                'Pending'  => 'warning',
                                default    => 'secondary'
                            };
                        @endphp
                        <div class="mb-1">
                            <span class="small text-muted me-1">Review:</span>
                            <span class="badge bg-{{ $reviewClass }}">{{ $doc->ReviewStatus ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="small text-muted me-1">Execution:</span>
                            <span class="badge bg-{{ $execClass }}">{{ $doc->ExecutionStatus ?: '—' }}</span>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    {{-- Document Info --}}
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">Document Info</h6>
                                <dl class="row mb-0">
                                    <dt class="col-5">Source Module</dt>
                                    <dd class="col-7">{{ $doc->SourceModule ?: '—' }}</dd>

                                    <dt class="col-5">Source ID</dt>
                                    <dd class="col-7">{{ $doc->SourceID ?: '—' }}</dd>

                                    <dt class="col-5">Linked DMS Doc ID</dt>
                                    <dd class="col-7">
                                        @if($doc->LinkedDMSDocID)
                                            <span class="badge bg-primary">{{ $doc->LinkedDMSDocID }}</span>
                                        @else
                                            —
                                        @endif
                                    </dd>

                                    <dt class="col-5">Dispatch Date</dt>
                                    <dd class="col-7">
                                        {{ $doc->DispatchDate ? \Carbon\Carbon::parse($doc->DispatchDate)->format('d/m/Y H:i') : '—' }}
                                    </dd>

                                    <dt class="col-5">Sign-off Date</dt>
                                    <dd class="col-7">
                                        {{ $doc->SignOffDate ? \Carbon\Carbon::parse($doc->SignOffDate)->format('d/m/Y H:i') : '—' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    {{-- Audit & Review --}}
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">Audit & Review</h6>
                                <dl class="row mb-0">
                                    <dt class="col-5">Reviewed By</dt>
                                    <dd class="col-7">
                                        {{-- If you add a reviewedBy() relation, swap to optional($doc->reviewedBy)->Name --}}
                                        {{ '-' ?? '—' }}
                                    </dd>

{{--                                    <dt class="col-5">Reviewed On</dt>--}}
{{--                                    <dd class="col-7">--}}
{{--                                        {{ $doc->ReviewedOn ? \Carbon\Carbon::parse($doc->ReviewedOn)->format('d/m/Y H:i') : '—' }}--}}
{{--                                    </dd>--}}

                                    <dt class="col-5">Created By</dt>
                                    <dd class="col-7">
                                        {{ optional($doc->createdBy)->Name ?? '—' }}
                                    </dd>

                                    <dt class="col-5">Created On</dt>
                                    <dd class="col-7">
                                        {{ $doc->CreatedOn ? \Carbon\Carbon::parse($doc->CreatedOn)->format('d/m/Y H:i') : '—' }}
                                    </dd>

                                    <dt class="col-5">Modified By</dt>
                                    <dd class="col-7">
                                        {{ optional($doc->modifiedBy)->Name ?? '—' }}
                                    </dd>

                                    <dt class="col-5">Modified On</dt>
                                    <dd class="col-7">
                                        {{ $doc->ModifiedOn ? \Carbon\Carbon::parse($doc->ModifiedOn)->format('d/m/Y H:i') : '—' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                @if($doc->Remarks)
                    <div class="mt-3">
                        <h6 class="text-muted">Remarks</h6>
                        <div class="border rounded p-3 bg-light">{!! nl2br(e($doc->Remarks)) !!}</div>
                    </div>
                @endif
            </div>

            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('legal.documents.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-1"></i> All Documents
                </a>
            </div>
        </div>
    </div>
@endsection
