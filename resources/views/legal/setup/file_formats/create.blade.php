@extends('layouts.app')
@section('title', 'Add File Format')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-3">➕ Add File Format</h4>

        <form method="POST" action="{{ route('legal.setup.file_formats.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="Name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mime Type</label>
                <input type="text" name="MimeType" class="form-control">
            </div>
            <button class="btn btn-success">💾 Save</button>
            <a href="{{ route('legal.setup.file_formats.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
