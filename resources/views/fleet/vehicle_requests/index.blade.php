@extends('layouts.app')
@section('title', 'Vehicle Requests')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">📄 Vehicle Requests</h4>
        <a href="{{ route('fleet.vehicle_requests.create') }}" class="btn btn-primary">➕ New Request</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Request ID</th>
                    <th>Requester</th>
                    <th>Department</th>
                    <th>Request Date</th>
                    <th>Trip No</th>
                    <th>Trip Date</th>
                    <th>Route</th>
                    <th>Passengers</th>
                    <th>Vehicle Type</th>
                    <th>Status</th>
                    <th>Purpose</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $req)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $req->RequestID }}</td>
                        <td>
                            {{ $req->requester->FirstName ?? '' }} {{ $req->requester->LastName ?? '' }}
                        </td>
                        <td>{{ $req->department->Name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($req->RequestDate)->format('d/m/Y') }}</td>
                        <td>{{ $req->trip?->TripNo ?? '-' }}</td>
                        <td>
                            @if ($req->trip?->TripStartDate && $req->trip?->TripEndDate)
                                {{ \Carbon\Carbon::parse($req->trip->TripStartDate)->format('d/m/Y') }}
                                <span class="text-muted">→</span>
                                {{ \Carbon\Carbon::parse($req->trip->TripEndDate)->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $req->FromLocation }}</span> 
                            <span class="text-muted">→</span> 
                            <span class="fw-semibold">{{ $req->ToLocation }}</span>
                        </td>
                        <td>{{ $req->PassengerCount ?? '-' }}</td>
                        <td>{{ $req->vehicle->Description ?? '-' }}</td>
                        <td>
                            @php
                                $statusDescription = $req->statusDetail?->Description ?? 'Pending';
                                $statusColors = [
                                    'Pending'  => 'warning',
                                    'Approved' => 'success',
                                    'Rejected' => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$statusDescription] ?? 'secondary' }}">
                                {{ $statusDescription }}
                            </span>
                        </td>

                        <td>{{ Str::limit($req->Purpose, 30) }}</td>
                        <td>
                            <a href="{{ route('fleet.vehicle_requests.approve', $req->Id) }}" 
                               class="btn btn-sm btn-outline-primary">🔍 Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center text-muted">No vehicle requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
