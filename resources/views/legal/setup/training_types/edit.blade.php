@extends('layouts.app')
@section('title', 'Edit Training Type')

@section('content')
    <div class="card shadow rounded-4 p-4">
        <h4 class="mb-3">✏️ Edit Training Type</h4>

        <form method="POST" action="{{ route('legal.setup.training_types.update', $training->Id) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="Name" value="{{ $training->Name }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="Description" class="form-control">{{ $training->Description }}</textarea>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="IsActive" class="form-check-input"
                       value="1" {{ $training->IsActive ? 'checked' : '' }}>
                <label class="form-check-label">Active</label>
            </div>
            <button class="btn btn-success">💾 Update</button>
            <a href="{{ route('legal.setup.training_types.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
