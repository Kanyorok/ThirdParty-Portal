@extends('layouts.app')
@section('title', 'Add Driver ')
@section('content')

<div class="container mt-5">
    <h2 class="mb-3">Fleet Management Module</h2>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Driver Management</h4>
        <a href="{{ route('drivermanagement.index') }}" class="btn btn-secondary">Back to Driver List</a>
    </div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Add New Driver</h5>
            <form action="{{ route('drivermanagement.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="full_name">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="DriverName"
                               value="{{ old('DriverName') }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="license_number">License Number</label>
                        <input type="text" class="form-control @error('LicenseNumber') is-invalid @enderror"
                               id="license_number" name="LicenseNumber"
                               value="{{ old('LicenseNumber') }}" required>

                        @error('LicenseNumber')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label for="expiry_date">License Expiry</label>
                        <input type="date" class="form-control" id="expiry_date" name="LicenseExpiryDate"
                               value="{{ old('LicenseExpiryDate') }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group  col-md-3">
                        <label for="contact">Phone</label>
                        <input type="text" class="form-control" id="contact" name="Phone" value="{{ old('Phone') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="Email" value="{{ old('Email') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="status">Employment Status</label>
                        <select class="form-control" id="employment_status" name="EmploymentStatus" required>
                            <option value="">-- Select Status --</option>
                            @foreach($employmentStatus as $status)
                                <option
                                    value="{{ $status->ID }}" {{ old('EmploymentStatus') == $status->ID ? 'selected' : '' }}>
                                    {{ $status->Description }}
                                </option>
                            @endforeach
                        </select>

                    </div>

                    <div class="form-group col-md-3">
                        <label for="remarks">Remarks</label>
                        <input type="text" class="form-control" id="remarks" name="Remarks"
                               value="{{ old('Remarks') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">➕ Save Driver</button>
                <button type="reset" class="btn btn-secondary ml-2">Clear</button>
            </form>
        </div>
    </div>
</div>
@endsection
