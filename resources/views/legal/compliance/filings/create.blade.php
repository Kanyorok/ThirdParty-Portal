@extends('layouts.app')
@section('title','Submit Filing')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-3">➕ Submit Filing</h4>
        <form method="POST" action="{{ route('legal.compliance.filings.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Template *</label>
                <select name="TemplateID" class="form-select" required>
                    @foreach($templates as $id=>$name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Submission Date *</label>
                <input type="date" name="SubmissionDate" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">File Upload</label>
                <input type="file" name="File" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="Notes" class="form-control"></textarea>
            </div>
            <button class="btn btn-success">💾 Save</button>
            <a href="{{ route('legal.compliance.filings.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
