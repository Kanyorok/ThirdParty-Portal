@extends('layouts.app')
@section('title', 'Register Vehicle')

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
    <h4 class="mb-4">🚗 Register New Vehicle</h4>

    <form action="{{ route('fleet.vehicles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf


        @php
        $activeStatus = $vehicleStatuses->firstWhere('Description', 'Active');
        @endphp

        @if($activeStatus)
        <input type="hidden" name="Status" value="{{ $activeStatus->ID }}">
        @endif

        <div class="row g-3">
            {{-- Registration Number --}}
            <div class="col-md-4">
                <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="RegistrationNo" required
                    value="{{ old('RegistrationNo') }}" required maxlength="7">
            </div>

            {{-- Vehicle Type --}}
            <div class="col-md-4">
                <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                <select class="form-select" name="VehicleType" required>
                    <option value="">Select Type</option>
                    @foreach ($vehicleTypes as $type)
                    <option value="{{ $type->ID }}" {{ old('VehicleType') == $type->ID ? 'selected' : '' }}>
                        {{ $type->Description }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Fuel Type --}}
            <div class="col-md-4">
                <label class="form-label">Fuel Type<span class="text-danger">*</span></label>
                <select class="form-select" name="FuelType">
                    <option value="">Select Fuel</option>
                    @foreach ($fuelTypes as $fuel)
                    <option value="{{ $fuel->Id }}" {{ old('FuelType') == $fuel->Id ? 'selected' : '' }}>
                        {{ $fuel->FuelName }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Make --}}
            <div class="col-md-4">
                <label class="form-label">Vehicle Make <span class="text-danger">*</span></label>
                <select class="form-control" id="make" name="Make" required>
                    <option value="">-- Select Make --</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->Id }}" {{ old('Make') == $brand->Id ? 'selected' : '' }}>
                        {{ $brand->BrandName }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Model --}}
            <div class="col-md-4">
                <label class="form-label">Vehicle Model <span class="text-danger">*</span></label>
                <select class="form-control" id="model" name="Model" required>
                    <option value="">{{ old('Model') ? old('Model') : '-- Select Model --' }}</option>
                </select>
            </div>

            {{-- Year --}}
            <div class="col-md-4">
                <label class="form-label">Year of Manufacture <span class="text-danger">*</span></label>
                <select class="form-select" name="YearOfManufacture" required>
                    <option value="">Select Year</option>
                    @for ($year = now()->year; $year >= 1980; $year--)
                    <option value="{{ $year }}" {{ old('YearOfManufacture') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                    @endfor
                </select>
            </div>

            {{-- Color --}}
            <div class="col-md-4">
                <label class="form-label">Vehicle Color<span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="Color" value="{{ old('Color') }}">
            </div>

            {{-- Chassis No --}}
            <div class="col-md-4">
                <label class="form-label">Chassis Number<span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control"
                    name="ChassisNo"
                    value="{{ old('ChassisNo') }}"
                    minlength="17"
                    maxlength="17"
                    pattern=".{17}"
                    title="Chassis Number must be exactly 17 characters">
            </div>

            {{-- Engine No --}}
            <div class="col-md-4">
                <label class="form-label">Engine Number<span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="EngineNo" value="{{ old('EngineNo') }}"
                    minlength="11" maxlength="17"
                    title="Engine Number must be 11 to 17 characters">
            </div>

            {{-- Capacity --}}
            <div class="col-md-4">
                <label class="form-label">Vehicle Engine (CC)<span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="Capacity" value="{{ old('Capacity') }}"
                    placeholder="e.g., 1500" min="0" max="9999" maxlength="4">
            </div>

            {{-- Odometer --}}
            <div class="col-md-4">
                <label class="form-label">Odometer Reading<span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="OdometerReading"
                    value="{{ old('OdometerReading') }}"
                    min="0" max="999999" maxlength="6" placeholder="e.g., 012345">
            </div>


            {{-- Max Load --}}
            <div class="col-md-4">
                <label class="form-label">Maximum Load (Kg)<span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" name="MaxLoad"
                    value="{{ old('MaxLoad') }}" min="0">
            </div>

            {{-- Max Passengers --}}
            <div class="col-md-4">
                <label class="form-label">Maximum Passengers<span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="MaxPassengers"
                    value="{{ old('MaxPassengers') }}" min="0">
            </div>

            {{-- Branch --}}
            <div class="col-md-4">
                <label class="form-label">Assign to Branch<span class="text-danger">*</span></label>
                <select class="form-select" name="AssignedBranch">
                    <option value="">-- None --</option>
                    @foreach ($branches as $branch)
                    <option value="{{ $branch->Id }}" {{ old('AssignedBranch') == $branch->Id ? 'selected' : '' }}>
                        {{ $branch->Name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Image Upload --}}
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label class="form-label">Upload Vehicle Image<span class="text-danger">*</span></label>
                <input type="file" class="form-control" name="ImageFile" accept="image/*">
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-success" type="submit">✅ Register Vehicle</button>
        </div>
    </form>
</div>

{{-- Dynamic Make → Model --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const makeSelect = document.getElementById('make');
        const modelSelect = document.getElementById('model');
        const selectedModel = "{{ old('Model') }}";

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

        makeSelect.addEventListener('change', function() {
            loadModels(this.value, null);
        });

        // Load models if form reloads with validation errors
        if (makeSelect.value) {
            loadModels(makeSelect.value, selectedModel);
        }
    });
</script>
@endsection