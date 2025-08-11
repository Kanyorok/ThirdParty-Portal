@extends('layouts.app')
@section('title', 'Dispatch Register')
@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-4">📤 Dispatch Register for: {{ $document->DocumentTitle }}</h4>
        <a href="{{ route('legal.documents.dispatches.create', $document->ID) }}" class="btn btn-primary">➕ Add Dispatch Entry</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Dispatch Date</th>
                <th>Recipient</th>
                <th>Method</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dispatches as $d)
                <tr>
                    <td>{{ $d->DispatchDate }}</td>
                    <td>{{ $d->DispatchedTo }}</td>
                    <td>{{ $d->DispatchMethod }}</td>
                    <td>{{ $d->Status }}</td>
                    <td>{{ $d->Remarks }}</td>
                    <td>
                        <a href="{{ route('legal.documents.dispatches.show', [$document->ID, $d->ID]) }}" class="btn btn-sm btn-info">👁️ View</a>
                        <a href="{{ route('legal.documents.dispatches.edit', [$document->ID, $d->ID]) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                        <form action="{{ route('legal.documents.dispatches.destroy', [$document->ID, $d->ID]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">🗑️ Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No dispatch records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
