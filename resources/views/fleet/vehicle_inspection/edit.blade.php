@extends('layouts.app')
@section('title', 'Edit Vehicle Inspection')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">✏️ Edit Vehicle Inspection</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>⚠️ Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('fleet.vehicle_inspection.update', $inspection->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Row 1 -->
        <div class="row g-3">
             <div class="col-md-4">
            <label class="form-label">Inspection Type</label>
            <select name="InspectionType" class="form-select" required>
                <option value="">-- Select Inspection Type --</option>
                @foreach($inspectionTypes as $type)
                    <option value="{{ $type->ID }}" {{ old('InspectionTypeID') == $type->ID ? 'selected' : '' }}>
                        {{ $type->Description }}
                    </option>
                @endforeach
            </select>
        </div>
            <div class="col-md-4">
                <label class="form-label">Vehicle</label>
                <select name="VehicleID" class="form-select" required>
                    <option value="">-- Select Vehicle --</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->Id }}" 
                            {{ old('VehicleID', $inspection->VehicleID) == $vehicle->Id ? 'selected' : '' }}>
                            {{ $vehicle->RegistrationNo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Driver</label>
                <select name="DriverID" class="form-select" required>
                    <option value="">-- Select Driver --</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->Id }}" 
                            {{ old('DriverID', $inspection->DriverID) == $driver->Id ? 'selected' : '' }}>
                            {{ $driver->FullName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Fuel Type</label>
                <select name="FuelType" class="form-select" required>
                    <option value="">-- Select Fuel Type --</option>
                    @foreach($fuels as $fuel)
                        <option value="{{ $fuel->Id }}" 
                            {{ old('FuelType', $inspection->FuelType) == $fuel->Id ? 'selected' : '' }}>
                            {{ $fuel->FuelName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Inspection Date</label>
                <input type="date" name="InspectionDate" class="form-control" 
                       value="{{ old('InspectionDate', $inspection->InspectionDate) }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Mileage</label>
                <input type="number" name="Mileage" class="form-control" 
                       value="{{ old('Mileage', $inspection->Mileage) }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Fuel (Litres)</label>
                <input type="number" step="0.01" name="Fuel" class="form-control" 
                       value="{{ old('Fuel', $inspection->Fuel) }}" required>
            </div>
        </div>

        <!-- Row 3 -->
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Engine Oil</label>
                <input type="number" step="0.01" name="EngineOil" class="form-control" 
                       value="{{ old('EngineOil', $inspection->EngineOil) }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Coolant</label>
                <input type="number" step="0.01" name="Coolant" class="form-control" 
                       value="{{ old('Coolant', $inspection->Coolant) }}" required>
            </div>

        <!-- Safety Equipment -->
        <div class="row mt-4">
            <div class="col-md-12">
                <label class="form-label fw-bold">Safety Equipment</label>
                <div class="d-flex flex-wrap gap-4 border rounded p-3">
                    @foreach([
                        'Reflector' => 'Reflector',
                        'FireExtinguisher' => 'Fire Extinguisher',
                        'FirstAidKit' => 'First Aid Kit',
                        'SpareTyre' => 'Spare Tyre',
                        'Spanner' => 'Spanner',
                        'Jack' => 'Jack',
                        '4XFloorMats' => '4X Floor Mats',
                    ] as $field => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                name="{{ $field }}" value="1" 
                                {{ old($field, $inspection->$field) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="mt-4">
            <button type="submit" class="btn btn-success">Update Inspection</button>
            <a href="{{ route('fleet.vehicle_inspection.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
