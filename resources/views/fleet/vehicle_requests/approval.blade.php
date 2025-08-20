@extends('layouts.app')
@section('title', 'Review Vehicle Request')

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4"> Vehicle Request Details </h4>

    {{-- Read-only details from the request --}}
    <dl class="row">
        <dt class="col-sm-3">Requested By:</dt>
        <dd class="col-sm-9">{{ $vehicleRequest->requester->FirstName ?? '' }} {{ $vehicleRequest->requester->LastName ?? '' }}</dd>

        <dt class="col-sm-3">Request Date:</dt>
        <dd class="col-sm-9">{{ $vehicleRequest->RequestDate }}</dd>

        <dt class="col-sm-3">Department:</dt>
        <dd class="col-sm-9">{{ $vehicleRequest->department->Name ?? '-' }}</dd>

        <dt class="col-sm-3">Trip Dates:</dt>
        <dd class="col-sm-9">
            {{ $vehicleRequest->trip?->TripStartDate ?? $vehicleRequest->TripDate }}
            @if(!empty($vehicleRequest->trip?->TripEndDate))
                → {{ $vehicleRequest->trip->TripEndDate }}
            @endif
        </dd>

        <dt class="col-sm-3">From:</dt>
        <dd class="col-sm-9">{{ $vehicleRequest->FromLocation }}</dd>

        <dt class="col-sm-3">To:</dt>
        <dd class="col-sm-9">{{ $vehicleRequest->ToLocation }}</dd>

        <dt class="col-sm-3">Vehicle Type:</dt>
        <dd class="col-sm-9">
            {{ $vehicleRequest->vehicle?->Description ?? '-' }}
        </dd>

        <dt class="col-sm-3">Passengers:</dt>
        <dd class="col-sm-9">{{ $vehicleRequest->PassengerCount }}</dd>

        <dt class="col-sm-3">Purpose:</dt>
        <dd class="col-sm-9">{{ $vehicleRequest->Purpose }}</dd>
    </dl>

    {{-- Approval section --}}
    <form action="{{ route('fleet.vehicle_requests.approve', $vehicleRequest->Id) }}" method="POST">
        @csrf

        {{-- Approved On --}}
        <div class="col-md-6 mb-3">
            <label for="ApprovedOn" class="form-label">Approval Date</label>
            <input type="date" name="ApprovedOn" class="form-control" 
                   value="{{ now()->toDateString() }}" required>
        </div>

        {{-- Approved By (hidden field: current user) --}}
        <input type="hidden" name="ApprovedBy" value="{{ Auth::id() }}">

        <div class="mb-3">
            <label for="RejectionReason" class="form-label">Comments / Rejection Reason</label>
            <textarea name="RejectionReason" class="form-control" rows="3"></textarea>
        </div>

        <div class="d-flex gap-2">
            @if($vehicleRequest->Status === 'Pending' || 
                $vehicleRequest->Status === \App\Models\Core\CodeDetail::where('CodeID','VehicleRequestStatus')->where('Description','Pending')->value('Value'))
                <button type="submit" name="Status" value="Approved" class="btn btn-success">
                    ✅ Approve
                </button>
                <button type="submit" name="Status" value="Rejected" class="btn btn-danger">
                    ❌ Reject
                </button>
            @else
                <button type="button" class="btn btn-secondary" disabled>
                    ✔ Already {{ $vehicleRequest->Status == 'Ap' ? 'Approved' : 'Rejected' }}
                </button>
            @endif
        </div>
    </form>
</div>
@endsection
