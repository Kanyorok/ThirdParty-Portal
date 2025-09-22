@extends('layouts.app')
@section('title', 'Evidence Details')

@section('content')
    <div class="container">
        <div class="card p-3 shadow rounded-4 mb-0">
            <div class="card-header bg-light  rounded-3 px-3 py-2 ">
                <div class="row">
                    <div class="col">
                        <h5 class="text-info mb-0">
                            <i class="fas fa-file-alt"></i> Evidence Details for Case: {{ $evidence->case->CaseTitle }}
                        </h5>
                    </div>
                    <div class="col text-end">
                        <a href="{{ route('legal.cases.evidence.index', $evidence->case->Id) }}"
                           class="btn btn-outline-secondary">
                            <i class="fas fa-long-arrow-alt-left"></i> Back to Evidence List
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Below are the details for this evidence linked to
                    <strong class="text-dark">{{ $evidence->case->CaseTitle }}</strong>.
                </p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded-3">
                            <strong class="text-info">Evidence Title:</strong>
                            <p>{{ $evidence->EvidenceTitle }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded-3">
                            <strong class="text-info">Linked Case:</strong>
                            <p>{{ $evidence->case->CaseNumber ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded-3">
                            <strong class="text-info">DMS Document ID:</strong>
                            <div class="d-flex justify-content-between">
                                @if($evidence->DMSDocumentID)
                                    <p>{{ $evidence->DMSDocumentID ?? 'N/A' }}</p>
                                    <p>
                                        <a href="#" class="btn btn-sm btn-primary">
                                            View Document
                                        </a>
                                    </p>
                                @else
                                    <p>N/A</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 bg-light rounded-3">
                            <strong class="text-info">External File Link:</strong>
                            @if($evidence->ExternalLink)
                                <p>
                                    <a href="{{ $evidence->ExternalLink }}" target="_blank" class="text-primary">
                                        View Document <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </p>
                            @else
                                <p>N/A</p>
                            @endif
                        </div>
                    </div>
                </div>


                <div class="mb-3">
                    <div class="p-2 bg-light rounded-3">
                        <strong class="text-info">Description:</strong>
                        <p>{{ $evidence->Description ?? 'No description provided.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
