@extends('layouts.app')
@section('title', 'Review Vehicle Request')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🛠️ Review Vehicle Request</h4>

    <dl class="row">
        <dt class="col-sm-3">Requested By:</dt>
        <dd class="col-sm-9">{{ $requestItem->requestedBy->name ?? '-' }}</dd>

        <dt class="col-sm-3">Trip Date:</dt>
        <dd class="col-sm-9">{{ $requestItem->TripDate }}</dd>

        <dt class="col-sm-3">From:</dt>
        <dd class="col-sm-9">{{ $requestItem->PickupLocation }}</dd>

        <dt class="col-sm-3">To:</dt>
        <dd class="col-sm-9">{{ $requestItem->Destination }}</dd>

        <dt class="col-sm-3">Purpose:</dt>
        <dd class="col-sm-9">{{ $requestItem->Purpose }}</dd>
    </dl>

    <form action="{{ route('fleet.vehicle_requests.update_status', $requestItem->RequestID) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="Status" class="form-label">Approval Decision</label>
            <select name="Status" class="form-select" required>
                <option value="">-- Select --</option>
                <option value="Approved">Approve</option>
                <option value="Rejected">Reject</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="ApproverComments" class="form-label">Comments</label>
            <textarea name="ApproverComments" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">✅ Submit Decision</button>
    </form>
</div>
@endsection
