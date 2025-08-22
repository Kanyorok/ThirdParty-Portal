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
                    <th>Employment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($drivers as $driver)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $driver->FullName }}</td>
                        <td>{{ $driver->driver->FullName ?? '—' }}</td>
                        <td>{{ $driver->NationalID }}</td>
                        <td>{{ $driver->Phone }}</td>
                        <td>{{ $driver->LicenseNumber }}</td>
                        <td>{{ $driver->LicenseExpiryDate }}</td>
                        <td>{{ $driver->employmentType->Description ?? '—' }}</td>
                        <td>
                            @if($driver->IsActive)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('fleet.drivers.show', $driver->Id) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('fleet.drivers.edit', $driver->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('fleet.drivers.destroy', $driver->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this driver?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted">No drivers registered.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
