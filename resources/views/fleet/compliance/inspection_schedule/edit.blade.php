@extends('layouts.app')
@section('title', 'Edit Inspection Schedule')

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
        <h4 class="mb-4">✏️ Edit Inspection Schedule - {{ $schedule->Id }}</h4>

        <form action="{{ route('fleet.inspection_schedule.update', $schedule->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="VehicleID" class="form-label">Vehicle<span class="text-danger">*</span></label>
                    <select name="VehicleID" id="VehicleID" class="form-select" required>
                        <option value="">-- Select Vehicle --</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->Id }}" {{ $vehicle->Id == $schedule->VehicleID ? 'selected' : '' }}>
                                {{ $vehicle->RegistrationNo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="InspectionType" class="form-label">Inspection Type<span class="text-danger">*</span></label>
                    <input type="text" name="InspectionType" id="InspectionType"
                           value="{{ old('InspectionType', $schedule->InspectionType) }}"
                           class="form-control" required
                           placeholder="e.g., Roadworthiness">
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
                           class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="Status" class="form-label">Status<span class="text-danger">*</span></label>
                    <select class="form-select" name="Status" required>
                        <option value="">Select Status</option>
                        @foreach ($inspectionStatus as $status)
                            <option value="{{ $status->ID }}" {{ $status->ID == $schedule->Status ? 'selected' : '' }}>
                                {{ $status->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="Inspector" class="form-label">Inspector<span class="text-danger">*</span></label>
                    
                    <!-- Hidden field for form submission -->
                    <input type="hidden" name="Inspector" id="Inspector" 
                           value="{{ $schedule->Inspector }}">
                    
                    <!-- Read-only display field -->
                    <input type="text" class="form-control" 
                           value="{{ $schedule->inspector->FirstName ?? '' }} {{ $schedule->inspector->LastName ?? '' }}"
                           readonly
                           placeholder="Original inspector">
                    
                    <small class="text-muted">Inspector cannot be changed</small>
                </div>

                <div class="col-md-6 mt-3">
                    <label for="Remarks" class="form-label">Remarks<span class="text-danger">*</span></label>
                    <input type="text" name="Remarks" id="Remarks"
                           value="{{ old('Remarks', $schedule->Remarks) }}"
                           class="form-control" required
                           placeholder="Optional notes">
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">💾 Update Schedule</button>
                <a href="{{ route('fleet.inspection_schedule.index') }}" class="btn btn-secondary">⬅ Back</a>
            </div>
        </form>
    </div>
@endsection