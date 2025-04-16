@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">Procurement Mode Details</h3>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $procurement_mode->name }}</h5>
            <p class="card-text"><strong>Description:</strong><br>{{ $procurement_mode->description ?? '—' }}</p>
            <p class="card-text"><strong>Created At:</strong> {{ $procurement_mode->created_at->format('F j, Y, g:i a') }}</p>
            <p class="card-text"><strong>Last Updated:</strong> {{ $procurement_mode->updated_at->format('F j, Y, g:i a') }}</p>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('procurement-modes.edit', $procurement_mode->id) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('procurement-modes.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>
@endsection
