@extends('layouts.app')
@section('title', 'Fleet Model Management')
@section('content')

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Vehicle Registry Management</h4>
        <a href="{{ route('vehicle-registry.index') }}" class="btn btn-secondary">Back to Vehicle List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Add New Vehicle</h5>
            <form action="{{ route('vehicle-registry.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="form-group col-md-3">
                        <label for="registrationno">Registration Number</label>
                        <input type="text" 
                            class="form-control @error('RegistrationNo') is-invalid @enderror" 
                            id="registrationno" 
                            name="RegistrationNo" 
                            value="{{ old('RegistrationNo') }}">
                        @error('RegistrationNo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="make">Vehicle Make</label>
                        <select class="form-control" id="make" name="Make" required>
                            <option value="">-- Select Make --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->Id }}" {{ old('Make') == $brand->Id ? 'selected' : '' }}>
                                    {{ $brand->BrandName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="model">Vehicle Model</label>
                        <select class="form-control" id="model" name="Model" required>
                            <option value="">-- Select Model --</option>
                            @foreach($fleetModels as $model)
                                <option value="{{ $model->Id }}" {{ old('Model') == $model->Id ? 'selected' : '' }}>
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
                                <option value="{{ $type->ID }}" {{ old('Type') == $type->ID ? 'selected' : '' }}>
                                    {{ $type->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-3">
                        <label for="color">Color</label>
                        <input type="text" class="form-control" id="color" name="Color" value="{{ old('Color') }}">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="year">Year</label>
                        <input type="text" class="form-control" id="year" name="Year"
                               value="{{ old('Year', $fleetModel->Year ?? '') }}"
                               pattern="\d{4}" placeholder="YYYY">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="chassisno">Chassis Number</label>
                        <input type="text" 
                            class="form-control @error('ChassisNo') is-invalid @enderror" 
                            id="chassisno" 
                            name="ChassisNo" 
                            value="{{ old('ChassisNo') }}">
                        @error('ChassisNo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="engineno">Engine Number</label>
                        <input type="text" class="form-control" id="engineno" name="EngineNo" value="{{ old('EngineNo') }}">
                    </div>
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-success">➕ Save Model</button>
                    <button type="reset" class="btn btn-secondary ml-2">Clear</button>
                </div>
            </form>
        </div>
    </div>          
</div>
@endsection
