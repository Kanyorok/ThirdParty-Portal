@extends('layouts.app')
@section('title', 'Edit Inspection Schedule')

@section('content')
    <div class="card p-4 shadow rounded-4">

        <form action="{{ route('fleet.inspection_schedule.update', $schedule->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="VehicleID" class="form-label">Vehicle<span class="text-danger">*</span></label>
                    <select name="VehicleID" id="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach ($vehicles as $vehicle)
                            <option
                                value="{{ $vehicle->Id }}" {{ $vehicle->Id == $schedule->VehicleID ? 'selected' : '' }}>
                                {{ $vehicle->RegistrationNo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="InspectionType" class="form-label">Inspection Type<span class="text-danger">*</span></label>
                    <input type="text" name="InspectionType" id="InspectionType"
                           value="{{ old('InspectionType', $schedule->InspectionType) }}"
                           class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="InspectionDate" class="form-label">Inspection Date<span class="text-danger">*</span></label>
                    <input type="date" name="InspectionDate" id="InspectionDate"
                           value="{{ old('InspectionDate', $schedule->InspectionDate ? \Carbon\Carbon::parse($schedule->InspectionDate)->format('Y-m-d') : '') }}"

                           class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="DueDate" class="form-label">Due Date<span class="text-danger">*</span></label>
                    <input type="date" name="DueDate" id="DueDate"
                           value="{{ old('DueDate', $schedule->DueDate ? \Carbon\Carbon::parse($schedule->DueDate)->format('Y-m-d') : '') }}"

                           class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="Status" class="form-label">Status<span class="text-danger">*</span></label>
                    <select class="form-select" name="Status" required>
                        <option value="">Select Type</option>
                        @foreach ($inspectionStatus as $status)
                            <option value="{{ $status->ID }}" {{ $status->ID == $schedule->Status ? 'selected' : '' }}>
                                {{ $status->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="Inspector" class="form-label">Inspector<span class="text-danger">*</span></label>
                    <select name="Inspector" id="Inspector" class="form-select" required>
                        <option value="">-- Select Inspector --</option>
                        @foreach ($inspectors as $inspector)
                            <option
                                value="{{ $inspector['id'] }}" {{ $inspector['id'] == $schedule->Inspector ? 'selected' : '' }}>
                                {{ $inspector['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mt-3">
                    <label for="Remarks" class="form-label">Remarks<span class="text-danger">*</span></label>
                    <input type="text" name="Remarks" id="Remarks"
                           value="{{ old('Remarks', $schedule->Remarks) }}"
                           class="form-control" required>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Update Schedule</button>
                <a href="{{ route('fleet.inspection_schedule.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
@endsection
