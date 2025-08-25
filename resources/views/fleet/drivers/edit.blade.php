@extends('layouts.app')
@section('title', 'Edit Driver')

@section('content')
<div class="card p-4 shadow rounded-4">
    <h4 class="mb-4">Edit Driver</h4>

    <form action="{{ route('fleet.drivers.update', $driver->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">

            {{-- Full Name --}}
            <div class="col-md-3">
                <label for="FullName" class="form-label">Full Name</label>
                <input type="text" name="FullName" class="form-control"
                       value="{{ old('FullName', $driver->FullName) }}" required>
            </div>

            {{-- Staff Number --}}
            <div class="col-md-3">
                <label for="StaffNumber" class="form-label">Staff Member</label>
                <select name="StaffNumber" id="StaffNumber" class="form-select" required>
                    <option value="">-- Select Staff Member --</option>
                    @foreach ($staffNo as $employee)
                        <option value="{{ $employee->Id }}"
                            {{ old('StaffNumber', $driver->StaffNumber) == $employee->Id ? 'selected' : '' }}>
                            {{ $employee->FullName }} ({{ $employee->EmployeeID }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- National ID --}}
            <div class="col-md-3">
                <label for="NationalID" class="form-label">National ID</label>
                <input type="text" name="NationalID" class="form-control"
                       value="{{ old('NationalID', $driver->NationalID) }}">
            </div>

            {{-- Phone --}}
            <div class="col-md-3">
                <label for="Phone" class="form-label">Phone Number</label>
                <input type="text" name="Phone" class="form-control"
                       value="{{ old('Phone', $driver->Phone) }}">
            </div>


            {{-- Employment Type --}}
            <div class="col-md-3">
                <label for="EmploymentType" class="form-label">Employment Type</label>
                <select name="EmploymentType" class="form-select">
                    <option value="">Select Type</option>
                    @foreach($employmentType as $type)
                        <option value="{{ $type->ID }}"
                            {{ old('EmploymentType', $driver->EmploymentType) == $type->ID ? 'selected' : '' }}>
                            {{ $type->Description }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Notes --}}
            <div class="col-md-12">
                <label for="Notes" class="form-label">Notes</label>
                <textarea name="Notes" class="form-control" rows="2">{{ old('Notes', $driver->Notes) }}</textarea>
            </div>

            {{-- Is Active --}}
            <div class="col-md-4 mt-3">
                <input type="hidden" name="IsActive" value="0">
                <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input" value="1"
                       {{ old('IsActive', $driver->IsActive) ? 'checked' : '' }}>
                <label for="IsActive" class="form-check-label">Active</label>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary" type="submit">Update Driver</button>
            <a href="{{ route('fleet.drivers.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
