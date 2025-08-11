@extends('layouts.app')
@section('title', 'Edit Evidence')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Evidence - {{ $evidence->EvidenceTitle }}</h4>

    <form method="POST" action="{{ route('legal.cases.evidence.update', [$case->ID, $evidence->ID]) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="EvidenceTitle" class="form-label">Evidence Title</label>
            <input type="text" name="EvidenceTitle" class="form-control" value="{{ $evidence->EvidenceTitle }}" required>
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Description</label>
            <textarea name="Description" class="form-control">{{ $evidence->Description }}</textarea>
        </div>

        <div class="mb-3">
            <label for="DMSDocumentID" class="form-label">DMS Document ID</label>
            <input type="text" name="DMSDocumentID" class="form-control" value="{{ $evidence->DMSDocumentID }}">
        </div>

        <div class="mb-3">
            <label for="ExternalLink" class="form-label">External File Link</label>
            <input type="url" name="ExternalLink" class="form-control" value="{{ $evidence->ExternalLink }}">
        </div>

        <button type="submit" class="btn btn-primary">💾 Update</button>
        <a href="{{ route('legal.cases.evidence.index', $case->ID) }}" class="btn btn-secondary">↩️ Back</a>
    </form>
</div>
@endsection
