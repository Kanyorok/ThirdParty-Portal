@extends('layouts.app')
@section('title', 'Contract Registry')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">📜 Contract Registry</h4>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Title</th>
                <th>Source Module</th>
                <th>Review Status</th>
                <th>Execution Status</th>
                <th>DMS Link</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contracts as $contract)
                <tr>
                    <td>{{ $contract->DocumentTitle }}</td>
                    <td>{{ $contract->SourceModule }}</td>
                    <td>{{ $contract->ReviewStatus }}</td>
                    <td>{{ $contract->ExecutionStatus }}</td>
                    <td>
                        @if ($contract->LinkedDMSDocID)
                            <span class="text-muted">📎 DMS Linked (ID: {{ $contract->LinkedDMSDocID }})</span>
                            
                        @else
                            <span class="text-muted">Not Linked</span>
                        @endif
                    </td>
                
                    <td>
                        {{-- <a href="{{ route('legal.documents.show', $contract->ID) }}" class="btn btn-sm btn-info">👁️ View</a> --}}
                        <a href="#" class="btn btn-sm btn-info">👁️ View</a>
                        {{-- Legal review button will be added next --}}
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No contracts found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
