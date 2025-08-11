@extends('layouts.app')
@section('title', 'Evidence Details')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📄 Evidence Details</h4>
    <ul class="list-group">
        <li class="list-group-item"><strong>Title:</strong> {{ $evidence->EvidenceTitle }}</li>
        <li class="list-group-item"><strong>Description:</strong> {{ $evidence->Description }}</li>
        <li class="list-group-item"><strong>DMS Document ID:</strong> {{ $evidence->DMSDocumentID ?? '-' }}</li>
        <li class="list-group-item">
            <strong>External Link:</strong>
            @if ($evidence->ExternalLink)
                <a href="{{ $evidence->ExternalLink }}" target="_blank">View Document</a>
            @else
                -
            @endif
        </li>
        <li class="list-group-item"><strong>Uploaded On:</strong> {{ \Carbon\Carbon::parse($evidence->UploadedOn)->format('d M Y H:i') }}</li>
    </ul>
    <a href="{{ route('legal.cases.evidence.index', $evidence->LegalCaseID) }}" class="btn btn-secondary mt-3">↩️ Back</a>
</div>
@endsection
