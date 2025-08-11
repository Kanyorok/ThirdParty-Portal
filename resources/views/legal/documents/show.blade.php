@extends('layouts.app')
@section('title', 'View Legal Document')
@section('content')

<div class="card p-4 shadow rounded-4 mb-4">
    <h4 class="mb-4">📄 Document Details</h4>
    <ul class="list-group">
        <li class="list-group-item"><strong>Title:</strong> {{ $document->DocumentTitle }}</li>
        <li class="list-group-item"><strong>Type:</strong> {{ $document->DocumentType }}</li>
        <li class="list-group-item"><strong>Source Module:</strong> {{ $document->SourceModule }}</li>
        <li class="list-group-item"><strong>Review Status:</strong> {{ $document->ReviewStatus }}</li>
        <li class="list-group-item"><strong>Execution Status:</strong> {{ $document->ExecutionStatus }}</li>
        <li class="list-group-item"><strong>Remarks:</strong> {{ $document->Remarks }}</li>
        <li class="list-group-item"><strong>DMS Document ID:</strong> {{ $document->LinkedDMSDocID }}</li>
    </ul>
</div>

<div class="card shadow p-4 rounded-4 mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h5>📌 Contract Obligations</h5>
        <a href="{{ route('legal.documents.obligations.index', $document->ID) }}" class="btn btn-sm btn-outline-primary">Manage Obligations</a>
    </div>
</div>

<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">✅ Execution Logs</h5>
        <a href="{{ route('legal.documents.execution_logs.index', $document->ID) }}" class="btn btn-outline-primary">
            View Logs
        </a>
    </div>
    <p class="text-muted">Track signature records and legal execution status of this document.</p>
</div>

@endsection
