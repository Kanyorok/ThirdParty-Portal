@extends('layouts.app')
@section('title', 'New IP Tracking Entry')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">➕ New IP Renewal / Dispute Tracking</h4>

    <form method="POST" action="{{ route('legal.ip_tracking.store') }}">
        @csrf

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="LegalIPID" class="form-label">Select IP</label>
                <select name="LegalIPID" class="form-control" required>
                    <option value="">-- Select --</option>
                    @foreach($ips as $ip)
                        <option value="{{ $ip->ID }}">{{ $ip->Title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="TrackingType" class="form-label">Tracking Type</label>
                <select name="TrackingType" class="form-control" required>
                    <option value="Renewal">Renewal</option>
                    <option value="Dispute">Dispute</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="StartDate" class="form-label">Start Date</label>
                <input type="date" name="StartDate" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label for="Status" class="form-label">Status</label>
                <select name="Status" class="form-control">
                    <option value="Pending">Pending</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Resolved">Resolved</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="Remarks" class="form-label">Remarks</label>
                <input type="text" name="Remarks" class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Save Tracking Entry</button>
    </form>
</div>
@endsection
