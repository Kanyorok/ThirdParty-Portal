@extends('layouts.app')
@section('title', 'License History')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">🧾 License Tracking History – {{ $driver->FullName }}</h4>

    <div class="mb-3">
        <a href="{{ route('fleet.licenses.create', $driver->DriverID) }}" class="btn btn-primary">+ Add New License Entry</a>
        <a href="{{ route('fleet.drivers.show', $driver->DriverID) }}" class="btn btn-secondary ms-2">⬅ Back to Driver</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>License No.</th>
                    <th>Category</th>
                    <th>Issue Date</th>
                    <th>Expiry Date</th>
                    <th>Renewed On</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($licenses as $license)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $license->LicenseNumber }}</td>
                        <td>{{ $license->LicenseCategory }}</td>
                        <td>{{ $license->IssueDate }}</td>
                        <td>{{ $license->ExpiryDate }}</td>
                        <td>{{ $license->RenewalDate ?? '-' }}</td>
                        <td>{{ $license->Notes }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No license records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
