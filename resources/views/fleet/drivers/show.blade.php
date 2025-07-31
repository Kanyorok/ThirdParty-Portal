@extends('layouts.app')
@section('title', 'Driver Details')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🧑‍✈️ Driver Details</h4>

    <div class="mb-3">
        <strong>Full Name:</strong> {{ $driver->FullName }}<br>
        <strong>Staff No.:</strong> {{ $driver->StaffNumber }}<br>
        <strong>ID No.:</strong> {{ $driver->NationalID }}<br>
        <strong>Phone:</strong> {{ $driver->Phone }}<br>
        <strong>License:</strong> {{ $driver->LicenseNumber }} ({{ $driver->LicenseCategory }})<br>
        <strong>License Expiry:</strong> {{ $driver->LicenseExpiryDate }}<br>
        <strong>Employment Type:</strong> {{ $driver->EmploymentType }}
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ route('fleet.driver_assignments.create', ['driver_id' => $driver->DriverID]) }}" class="btn btn-success">
            🚚 Assign Vehicle
        </a>
        <a href="{{ route('fleet.driver_assignments.index') }}" class="btn btn-primary">
            📜 View Assignments
        </a>
        <a href="#" class="btn btn-info">
           <a href="{{ route('fleet.licenses.index', $driver->DriverID) }}" class="btn btn-secondary ms-2"> 🪪 License Tracker
        </a>
        <form action="{{ route('fleet.drivers.deactivate', $driver->DriverID) }}" method="POST" onsubmit="return confirm('Deactivate this driver?');">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-danger">🛑 Deactivate</button>
        </form>
        <a href="{{ route('fleet.drivers.edit', $driver->DriverID) }}" class="btn btn-warning">
            ✏️ Edit
        </a>
    </div>
</div>
@endsection
