@extends('layouts.app')
@section('title', 'Contract Registry')

@section('content')
<div class="container">
    <div class="card p-4 shadow rounded-4 border-0">
        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-centre">
            <h5 class="text-info mb-0"><i class="fas fa-file-contract"></i> Contract Registry</h4>
        </div>
        <div class="card-body">
            <p class="text-muted"></p>
            <table class="table table-sm table-hover align-middle text-centre"
                style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
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
                                <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</a>
                                {{-- Legal review button will be added next --}}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No contracts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
