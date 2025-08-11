@extends('layouts.app')
@section('title', 'Edit Clause')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">✏️ Edit Clause – {{ $clause->Title }}</h4>

    <form action="{{ route('legal.clauses.update', $clause->ID) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="Title" class="form-control" value="{{ $clause->Title }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Clause Type</label>
            <input type="text" name="ClauseType" class="form-control" value="{{ $clause->ClauseType }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Clause Content</label>
            <textarea name="Content" rows="6" class="form-control" required>{{ $clause->Content }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Version</label>
            <input type="text" name="Version" class="form-control" value="{{ $clause->Version }}">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="IsStandard" id="IsStandard"
                   {{ $clause->IsStandard ? 'checked' : '' }}>
            <label class="form-check-label" for="IsStandard">Mark as Standard Clause</label>
        </div>

        <button class="btn btn-success">💾 Update Clause</button>
        <a href="{{ route('legal.clauses.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
