@extends('layouts.app')
@section('title', 'Edit Filing Type')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">✏️ Edit Filing Type</h4>

    <form method="POST" action="{{ route('legal.setup.filing_types.update', $filing->Id) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name *</label>
            <input type="text" name="Name" value="{{ $filing->Name }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="Description" class="form-control">{{ $filing->Description }}</textarea>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="IsActive" class="form-check-input" value="1" {{ $filing->IsActive ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
        <button class="btn btn-success">💾 Update</button>
        <a href="{{ route('legal.setup.filing_types.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
