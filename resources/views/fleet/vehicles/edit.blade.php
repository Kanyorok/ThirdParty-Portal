@extends('layouts.app')
@section('title', 'Edit Vehicle')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">✏️ Edit Vehicle</h4>

        <form action="{{ route('fleet.vehicles.update', $vehicle->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Registration Number --}}
                <div class="col-md-4">
                    <label class="form-label">Registration Number</label>
                    <input type="text" class="form-control" name="RegistrationNo"
                           value="{{ old('RegistrationNo', $vehicle->RegistrationNo) }}" required>
                </div>

                {{-- Vehicle Type --}}
                <div class="col-md-4">
                    <label class="form-label">Vehicle Type</label>
                    <select class="form-select" name="VehicleType" required>
                        <option value="">Select Type</option>
                        @foreach ($vehicleTypes as $type)
                            <option
                                value="{{ $type->ID }}" {{ old('VehicleType', $vehicle->VehicleType) == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Fuel Type --}}
                <div class="col-md-4">
                    <label class="form-label">Fuel Type</label>
                    <select class="form-select" name="FuelType">
                        <option value="">Select Fuel</option>
                        @foreach ($fuelTypes as $fuel)
                            <option
                                value="{{ $fuel->Id }}" {{ old('FuelType', $vehicle->FuelType) == $fuel->Id ? 'selected' : '' }}>
                                {{ $fuel->FuelName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Make --}}
                <div class="col-md-4">
                    <label class="form-label">Vehicle Make</label>
                    <select class="form-control" id="make" name="Make" required>
                        <option value="">-- Select Make --</option>
                        @foreach($brands as $brand)
                            <option
                                value="{{ $brand->Id }}" {{ old('Make', $vehicle->Make) == $brand->Id ? 'selected' : '' }}>
                                {{ $brand->BrandName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Model (will be dynamically populated) --}}
                <div class="col-md-4">
                    <label class="form-label">Vehicle Model</label>
                    <select class="form-control" id="model" name="Model" required>
                        <option value="">-- Select Model --</option>
                    </select>
                </div>

                {{-- Year --}}
                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <input type="number" class="form-control" name="YearOfManufacture"
                           value="{{ old('YearOfManufacture', $vehicle->YearOfManufacture) }}">
                </div>

                {{-- Chassis No --}}
                <div class="col-md-4">
                    <label class="form-label">Chassis Number</label>
                    <input type="text" class="form-control" name="ChassisNo"
                           value="{{ old('ChassisNo', $vehicle->ChassisNo) }}">
                </div>

                {{-- Engine No --}}
                <div class="col-md-4">
                    <label class="form-label">Engine Number</label>
                    <input type="text" class="form-control" name="EngineNo"
                           value="{{ old('EngineNo', $vehicle->EngineNo) }}">
                </div>

                {{-- Capacity --}}
                <div class="col-md-4">
                    <label class="form-label">Capacity</label>
                    <input type="text" class="form-control" name="Capacity"
                           value="{{ old('Capacity', $vehicle->Capacity) }}">
                </div>

                {{-- Odometer --}}
                <div class="col-md-4">
                    <label class="form-label">Odometer Reading</label>
                    <input type="number" step="0.01" class="form-control" name="OdometerReading"
                           value="{{ old('OdometerReading', $vehicle->OdometerReading) }}">
                </div>

                {{-- Status --}}
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="Status" required>
                        <option value="">Select Type</option>
                        @foreach ($vehicleStatuses as $status)
                            <option
                                value="{{ $status->ID }}" {{ old('Status', $vehicle->Status) == $status->ID ? 'selected' : '' }}>
                                {{ $status->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Branch --}}
                <div class="col-md-4">
                    <label class="form-label">Assign to Branch</label>
                    <select class="form-select" name="AssignedBranch">
                        <option value="">-- None --</option>
                        @foreach ($branches as $branch)
                            <option
                                value="{{ $branch->Id }}" {{ old('AssignedBranch', $vehicle->AssignedBranch) == $branch->Id ? 'selected' : '' }}>
                                {{ $branch->Name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Active --}}
            <div class="col-md-4 mt-3">
                <input type="hidden" name="IsActive" value="0">
                <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input" value="1"
                    {{ old('IsActive', $vehicle->IsActive) ? 'checked' : '' }}>
                <label for="IsActive" class="form-check-label">Active</label>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary" type="submit">💾 Update Vehicle</button>
            </div>
        </form>
    </div>

    {{-- Dynamic Make → Model --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const makeSelect = document.getElementById('make');
            const modelSelect = document.getElementById('model');
            const selectedModel = "{{ old('Model', $vehicle->Model) }}";

            function loadModels(makeId, selectedModelId) {
                modelSelect.innerHTML = '<option value="">Loading...</option>';
                if (makeId) {
                    fetch(`/fleet/models-by-make/${makeId}`)
                        .then(response => response.json())
                        .then(data => {
                            modelSelect.innerHTML = '<option value="">-- Select Model --</option>';
                            data.forEach(model => {
                                const selected = model.Id == selectedModelId ? 'selected' : '';
                                modelSelect.innerHTML += `<option value="${model.Id}" ${selected}>${model.ModelName}</option>`;
                            });
                        });
                } else {
                    modelSelect.innerHTML = '<option value="">-- Select Model --</option>';
                }
            }

            makeSelect.addEventListener('change', function () {
                loadModels(this.value, null);
            });

            // Initial load for edit mode
            if (makeSelect.value) {
                loadModels(makeSelect.value, selectedModel);
            }
        });
    </script>
@endsection
