@extends('layouts.app')
@section('title', 'New Vehicle Inspection')

@section('content')
<div class="card shadow p-4 rounded-4">
    <h4 class="mb-4">➕ New Vehicle Inspection</h4>

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

    <form action="{{ route('fleet.vehicle_inspection.store') }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf

        {{-- If this is a Post-Trip inspection --}}
        @if(isset($parentInspection))
            <input type="hidden" name="ParentInspectionID" value="{{ $parentInspection->Id }}">
            <input type="hidden" name="VehicleID" value="{{ $parentInspection->VehicleID }}">
            <input type="hidden" name="FuelType" value="{{ $parentInspection->FuelType }}">
            <input type="hidden" name="InspectionTypeID" value="{{ $parentInspection->InspectionTypeID }}">

            <div class="alert alert-info">
                Creating a <strong>Post-Trip</strong> inspection for:
                <ul class="mb-0">
                    <li><strong>Pre-Trip ID:</strong> {{ $parentInspection->InspectionID }}</li>
                    <li><strong>Vehicle:</strong> {{ $parentInspection->vehicle?->RegistrationNo }}</li>
                    <li><strong>Fuel Type:</strong> {{ $parentInspection->fuel?->FuelName }}</li>
                    <li><strong>Inspection Type:</strong> {{ $parentInspection->inspectionType?->Description }}</li>
                </ul>
            </div>
        @endif

        {{-- Row 1: Vehicle & Fuel (Pre-Trip only) --}}
        @unless(isset($parentInspection))
        <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Inspection Type</label>
            <select name="InspectionTypeID" class="form-select" required>
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
                        <option value="{{ $vehicle->Id }}" {{ old('VehicleID') == $vehicle->Id ? 'selected' : '' }}>
                            {{ $vehicle->RegistrationNo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Fuel Type</label>
                <select name="FuelType" class="form-select" required>
                    <option value="">-- Select Fuel Type --</option>
                    @foreach($fuels as $fuel)
                        <option value="{{ $fuel->Id }}" {{ old('FuelType') == $fuel->Id ? 'selected' : '' }}>
                            {{ $fuel->FuelName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endunless

        {{-- Driver (Always shown for both Pre-Trip & Post-Trip) --}}
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Driver</label>
                <select name="DriverID" class="form-select" required>
                    <option value="">-- Select Driver --</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->Id }}" {{ old('DriverID') == $driver->Id ? 'selected' : '' }}>
                            {{ $driver->FullName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Inspection Date</label>
                <input type="date" name="InspectionDate" class="form-control" 
                       value="{{ old('InspectionDate') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Mileage (Km)</label>
                <input type="number" name="Mileage" class="form-control" 
                       value="{{ old('Mileage') }}" required>
            </div>
        </div>

        {{-- Row 2: Fluids & Speedometer --}}
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Fuel (Litres)</label>
                <input type="number" step="0.01" name="Fuel" class="form-control" 
                       value="{{ old('Fuel') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Engine Oil (L)</label>
                <input type="number" step="0.01" name="EngineOil" class="form-control" 
                       value="{{ old('EngineOil') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Coolant (L)</label>
                <input type="number" step="0.01" name="Coolant" class="form-control" 
                       value="{{ old('Coolant') }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Speedometer (Km/h)</label>
                <input type="number" name="Speedometer" class="form-control" 
                       value="{{ old('Speedometer') }}" required>
            </div>
        </div>

        {{-- Safety Equipment --}}
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
                                   {{ old($field) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Document Upload --}}
        <div class="mb-3 mt-3">
            <label class="form-label">Upload Supporting Document</label>
            <input type="file" name="Document" class="form-control">
            <small class="text-muted">Attach inspection sheet, photos, or related files</small>
        </div>

        {{-- Submit --}}
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save Inspection</button>
            <a href="{{ route('fleet.vehicle_inspection.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
