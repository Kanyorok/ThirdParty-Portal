@extends('layouts.app')
@section('title', 'IP Tracking List')

@section('content')
<div class="card shadow p-4 rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">📍 IP Renewal / Dispute Tracker</h4>
        <a href="{{ route('legal.ip_tracking.create') }}" class="btn btn-primary">➕ New Tracking Entry</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>IP Title</th>
                <th>Tracking Type</th>
                <th>Start Date</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trackings as $tracking)
            <tr>
                <td>{{ $tracking->IPTitle }}</td>
                <td>{{ $tracking->TrackingType }}</td>
                <td>{{ $tracking->StartDate }}</td>
                <td>{{ $tracking->Status }}</td>
                <td>{{ $tracking->Remarks }}</td>
                <td>
                    <a href="{{ route('legal.ip_tracking.edit', $tracking->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-muted">No IP tracking entries found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
