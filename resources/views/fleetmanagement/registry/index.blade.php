@extends('layouts.app')
@section('title', 'Register New Vehicle')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="container py-4">

        <!-- Fleet Make/Brand List Header + Add Button -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Vehicle Registry List</h4>
            <a href="{{ route('vehicle-registry.create') }}" class="btn btn-primary">Add New</a>
        </div>

        <!-- Fleet Model Table -->
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table id="vehicleRegistryTable" class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Registration No</th>
                            <th>Model</th>
                            <th>Make</th>
                            <th>Type</th>
                            <th>Color</th>
                            <th>Year</th>
                            <th>Chassis No</th>
                            <th>Engine No</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($vehicles as $vehicle)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $vehicle->RegistrationNo }}</td>
                                <td>{{ $vehicle->model->ModelName ?? 'N/A' }}</td>
                                <td>{{ $vehicle->brand->BrandName ?? 'N/A' }}</td>
                                <td>{{ $vehicle->vehicleType->Description ?? 'N/A' }}</td>
                                <td>{{ $vehicle->Color ?? 'N/A' }}</td>
                                <td>{{ $vehicle->Year ?? 'N/A' }}</td>
                                <td>{{ $vehicle->ChassisNo ?? 'N/A' }}</td>
                                <td>{{ $vehicle->EngineNo ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('vehicle-registry.show', $vehicle->Id) }}"
                                       class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('vehicle-registry.edit', $vehicle->Id) }}"
                                       class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('vehicle-registry.destroy', $vehicle->Id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this vehicle?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
