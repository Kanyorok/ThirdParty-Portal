@extends('layouts.app')
@section('title', 'Legal Documents')
@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-4">📁 Legal Documents Registry</h4>
        <a href="{{ route('legal.documents.create') }}" class="btn btn-primary mb-3">➕ Add New Document</a>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Source</th>
                <th>Status</th>
                <th>Execution</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($documents as $doc)
                <tr>
                    <td>{{ $doc->DocumentTitle }}</td>
                    <td>{{ $doc->DocumentType }}</td>
                    <td>{{ $doc->SourceModule }}</td>
                    <td>{{ $doc->ReviewStatus }}</td>
                    <td>{{ $doc->ExecutionStatus }}</td>
                    <td>
                        <a href="{{ route('legal.documents.show', $doc->ID) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('legal.documents.edit', $doc->ID) }}" class="btn btn-warning btn-sm">Edit</a>
                        <a href="{{ route('legal.documents.dispatches.index', $doc->ID) }}" class="btn btn-sm btn-secondary">Dispatches</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No documents found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
