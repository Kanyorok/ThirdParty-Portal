@extends('layouts.app')
@section('title', 'Add IP Tracking Entry')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">➕ Add IP Tracking Entry</h4>

        <form method="POST" action="{{ route('legal.intellectual_property.tracking.store') }}">
            @csrf
            <input type="hidden" name="LegalIPID" value="{{ $ip->ID }}">

            <div class="mb-3">
                <label for="TrackingType" class="form-label">Tracking Type</label>
                <select name="TrackingType" class="form-control" required>
                    <option value="Renewal">Renewal</option>
                    <option value="Dispute">Dispute</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="TrackingDate" class="form-label">Tracking Date</label>
                <input type="date" name="TrackingDate" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="Status" class="form-label">Status</label>
                <select name="Status" class="form-control" required>
                    <option value="Pending">Pending</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Escalated">Escalated</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="NextActionDate" class="form-label">Next Action Date</label>
                <input type="date" name="NextActionDate" class="form-control">
            </div>

            <div class="mb-3">
                <label for="Description" class="form-label">Description</label>
                <textarea name="Description" class="form-control" rows="4"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Entry</button>
        </form>
    </div>
@endsection
