@extends('layouts.app')
@section('title', 'Execution Logs')
@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">✅ Execution Logs for: {{ $document->DocumentTitle }}</h4>
        <a href="{{ route('legal.documents.execution_logs.create', $document->ID) }}" class="btn btn-primary">➕ Add Execution Log</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Signed By</th>
                <th>Signed On</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($executionLogs as $log)
                <tr>
                    <td>{{ $log->SignedBy }}</td>
                    <td>{{ $log->SignedOn }}</td>
                    <td>{{ $log->Remarks }}</td>
                    <td>
                        <a href="{{ route('legal.documents.execution_logs.show', [$document->ID, $log->ID]) }}" class="btn btn-sm btn-info">👁️ View</a>
                        <a href="{{ route('legal.documents.execution_logs.edit', [$document->ID, $log->ID]) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                        <form action="{{ route('legal.documents.execution_logs.destroy', [$document->ID, $log->ID]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">🗑️ Archive</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No execution logs found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
