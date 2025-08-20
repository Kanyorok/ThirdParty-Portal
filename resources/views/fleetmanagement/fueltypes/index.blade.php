@extends('layouts.app')

@section('title', 'Fuel Types')

@section('content')
<div class="container mt-4">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('fueltypes.create') }}" class="btn btn-primary mb-3">Add New Fuel Type</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Fuel Name</th>
                <th>Description</th>
                <th>Active</th>
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
                    <td>
                        <a href="{{ route('fueltypes.show', $fuelType->Id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('fueltypes.edit', $fuelType->Id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('fueltypes.destroy', $fuelType->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this fuel type?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No fuel types found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
