@extends('layouts.app')
@section('title', 'View Clause')

@section('content')
<div class="card shadow p-4 rounded-4">
    <div class="d-flex justify-content-between mb-4">
        <h4>📄 Clause Details</h4>
        <a href="{{ route('legal.clauses.index') }}" class="btn btn-secondary">← Back to List</a>
    </div>

    <ul class="list-group mb-4">
        <li class="list-group-item"><strong>Title:</strong> {{ $clause->Title }}</li>
        <li class="list-group-item"><strong>Type:</strong> {{ $clause->ClauseType }}</li>
        <li class="list-group-item"><strong>Version:</strong> {{ $clause->Version ?? 'N/A' }}</li>
        <li class="list-group-item"><strong>Standard:</strong> {{ $clause->IsStandard ? 'Yes' : 'No' }}</li>
        <li class="list-group-item"><strong>Status:</strong> {{ $clause->IsActive ? 'Active' : 'Inactive' }}</li>
    </ul>

    <div class="mb-3">
        <h5>📜 Clause Content:</h5>
        <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $clause->Content }}</div>
    </div>
</div>
@endsection
