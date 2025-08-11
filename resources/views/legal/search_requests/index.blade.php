@extends('layouts.app')
@section('title', 'Legal Search Requests')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>📋 Legal Search Requests</h4>
        <a href="{{ route('legal.search_requests.create') }}" class="btn btn-primary">➕ New Search Request</a>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Request Type</th>
                <th>Entity Name</th>
                <th>Requested By</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
        <td>Access Request</td>
        <td>ABC Corporation</td>
        <td>John Mwangi</td>
        <td>2025-08-05</td>
        <td>Pending</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Data Update</td>
        <td>XYZ Ltd</td>
        <td>Jane Wanjiru</td>
        <td>2025-08-02</td>
        <td>Approved</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Account Closure</td>
        <td>Global Traders</td>
        <td>Ali Hassan</td>
        <td>2025-07-28</td>
        <td>Rejected</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Service Activation</td>
        <td>Bright Future Ltd</td>
        <td>Lucy Kariuki</td>
        <td>2025-08-01</td>
        <td>Pending</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Document Verification</td>
        <td>Sunrise Enterprises</td>
        <td>David Otieno</td>
        <td>2025-07-30</td>
        <td>Approved</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
            {{-- @foreach($requests as $request)
                <tr>
                    <td>{{ $request->RequestType }}</td>
                    <td>{{ $request->EntityName }}</td>
                    <td>{{ $request->RequestedBy }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->RequestedOn)->format('d M Y') }}</td>
                    <td>{{ $request->Status }}</td>
                    <td>
                        <a href="{{ route('legal.search_requests.show', $request->ID) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('legal.search_requests.edit', $request->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @endforeach --}}
        </tbody>
    </table>
</div>
@endsection
