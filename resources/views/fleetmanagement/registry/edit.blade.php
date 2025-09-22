@extends('layouts.app')
@section('title', 'Edit Vehicle')
@section('content')

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Edit Vehicle</h4>
            <a href="{{ route('vehicle-registry.index') }}" class="btn btn-secondary">Back to Vehicle List</a>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Update Vehicle Details</h5>
                <form action="{{ route('vehicle-registry.update', $vehicle->Id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="registrationno">Registration Number</label>
                            <input type="text"
                                   class="form-control @error('RegistrationNo') is-invalid @enderror"
                                   id="registrationno"
                                   name="RegistrationNo"
                                   value="{{ old('RegistrationNo', $vehicle->RegistrationNo) }}">
                            @error('RegistrationNo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label for="make">Vehicle Make</label>
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

                        <div class="form-group col-md-3">
                            <label for="model">Vehicle Model</label>
                            <select class="form-control" id="model" name="Model" required>
                                <option value="">-- Select Model --</option>
                                @foreach($fleetModel as $model)
                                    <option
                                        value="{{ $model->Id }}" {{ old('Model', $vehicle->Model) == $model->Id ? 'selected' : '' }}>
                                        {{ $model->ModelName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label for="type">Vehicle Type</label>
                            <select class="form-control" id="type" name="Type" required>
                                <option value="">-- Select Type --</option>
                                @foreach($vehicleTypes as $type)
                                    <option
                                        value="{{ $type->ID }}" {{ old('Type', $vehicle->Type) == $type->ID ? 'selected' : '' }}>
                                        {{ $type->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="color">Color</label>
                            <input type="text" class="form-control" id="color" name="Color"
                                   value="{{ old('Color', $vehicle->Color) }}">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="year">Year</label>
                            <input type="text" class="form-control" id="year" name="Year"
                                   value="{{ old('Year', $vehicle->Year) }}"
                                   pattern="\d{4}" placeholder="YYYY">
                        </div>

                        <div class="form-group col-md-3">
                            <label for="chassisno">Chassis Number</label>
                            <input type="text"
                                   class="form-control @error('ChassisNo') is-invalid @enderror"
                                   id="chassisno"
                                   name="ChassisNo"
                                   value="{{ old('ChassisNo', $vehicle->ChassisNo) }}">
                            @error('ChassisNo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label for="engineno">Engine Number</label>
                            <input type="text" class="form-control" id="engineno" name="EngineNo"
                                   value="{{ old('EngineNo', $vehicle->EngineNo) }}">
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">Update Vehicle</button>
                        <a href="{{ route('vehicle-registry.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
