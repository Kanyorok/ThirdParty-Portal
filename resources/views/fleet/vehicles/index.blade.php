@extends('layouts.app')
@section('title', 'Fleet Vehicles')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="card p-4 shadow rounded-4">

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('fleet.vehicles.create') }}" class="btn btn-primary">➕ Add Vehicle</a>
</div>

    <div class="table-responsive">
        <table id="vehicleRegistryTable" class="table table-bordered table-striped align-middle">
            <thead class="table-light"> 
            <tr>
                <th>#</th>
                <th>Reg No</th>
                <th>Make</th>
                <th>Model</th>
                <th>Type</th>
                <th>Fuel</th>
                <th>Status</th>
                <th>Branch</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vehicles as $vehicle)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $vehicle->RegistrationNo }}</td>
                    <td>{{ $vehicle->brand->BrandName }}</td>
                    <td>{{ $vehicle->model->ModelName }}</td>
                    <td>{{ $vehicle->vehicleType->Description }}</td>
                    <td>{{ $vehicle->fuelType->FuelName }}</td>
                    <td>{{ $vehicle->status->Description }}</td>
                    <td>{{ $vehicle->branch->Name ?? '-' }}</td>
                    <td>
                        @if ($vehicle->IsActive)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </td>

                    <td>
                        <a href="{{ route('fleet.vehicles.show', $vehicle->Id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('fleet.vehicles.edit', $vehicle->Id) }}" class="btn btn-warning btn-sm">Edit</a>
                         <form action="{{ route('fleet.vehicles.destroy', $vehicle->Id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this Vehicle?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                    </td>
                    
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No vehicles found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@section('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                @if(!$vehicles->isEmpty())
                $('#vehicleRegistryTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    lengthChange: true,
                    language: {
                        emptyTable: ""
                    }
                });
                @endif
            });
        </script>
    @endsection
@endsection
