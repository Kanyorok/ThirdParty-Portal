@extends('layouts.app')
@section('title', 'Edit File Format')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">✏️ Edit File Format</h4>

    <form method="POST" action="{{ route('legal.setup.file_formats.update', $format->Id) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name *</label>
            <input type="text" name="Name" value="{{ $format->Name }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Mime Type</label>
            <input type="text" name="MimeType" value="{{ $format->MimeType }}" class="form-control">
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="IsActive" class="form-check-input" value="1" {{ $format->IsActive ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
        <button class="btn btn-success">💾 Update</button>
        <a href="{{ route('legal.setup.file_formats.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
