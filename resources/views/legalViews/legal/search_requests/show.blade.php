@extends('layouts.app')
@section('title', 'Search Request Details')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4>🔍 Search Request Details</h4>

    <ul class="list-group mb-4">
        <li class="list-group-item"><strong>Request Type:</strong> {{ $request->RequestType }}</li>
        <li class="list-group-item"><strong>Entity Name:</strong> {{ $request->EntityName }}</li>
        <li class="list-group-item"><strong>Purpose:</strong> {{ $request->Purpose }}</li>
        <li class="list-group-item"><strong>Requested By:</strong> {{ $request->RequestedBy }}</li>
        <li class="list-group-item"><strong>Date:</strong> {{ \Carbon\Carbon::parse($request->RequestedOn)->format('d M Y') }}</li>
        <li class="list-group-item"><strong>Status:</strong> {{ $request->Status }}</li>
    </ul>

    <a href="{{ route('legal.search_requests.edit', $request->ID) }}" class="btn btn-warning">✏️ Edit Request</a>
</div>
@endsection
