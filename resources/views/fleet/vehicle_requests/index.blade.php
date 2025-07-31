@extends('layouts.app')
@section('title', 'Vehicle Requests')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">📄 My Vehicle Requests</h4>
        <a href="{{ route('fleet.vehicle_requests.create') }}" class="btn btn-primary">➕ New Request</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Requested By</th>
                    <th>Request Date</th>
                    <th>Trip Date</th>
                    <th>Destination</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $req)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $req->requestedBy->name ?? '-' }}</td>
                        <td>{{ $req->RequestDate }}</td>
                        <td>{{ $req->TripDate }}</td>
                        <td>{{ $req->Destination }}</td>
                        <td><span class="badge bg-{{ $req->Status == 'Approved' ? 'success' : ($req->Status == 'Rejected' ? 'danger' : 'secondary') }}">{{ $req->Status }}</span></td>
                        <td>
                            <a href="{{ route('fleet.vehicle_requests.approve', $req->RequestID) }}" class="btn btn-sm btn-outline-primary">🔍 Review</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection