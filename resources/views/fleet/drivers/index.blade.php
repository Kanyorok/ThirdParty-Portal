@extends('layouts.app')
@section('title', 'Drivers')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">🧑‍✈️ Fleet Driver Register</h4>
        <a href="{{ route('fleet.drivers.create') }}" class="btn btn-primary">
            + New Driver
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Staff No.</th>
                    <th>ID No.</th>
                    <th>Phone</th>
                    <th>License No.</th>
                    <th>Expiry</th>
                    <th>Category</th>
                    <th>Employment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($drivers as $driver)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $driver->FullName }}</td>
                        <td>{{ $driver->StaffNumber }}</td>
                        <td>{{ $driver->NationalID }}</td>
                        <td>{{ $driver->Phone }}</td>
                        <td>{{ $driver->LicenseNumber }}</td>
                        <td>{{ $driver->LicenseExpiryDate }}</td>
                        <td>{{ $driver->LicenseCategory }}</td>
                        <td>{{ $driver->EmploymentType }}</td>
                        <td>
                            <a href="{{ route('fleet.drivers.show', $driver->DriverID) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('fleet.drivers.edit', $driver->DriverID) }}" class="btn btn-sm btn-warning">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No drivers registered.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
