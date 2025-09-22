@extends('layouts.app')
@section('title', 'Driver Details')
@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title">{{ $driver->DriverName }} Details</h4>
            <a href="{{ route('drivermanagement.index') }}" class="btn btn-secondary">Back to Driver List</a>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-4"><strong>License Number:</strong> {{ $driver->LicenseNumber }}</div>
                    <div class="col-md-4"><strong>License
                            Expiry:</strong> {{ \Carbon\Carbon::parse($driver->LicenseExpiryDate)->format('d/m/Y') }}
                    </div>
                    <div class="col-md-4"><strong>Phone:</strong> {{ $driver->Phone }}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><strong>Email:</strong> {{ $driver->Email }}</div>
                    <div class="col-md-4"><strong>Status:</strong> {{ $driver->status->Description ?? 'N/A' }}</div>
                    <div class="col-md-4"><strong>Remarks:</strong> {{ $driver->Remarks }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
