@extends('layouts.app')
@section('title', 'Contracted Drivers')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="mb-0">🚐 Contracted Drivers</h4>
        <a href="{{ route('fleet.contracted_drivers.create') }}" class="btn btn-success">
            + Add Contracted Driver
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>ID No.</th>
                    <th>Phone</th>
                    <th>License</th>
                    <th>Expiry</th>
                    <th>Category</th>
                    <th>Source</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($drivers as $driver)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $driver->FullName }}</td>
                        <td>{{ $driver->NationalID }}</td>
                        <td>{{ $driver->Phone }}</td>
                        <td>{{ $driver->LicenseNumber }}</td>
                        <td>{{ $driver->LicenseExpiryDate }}</td>
                        <td>{{ $driver->LicenseCategory }}</td>
                        <td>{{ $driver->ContractedFrom }}</td>
                        <td>{{ $driver->Remarks }}</td>
                        <td>
                            @if($driver->IsActive)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('fleet.contracted_drivers.edit', $driver->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                            <a href="{{ route('fleet.contracted_drivers.show', $driver->ID) }}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No contracted drivers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
