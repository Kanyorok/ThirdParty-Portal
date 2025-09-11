@extends('layouts.app')
@section('title', 'Edit Driver')
@section('content')
<div class="container mt-5">
    <h2 class="mb-3">Fleet Management Module</h2>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Edit Driver</h4>
        <a href="{{ route('drivermanagement.index') }}" class="btn btn-secondary">Back to Driver List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('drivermanagement.update', $driver->Id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="full_name">Full Name</label>
                        <input type="text" class="form-control" name="DriverName" value="{{ old('DriverName', $driver->DriverName) }}" required>
                    </div>

                       <div class="form-group col-md-3">
                            <label for="license_number">License Number</label>
                            <input type="text" class="form-control @error('LicenseNumber') is-invalid @enderror"
                                id="license_number" name="LicenseNumber"
                                value="{{ old('LicenseNumber', $driver->LicenseNumber) }}" required>

                            @error('LicenseNumber')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>


                    <div class="form-group col-md-3">
                        <label for="expiry_date">License Expiry</label>
                        <input type="date" class="form-control" name="LicenseExpiryDate" value="{{ old('LicenseExpiryDate', $driver->LicenseExpiryDate) }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="Phone">Phone</label>
                        <input type="text" class="form-control" name="Phone" value="{{ old('Phone', $driver->Phone) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Email">Email</label>
                        <input type="email" class="form-control" name="Email" value="{{ old('Email', $driver->Email) }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="employment_status">Employment Status</label>
                        <select class="form-control" name="EmploymentStatus" required>
                            <option value="">-- Select Status --</option>
                            @foreach($employmentStatus as $status)
                                <option value="{{ $status->ID }}" {{ old('EmploymentStatus', $driver->EmploymentStatus) == $status->ID ? 'selected' : '' }}>
                                    {{ $status->Description }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="Remarks">Remarks</label>
                        <input type="text" class="form-control" name="Remarks" value="{{ old('Remarks', $driver->Remarks) }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">💾 Update Driver</button>
                <a href="{{ route('drivermanagement.index') }}" class="btn btn-secondary ml-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
