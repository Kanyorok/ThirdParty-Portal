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

    <form action="{{ route('fleet.vehicle_inspection.update', $inspection->Id) }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- If this is a Post-Trip Inspection --}}
        @if($inspection->ParentInspectionID)
            <input type="hidden" name="VehicleID" value="{{ $inspection->VehicleID }}">
            <input type="hidden" name="FuelType" value="{{ $inspection->FuelType }}">
            <input type="hidden" name="InspectionTypeID" value="{{ $inspection->InspectionTypeID }}">
            <input type="hidden" name="ParentInspectionID" value="{{ $inspection->ParentInspectionID }}">

            <div class="alert alert-info">
                Editing a <strong>Post-Trip</strong> inspection for:
                <ul class="mb-0">
                    <li><strong>Pre-Trip ID:</strong> {{ $inspection->parentInspection?->InspectionID }}</li>
                    <li><strong>Vehicle:</strong> {{ $inspection->vehicle?->RegistrationNo }}</li>
                    <li><strong>Fuel Type:</strong> {{ $inspection->fuel?->FuelName }}</li>
                    <li><strong>Inspection Type:</strong> {{ $inspection->inspectionType?->Description }}</li>
                </ul>
            </div>
        @else
            {{-- Pre-Trip Inspection --}}
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Inspection Type<span class="text-danger">*</span></label>
                    <select name="InspectionTypeID" class="form-select" required>
                        <option value="">-- Select Inspection Type --</option>
                        @foreach($inspectionTypes as $type)
                            <option value="{{ $type->ID }}"
                                {{ old('InspectionTypeID', $inspection->InspectionTypeID) == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Vehicle<span class="text-danger">*</span></label>
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
                    <label class="form-label">Fuel Type</label>
                    <input type="hidden" name="FuelType" id="FuelTypeID" value="{{ $inspection->FuelType }}">
                    <input type="text" id="FuelTypeName" class="form-control"
                           value="{{ $inspection->fuel?->FuelName }}" readonly>
                </div>
            </div>
        @endif

        {{-- Driver, Date, Mileage --}}
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label class="form-label">Driver<span class="text-danger">*</span></label>
                <input type="hidden" name="DriverID" id="DriverID" value="{{ old('DriverID', $inspection->DriverID) }}">
                <input type="text" id="DriverName" class="form-control"
                       value="{{ $inspection->driver?->FullName }}" readonly>
            </div>

            <div class="col-md-3">
                <label class="form-label">Inspection Date<span class="text-danger">*</span></label>
                <input type="date" name="InspectionDate" class="form-control"
                       value="{{ old('InspectionDate', $inspection->InspectionDate) }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Mileage (Km)<span class="text-danger">*</span></label>
                <input type="number" name="Mileage" class="form-control"
                       value="{{ old('Mileage', $inspection->Mileage) }}" min="0" required>
            </div>
        </div>

        {{-- Fluids --}}
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Fuel (Bars)</label>
                <select name="Fuel" class="form-select" required>
                    <option value="">-- Select Fuel Amount --</option>
                    @foreach($fuelUOMs as $fuelUOM)
                        <option value="{{ $fuelUOM->ID }}"
                            {{ old('Fuel', $inspection->Fuel) == $fuelUOM->ID ? 'selected' : '' }}>
                            {{ $fuelUOM->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Engine Oil</label>
                <select name="EngineOil" class="form-select" required>
                    <option value="">-- Select Engine Oil Amount --</option>
                    @foreach($engineOilUOMs as $engineOilUOM)
                        <option value="{{ $engineOilUOM->ID }}"
                            {{ old('EngineOil', $inspection->EngineOil) == $engineOilUOM->ID ? 'selected' : '' }}>
                            {{ $engineOilUOM->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Coolant</label>
                <select name="Coolant" class="form-select" required>
                    <option value="">-- Select Coolant Amount --</option>
                    @foreach($coolantUOMs as $coolantUOM)
                        <option value="{{ $coolantUOM->ID }}"
                            {{ old('Coolant', $inspection->Coolant) == $coolantUOM->ID ? 'selected' : '' }}>
                            {{ $coolantUOM->Description }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Safety Equipment --}}
        <div class="row mt-4">
            <div class="col-md-12">
                <label class="form-label fw-bold">Safety Equipment<span class="text-danger">*</span></label>
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

        {{-- Document Upload --}}
        <div class="mb-3 mt-3">
            <label class="form-label">Upload Supporting Document</label>
            <input type="file" name="Document" class="form-control">
            <small class="text-muted">Attach inspection sheet, photos, or related files</small>
        </div>

        {{-- Submit --}}
        <div class="mt-4">
            <button type="submit" class="btn btn-success">Update Inspection</button>
            <a href="{{ route('fleet.vehicle_inspection.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
