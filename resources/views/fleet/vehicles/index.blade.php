@extends('layouts.app')
@section('title', 'Fleet Vehicles')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Fleet Vehicles List</h4>
        <a href="{{ route('fleet.vehicles.create') }}" class="btn btn-primary">
            ➕ Add Vehicle
        </a>
    </div>

    <div class="mb-3">
        <p class="mb-0" style="font-style: italic;">
            <span class="me-2">💡</span>
            Click the <strong>Details</strong> button to view complete vehicle information including trips, inspections, maintenance, repairs, and assignments.<br>
            <strong><span class="me-1">ℹ️</span>Note:</strong> These records are automatically retrieved from their respective modules. They cannot be added here; please use the appropriate menus to manage them.
        </p>
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
                    <th>Action</th>
                   
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
                            <a href="{{ route('fleet.vehicles.show', $vehicle->Id) }}"
                               class="btn btn-info btn-sm">👁️Details</a>
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
