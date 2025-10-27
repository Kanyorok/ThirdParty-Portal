@extends('layouts.app')

@section('content')
<div class="container my-5">

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Prequalification Application</h1>
            <p class="text-muted mb-0">Detailed view for application
                <span class="fw-semibold">#{{ $application->ApplicationID }}</span>
            </p>
        </div>
        <div class="d-flex align-items-center">
            {{-- Conditionally show the Evaluate button --}}
            @if($application->round)
            <a href="{{ route('prequalification.prequalification-evaluation.show', $application->ApplicationID) }}" class="btn btn-primary me-2">
                <i class="fa fa-clipboard-check me-1"></i> Evaluate
            </a>
            @endif

            <a href="{{ route('prequalification.prequalification-applications.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Back to Applications
            </a>
        </div>
    </div>

    <div class="border-0 mb-5">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle w-100">
                    <tbody>
                        <tr>
                            <td class="fw-semibold text-muted">Application ID</td>
                            <td><span class="fw-bold text-dark">APP-{{ $application->ApplicationID }}</span></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Status</td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 {{ $application->Status->getColor() }} fw-bold">
                                    {{ $application->Status->getLabel() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Date Submitted</td>
                            <td>{{ $application->SubmittedOn?->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Round</td>
                            <td>{{ $application->round->Title ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Supplier Name</td>
                            <td>{{ $application->supplier->ThirdPartyName ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Trading Name</td>
                            <td>{{ $application->supplier->TradingName ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Email</td>
                            <td>
                                <a href="mailto:{{ $application->supplier->Email }}" class="text-decoration-none">
                                    {{ $application->supplier->Email ?? 'N/A' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Phone Number</td>
                            <td>
                                <a href="tel:{{ $application->supplier->Phone }}" class="text-decoration-none">
                                    {{ $application->supplier->Phone ?? 'N/A' }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Supplier Physical Address</td>
                            <td>{{ $application->supplier->PhysicalAddress ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold text-muted">Categories Applied</td>
                            <td>
                                @if($application->category)
                                    <span class="badge bg-secondary">{{ $application->category->CategoryName }}</span>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @php
        // Best-effort eager load documents if relation exists
        $application->loadMissing(['documents.dmsDocument.current']);
        $docs = optional($application->documents)->sortByDesc('CreatedOn');
    @endphp

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white">
            <h5 class="mb-0 text-primary"><i class="fa fa-paperclip me-2"></i>Supporting Documents</h5>
        </div>
        <div class="card-body">
            @if($docs && $docs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Section</th>
                                <th>Type</th>
                                <th>Uploaded</th>
                                <th style="width:120px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($docs as $doc)
                                @php($d = $doc->dmsDocument)
                                <tr>
                                    <td>{{ $d?->Name ?? $d?->current?->Name ?? 'Document' }}</td>
                                    <td>{{ $doc->SectionID ?? '-' }}</td>
                                    <td>{{ $doc->FileType ?? '-' }}</td>
                                    <td>{{ optional($doc->CreatedOn)->format('M d, Y H:i') }}</td>
                                    <td>
                                        @if($d)
                                            <a target="_blank" href="{{ url('/dms/document/'.$d->DocumentId.'/preview') }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-eye me-1"></i> Preview
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted">No supporting documents uploaded.</div>
            @endif
        </div>
    </div>
</div>
@endsection
