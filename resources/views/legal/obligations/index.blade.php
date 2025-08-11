@extends('layouts.app')
@section('title', 'Legal Obligations')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>📋 Legal Obligations</h4>
        <div>
            <a href="{{ route('legal.obligations.create') }}" class="btn btn-primary">➕ New Obligation</a>
            {{-- <a href="{{ route('legal.obligations.calendar') }}" class="btn btn-outline-secondary">📆 Calendar View</a> --}}
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Obligation</th>
                <th>Source</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
        <td>Submit Annual Tax Report</td>
        <td>Tax Authority</td>
        <td>2025-03-31</td>
        <td>Pending</td>
        <td>John Mwangi</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>File Environmental Compliance</td>
        <td>Environmental Agency</td>
        <td>2025-05-15</td>
        <td>Ongoing</td>
        <td>Jane Wanjiru</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Renew Operating License</td>
        <td>Trade Ministry</td>
        <td>2025-06-30</td>
        <td>Completed</td>
        <td>Ali Hassan</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Submit Annual Financial Audit</td>
        <td>Auditor General</td>
        <td>2025-04-20</td>
        <td>Pending</td>
        <td>Lucy Kariuki</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Report on Safety Inspections</td>
        <td>Safety Board</td>
        <td>2025-07-10</td>
        <td>Ongoing</td>
        <td>David Otieno</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
            {{-- @foreach($obligations as $obligation)
                <tr>
                    <td>{{ $obligation->Title }}</td>
                    <td>{{ $obligation->SourceType }} #{{ $obligation->SourceID }}</td>
                    <td>{{ \Carbon\Carbon::parse($obligation->DueDate)->format('d M Y') }}</td>
                    <td>{{ $obligation->Status }}</td>
                    <td>{{ $obligation->assignees->pluck('AssigneeName')->join(', ') }}</td>
                    <td>
                        <a href="{{ route('legal.obligations.edit', $obligation->ID) }}" class="btn btn-sm btn-warning">✏️ Edit</a>
                        <a href="{{ route('legal.obligations.assignments.index', $obligation->ID) }}" class="btn btn-sm btn-info">👤 Assign</a>
                    </td>
                </tr>
            @endforeach --}}
        </tbody>
    </table>
</div>
@endsection
