@extends('layouts.app')
@section('title', 'Legal Documents')
@section('content')
<div class="container">
    <div class="card p-4 shadow rounded-4">

        <div class="card-header bg-light px-3 py-1">
            <h6 class="mb-0">📁 Legal Documents Registry</h6>
        </div>
        <div class="card-body">
            <p class="text-muted"></p>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('legal.documents.create') }}" class="btn btn-primary mb-3 btn-sm">➕ Add New Document</a>
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
                <tr>
                    <td>Procurement Contract – Supplier X</td>
                    <td>Contract</td>
                    <td>Procurement</td>
                    <td>Approved</td>
                    <td>Signed</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-primary">View</a>
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
                        <a href="#" class="btn btn-sm btn-primary">View</a>
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
                        <a href="#" class="btn btn-sm btn-primary">View</a>
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
                        <a href="#" class="btn btn-sm btn-primary">View</a>
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
                        <a href="#" class="btn btn-sm btn-primary">View</a>
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
