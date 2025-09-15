@extends('layouts.app')
@section('title', 'Edit Control Type')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">✏️ Edit Control Type</h4>

    <form method="POST" action="{{ route('legal.setup.control_types.update', $control->Id) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name *</label>
            <input type="text" name="Name" value="{{ $control->Name }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="Description" class="form-control">{{ $control->Description }}</textarea>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="IsActive" class="form-check-input" value="1" {{ $control->IsActive ? 'checked' : '' }}>
            <label class="form-check-label">Active</label>
        </div>
        <button class="btn btn-success">💾 Update</button>
        <a href="{{ route('legal.setup.control_types.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
