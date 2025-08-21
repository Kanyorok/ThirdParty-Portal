@extends('layouts.app')
@section('title', 'Add Drivers')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container py-4">

    <!-- Driver List Header + Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Driver List</h4>
        <a href="{{ route('drivermanagement.create') }}" class="btn btn-primary">Add New Driver</a>
    </div>

    <!-- Driver Table -->
    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
        <table id="driversTable" class="table table-bordered table-striped align-middle">
            <thead class="table-light">  
                        <tr>
                            <th>#</th>
                            <th>Driver ID</th>
                            <th>Name</th>
                            <th>License Number</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $driver)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $driver->DriverID }}</td>                    
                            <td>{{ $driver->DriverName }}</td>
                            <td>{{ $driver->LicenseNumber }}</td>
                            <td>{{ \Carbon\Carbon::parse($driver->LicenseExpiryDate)->format('m/d/Y') }}</td>
                            <td>{{ $driver->status->Description ?? 'Unknown' }}</td>
                            <td>{{ $driver->Phone }}</td>
                            <td>
                                <a href="{{ route('drivermanagement.show', $driver->Id) }}"
                                      class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('drivermanagement.edit', $driver->Id) }}"
                                      class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('drivermanagement.destroy', $driver->Id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this driver?')">Delete</button>
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
    @endsection