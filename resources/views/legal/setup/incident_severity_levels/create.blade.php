@extends('layouts.app')
@section('title', 'Add Severity Level')

@section('content')
<div class="card shadow rounded-4 p-4">
    <h4 class="mb-3">➕ Add Incident Severity Level</h4>

    <form method="POST" action="{{ route('legal.setup.incident_severity_levels.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name *</label>
            <input type="text" name="Name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="Description" class="form-control"></textarea>
        </div>
        <button class="btn btn-success">💾 Save</button>
        <a href="{{ route('legal.setup.incident_severity_levels.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
