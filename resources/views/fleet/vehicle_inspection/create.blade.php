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

            {{-- Post-Trip Inspection --}}
            @if(isset($parentInspection))
                <input type="hidden" name="VehicleID" value="{{ $parentInspection->VehicleID }}">
                <input type="hidden" name="FuelType" value="{{ $parentInspection->FuelType }}">
                <input type="hidden" name="InspectionTypeID" value="{{ $parentInspection->InspectionTypeID }}">
                <input type="hidden" name="ParentInspectionID" value="{{ $parentInspection->Id }}">

                <div class="alert alert-info">
                    Creating a <strong>Post-Trip</strong> inspection for:
                    <ul class="mb-0">
                        <li><strong>Pre-Trip ID:</strong> {{ $parentInspection->InspectionID }}</li>
                        <li><strong>Vehicle:</strong> {{ $parentInspection->vehicle?->RegistrationNo }}</li>
                        {{-- <li><strong>Fuel Type:</strong> {{ $parentInspection->fuel?->FuelName }}</li> --}}
                        <li><strong>Inspection Type:</strong> {{ $parentInspection->inspectionType?->Description }}</li>
                    </ul>
                </div>
            @endif

            {{-- Pre-Trip Inspection --}}
            @unless(isset($parentInspection))
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Inspection Type<span class="text-danger">*</span></label>
                        <select name="InspectionTypeID" class="form-select" required>
                            <option value="">-- Select Inspection Type --</option>
                            @foreach($inspectionTypes as $type)
                                <option
                                    value="{{ $type->ID }}" {{ old('InspectionTypeID') == $type->ID ? 'selected' : '' }}>
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
                                        @if(isset($assignment) && (string)$assignment->VehicleID === (string)$vehicle->Id)
                                            selected
                                        @elseif(old('VehicleID') == $vehicle->Id)
                                            selected
                                    @endif>
                                    {{ $vehicle->RegistrationNo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fuel Type</label>
                        <input type="hidden" name="FuelType" id="FuelTypeID">
                        <input type="text" id="FuelTypeName" class="form-control" readonly>
                    </div>
                </div>
            @endunless

            {{-- Driver Section (Always shown) --}}
            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label">Driver<span class="text-danger">*</span></label>
                    
                    @if(isset($parentInspection))
                        {{-- Post-Trip: Show driver from parent inspection --}}
                        @php
                            // Get driver name from parent inspection
                            $driverName = 'No driver assigned';
                            $driverType = 'No driver assigned';
                            
                            if ($parentInspection->DriverID) {
                                $driverName = $parentInspection->driver->FullName ?? 'Driver not found';
                                $driverType = 'Fleet Driver';
                            } elseif ($parentInspection->ContractedDriverID) {
                                $driverName = $parentInspection->contractedDriver->FullName ?? 'Contracted driver not found';
                                $driverType = 'Contracted Driver';
                            }
                        @endphp
                        
                        <input type="text" class="form-control" value="{{ $driverName }}" readonly>
                        <small class="text-muted">{{ $driverType }} (from Pre-Trip Inspection)</small>
                        
                        {{-- Hidden fields to preserve driver assignment --}}
                        <input type="hidden" name="DriverID" value="{{ $parentInspection->DriverID }}">
                        <input type="hidden" name="ContractedDriverID" value="{{ $parentInspection->ContractedDriverID }}">
                        
                    @else
                        {{-- Pre-Trip: Dynamic driver selection --}}
                        <input type="hidden" name="DriverID" id="DriverID">
                        <input type="hidden" name="ContractedDriverID" id="ContractedDriverID">
                        <input type="text" id="DriverName" class="form-control" readonly>
                        <small class="text-muted" id="DriverTypeText"></small>
                    @endif
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Inspection Date<span class="text-danger">*</span></label>
                    <input type="date" name="InspectionDate" class="form-control" value="{{ old('InspectionDate', date('Y-m-d')) }}"
                           required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mileage (Km)<span class="text-danger">*</span></label>
                    <input type="number" name="Mileage" class="form-control" value="{{ old('Mileage') }}" min="0" required>
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
                                {{ old('Fuel') == $fuelUOM->ID ? 'selected' : '' }}>
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
                                {{ old('EngineOil') == $engineOilUOM->ID ? 'selected' : '' }}>
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
                                {{ old('Coolant') == $coolantUOM->ID ? 'selected' : '' }}>
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
                                <input class="form-check-input" type="checkbox" name="{{ $field }}"
                                       value="1" {{ old($field) ? 'checked' : '' }}>
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
                <small class="text-muted">Attach inspection sheet, photos, or related files (Max: 2MB)</small>
            </div>

            {{-- Submit --}}
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save Inspection</button>
                <a href="{{ route('fleet.vehicle_inspection.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const vehicleSelect = document.querySelector('[name="VehicleID"]');
    const driverIdInput = document.getElementById('DriverID');
    const contractedDriverIdInput = document.getElementById('ContractedDriverID');
    const driverNameInput = document.getElementById('DriverName');
    const driverTypeText = document.getElementById('DriverTypeText');
    const fuelTypeInput = document.getElementById('FuelTypeID');
    const fuelNameInput = document.getElementById('FuelTypeName');
    const mileageInput = document.querySelector('[name="Mileage"]');

    let lastMileage = 0;

    function fetchVehicleDetails(Id) {
        if (!Id) {
            // Clear all fields if no vehicle selected
            if (driverIdInput) driverIdInput.value = '';
            if (contractedDriverIdInput) contractedDriverIdInput.value = '';
            if (driverNameInput) driverNameInput.value = '';
            if (driverTypeText) driverTypeText.textContent = '';
            if (fuelTypeInput) fuelTypeInput.value = '';
            if (fuelNameInput) fuelNameInput.value = '';
            if (mileageInput) mileageInput.value = '';
            return;
        }

        fetch(`/fleet/vehicle_inspection/get-driver/${Id}`)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                // Clear both driver fields first
                if (driverIdInput) driverIdInput.value = '';
                if (contractedDriverIdInput) contractedDriverIdInput.value = '';
                if (driverTypeText) driverTypeText.textContent = '';
                
                // Set the appropriate driver field based on driverType
                if (data.driverType === 'FleetDriver') {
                    if (driverIdInput) driverIdInput.value = data.driverId ?? '';
                    if (driverTypeText) driverTypeText.textContent = 'Fleet Driver';
                } else if (data.driverType === 'ContractedDriver') {
                    if (contractedDriverIdInput) contractedDriverIdInput.value = data.driverId ?? '';
                    if (driverTypeText) driverTypeText.textContent = 'Contracted Driver';
                } else {
                    if (driverTypeText) driverTypeText.textContent = 'No driver assigned';
                }
                
                if (driverNameInput) driverNameInput.value = data.driverName ?? 'No driver assigned';

                if (data.fuelTypeId && fuelTypeInput) {
                    fuelTypeInput.value = data.fuelTypeId;
                    if (fuelNameInput) fuelNameInput.value = data.fuelTypeName ?? '';
                } else {
                    if (fuelTypeInput) fuelTypeInput.value = '';
                    if (fuelNameInput) fuelNameInput.value = '';
                }

                // Fetch last mileage
                fetch(`/fleet/vehicle_inspection/get-last-mileage/${Id}`)
                    .then(res => res.json())
                    .then(mileageData => {
                        lastMileage = mileageData.lastMileage ?? 0;
                        if (mileageInput) mileageInput.placeholder = `Last recorded: ${lastMileage} km`;
                    });
            })
            .catch(err => {
                console.error('Error fetching vehicle details:', err);
                if (driverNameInput) driverNameInput.value = 'Error loading driver information';
                if (driverTypeText) driverTypeText.textContent = '';
            });
    }

    // Prevent entering mileage lower than last
    if (mileageInput) {
        mileageInput.addEventListener('input', function () {
            const entered = parseInt(this.value || 0);
            if (entered < lastMileage) {
                alert(`Mileage cannot be less than the last recorded value (${lastMileage} km).`);
                this.value = lastMileage;
            }
        });
    }

    if (vehicleSelect) {
        vehicleSelect.addEventListener('change', function () {
            fetchVehicleDetails(this.value);
        });

        // Initialize if vehicle is already selected (e.g., form validation failed)
        if (vehicleSelect.value) {
            fetchVehicleDetails(vehicleSelect.value);
        }
    }
});
</script>
@endsection