@extends('layouts.app')
@section('title', 'IP Tracking History')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">📂 IP Renewal / Dispute Tracking History</h4>

        <a href="{{ route('legal.intellectual_property.tracking.create', ['ip_id' => $ip->ID]) }}"
           class="btn btn-primary mb-3">➕ Add Tracking Entry</a>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Type</th>
                <th>Date</th>
                <th>Status</th>
                <th>Next Action</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>
            @forelse($trackings as $track)
                <tr>
                    <td>{{ $track->TrackingType }}</td>
                    <td>{{ \Carbon\Carbon::parse($track->TrackingDate)->format('d M Y') }}</td>
                    <td>{{ $track->Status }}</td>
                    <td>{{ $track->NextActionDate ? \Carbon\Carbon::parse($track->NextActionDate)->format('d M Y') : '-' }}</td>
                    <td>{{ $track->Description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No tracking entries found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
