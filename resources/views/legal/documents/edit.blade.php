@extends('layouts.app')
@section('title', 'Edit Legal Document')
@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Document</h4>
    <form method="POST" action="{{ route('legal.documents.update', $document->ID) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="DocumentTitle" class="form-label">Title</label>
            <input type="text" name="DocumentTitle" class="form-control" value="{{ $document->DocumentTitle }}" required>
        </div>
        <div class="mb-3">
            <label for="Remarks" class="form-label">Remarks</label>
            <textarea name="Remarks" class="form-control">{{ $document->Remarks }}</textarea>
        </div>
        <div class="mb-3">
            <label for="ExecutionStatus" class="form-label">Execution Status</label>
            <select name="ExecutionStatus" class="form-control">
                <option {{ $document->ExecutionStatus == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option {{ $document->ExecutionStatus == 'Signed' ? 'selected' : '' }}>Signed</option>
                <option {{ $document->ExecutionStatus == 'Archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Document</button>
    </form>
</div>
@endsection
