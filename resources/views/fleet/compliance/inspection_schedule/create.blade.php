@extends('layouts.app')
@section('title', 'Schedule Inspection')

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

        <form action="{{ route('fleet.inspection_schedule.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="VehicleID" class="form-label">Vehicle</label>
                    <select name="VehicleID" id="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->Id }}">{{ $vehicle->RegistrationNo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="InspectionType" class="form-label">Inspection Type</label>
                    <input type="text" name="InspectionType" id="InspectionType" class="form-control" required
                           placeholder="e.g., Roadworthiness">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="InspectionDate" class="form-label">Inspection Date</label>
                    <input type="date" name="InspectionDate" id="InspectionDate" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="DueDate" class="form-label">Due Date</label>
                    <input type="date" name="DueDate" id="DueDate" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="Status" class="form-label">Status</label>
                    <select class="form-select" name="Status" required>
                        <option value="">Select Type</option>
                        @foreach ($inspectionStatus as $status)
                            <option value="{{ $status->ID }}" {{ old('Status') == $status->ID ? 'selected' : '' }}>
                                {{ $status->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="Inspector" class="form-label">Inspector</label>

                    <select name="Inspector" id="Inspector" class="form-select">
                        <option value="">-- Select Inspector --</option>
                        @foreach ($inspectors as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>


                <div class="col-md-6">
                    <label for="Remarks" class="form-label">Remarks</label>
                    <input type="text" name="Remarks" id="Remarks" class="form-control" placeholder="Optional notes">
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success">✅ Save Schedule</button>
            </div>
        </form>
    </div>
@endsection
