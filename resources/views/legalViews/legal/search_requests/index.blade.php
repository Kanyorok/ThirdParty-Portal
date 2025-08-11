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
            @foreach($requests as $request)
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
            @endforeach
        </tbody>
    </table>
</div>
@endsection
