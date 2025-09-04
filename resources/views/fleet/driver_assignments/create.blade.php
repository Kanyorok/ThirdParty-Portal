@extends('layouts.app')
@section('title', 'Assign Driver to Vehicle')

    
@section('content')
  @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">👨‍✈️ Assign Driver to Vehicle</h4>

        <form action="{{ route('fleet.driver_assignments.store') }}" method="POST">
            @csrf

            <div class="col-md-6">
                <label for="DriverName" class="form-label">Driver</label>
                <input type="text" class="form-control" value="{{ $driver->FullName }}" readonly>
                <input type="hidden" name="DriverID" value="{{ $driver->DriverID }}">
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="VehicleID" class="form-label">Select Vehicle</label>
                    <select name="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->Id }}">{{ $vehicle->RegistrationNo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="AssignmentDate" class="form-label">Assigned On</label>
                    <input type="date" name="AssignmentDate" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label for="UnassignmentDate" class="form-label">Unassigned On</label>
                    <input type="date" name="UnassignmentDate" class="form-control">
                </div>

                <div class="col-md-12">
                    <label for="Purpose" class="form-label">Purpose</label>
                    <input type="text" name="Purpose" class="form-control"
                           placeholder="e.g. Route coverage, temporary assignment">
                </div>

                <div class="col-md-12">
                    <label for="Notes" class="form-label">Notes</label>
                    <textarea name="Notes" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">🚚 Assign Driver</button>
            </div>
        </form>
    </div>
@endsection
