@extends('layouts.app')
@section('title', 'Edit IP Tracking Entry')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">✏️ Edit IP Tracking</h4>

        <form method="POST" action="{{ route('legal.ip_tracking.update', $tracking->ID) }}">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">IP Title</label>
                    <input type="text" value="{{ $tracking->ip->Title ?? '-' }}" class="form-control" readonly>
                </div>

                <div class="col-md-6">
                    <label for="TrackingType" class="form-label">Tracking Type</label>
                    <select name="TrackingType" class="form-control" required>
                        <option value="Renewal" {{ $tracking->TrackingType == 'Renewal' ? 'selected' : '' }}>Renewal
                        </option>
                        <option value="Dispute" {{ $tracking->TrackingType == 'Dispute' ? 'selected' : '' }}>Dispute
                        </option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="StartDate" class="form-label">Start Date</label>
                    <input type="date" name="StartDate" class="form-control" value="{{ $tracking->StartDate }}"
                           required>
                </div>

                <div class="col-md-4">
                    <label for="Status" class="form-label">Status</label>
                    <select name="Status" class="form-control">
                        <option value="Pending" {{ $tracking->Status == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Ongoing" {{ $tracking->Status == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                        <option value="Resolved" {{ $tracking->Status == 'Resolved' ? 'selected' : '' }}>Resolved
                        </option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="Remarks" class="form-label">Remarks</label>
                    <input type="text" name="Remarks" class="form-control" value="{{ $tracking->Remarks }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Tracking</button>
        </form>
    </div>
@endsection
