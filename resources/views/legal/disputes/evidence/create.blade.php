@extends('layouts.app')
@section('title', 'Link New Evidence')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ Link New Evidence to: {{ $case->CaseTitle }}</h4>

    <form method="POST" action="{{ route('legal.cases.evidence.store', $case->ID) }}">
        @csrf
        <div class="mb-3">
            <label for="EvidenceTitle" class="form-label">Evidence Title</label>
            <input type="text" name="EvidenceTitle" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description (optional)</label>
            <textarea name="Description" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label for="DMSDocumentID" class="form-label">DMS Document ID (optional)</label>
            <input type="text" name="DMSDocumentID" class="form-control">
        </div>

        <div class="mb-3">
            <label for="ExternalLink" class="form-label">External File Link (optional)</label>
            <input type="url" name="ExternalLink" class="form-control" placeholder="https://example.com/document.pdf">
        </div>

        <button type="submit" class="btn btn-success">💾 Save Evidence</button>
        <a href="{{ route('legal.cases.evidence.index', $case->ID) }}" class="btn btn-secondary">↩️ Cancel</a>
    </form>
</div>
@endsection
