@extends('layouts.app')
@section('title', 'Add Clause')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ Add New Clause</h4>

    <form action="{{ route('legal.clauses.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="Title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Clause Type</label>
            <input type="text" name="ClauseType" class="form-control" placeholder="e.g. Termination, Payment, etc.">
        </div>

        <div class="mb-3">
            <label class="form-label">Clause Content</label>
            <textarea name="Content" rows="6" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Version</label>
            <input type="text" name="Version" class="form-control" placeholder="e.g. v1.0, Draft 2">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="IsStandard" id="IsStandard" checked>
            <label class="form-check-label" for="IsStandard">Mark as Standard Clause</label>
        </div>

        <button class="btn btn-success">💾 Save Clause</button>
        <a href="{{ route('legal.clauses.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
