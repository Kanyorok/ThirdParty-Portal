@extends('layouts.app')

@section('title', 'Fuel Types')

@section('content')
    <div class="container mt-4">

        {{-- ✅ Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <p>
                <i class="fas fa-info-circle"></i>
                <span class="text-info" data-bs-toggle="tooltip" title="This indicates how many vehicles are associated with each fleet brand."></span>
                <i>Help Notes: The Usage Column shows the number of vehicles under the car fuel type </i>
            </p>
        {{-- ✅ Add new fuel type --}}
        <a href="{{ route('fueltypes.create') }}" class="btn btn-primary mb-3">Add New Fuel Type</a>

        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Fuel Name</th>
                    <th>Notes</th>
                    <th>Active</th>
                    <th>Usage</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            @forelse($fuelTypes as $fuelType)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $fuelType->FuelName }}</td>
                    <td>{{ $fuelType->Description ?? '-' }}</td>

                    <td>
                        @if($fuelType->IsActive)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>

                    {{-- ✅ Show usage count --}}
                    <td>
                        @if($fuelType->vehicles_count > 0)
                            <span class="badge bg-info text-dark">
                                In Use ({{ $fuelType->vehicles_count }})
                            </span>
                        @else
                            <span class="badge bg-secondary">Not In Use</span>
                        @endif
                    </td>

                    {{-- ✅ Actions --}}
                    <td>
                        <a href="{{ route('fueltypes.show', $fuelType->Id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('fueltypes.edit', $fuelType->Id) }}" class="btn btn-warning btn-sm">Edit</a>

                        @if($fuelType->vehicles_count > 0)
                            {{-- 🔒 Locked delete button --}}
                            <button class="btn btn-danger btn-sm" disabled
                                    title="Cannot delete — this fuel type is in use by {{ $fuelType->vehicles_count }} vehicle(s)">
                                Delete
                            </button>
                        @else
                            <form action="{{ route('fueltypes.destroy', $fuelType->Id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this fuel type?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No fuel types found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
