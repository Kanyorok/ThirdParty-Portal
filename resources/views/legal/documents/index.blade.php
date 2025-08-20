@extends('layouts.app')
@section('title', 'Legal Documents')
@section('content')
<div class="container">
    <div class="card p-2 shadow rounded-4">

        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-info"><i class="fas fa-folder"></i> Legal Documents Registry</h5>
            <a href="{{ route('legal.documents.create') }}" class="btn btn-info mb-3 btn-sm p-2"><i class="fas fa-plus me-1"></i> Add New Document</a>
        </div>
        <div class="card-body">
            <p class="text-muted"></p>
        <table class="table table-hover table-sm align-middle text-centre"
               style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
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
                <tr>
                    <td>Procurement Contract – Supplier X</td>
                    <td>Contract</td>
                    <td>Procurement</td>
                    <td>Approved</td>
                    <td>Signed</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">View</a>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>Office Lease Agreement – Downtown Hub</td>
                    <td>Lease</td>
                    <td>Property</td>
                    <td>In Review</td>
                    <td>Pending</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">View</a>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>Employee NDA – John Doe</td>
                    <td>NDA</td>
                    <td>HR</td>
                    <td>Draft</td>
                    <td>Pending</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">View</a>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>Vendor Agreement – Alpha Supplies</td>
                    <td>Contract</td>
                    <td>Procurement</td>
                    <td>Approved</td>
                    <td>Archived</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">View</a>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td>Partnership MOU – Beta Corp</td>
                    <td>MOU</td>
                    <td>Legal</td>
                    <td>Rejected</td>
                    <td>Pending</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">View</a>
                        <a href="#" class="btn btn-sm btn-warning">Edit</a>
                        <a href="#" class="btn btn-sm btn-danger">Delete</a>
                    </td>
                </tr>
                {{-- @forelse ($documents as $doc)
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
                @endforelse --}}
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
