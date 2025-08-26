@extends('layouts.app')
@section('title', 'Edit Contracted Driver')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <h4 class="mb-4">📝 Edit Contracted Driver</h4>

        <form action="{{ route('fleet.contracted_drivers.update', $driver->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="FullName" class="form-control @error('FullName') is-invalid @enderror"
                           value="{{ old('FullName', $driver->FullName) }}" placeholder="Enter full name" required>
                    @error('FullName')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">National ID</label>
                    <input type="text" name="NationalID" class="form-control @error('NationalID') is-invalid @enderror"
                           value="{{ old('NationalID', $driver->NationalID) }}" placeholder="Enter National ID"
                           required>
                    @error('NationalID')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="Phone" class="form-control @error('Phone') is-invalid @enderror"
                           value="{{ old('Phone', $driver->Phone) }}" placeholder="Enter phone number">
                    @error('Phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            
            <div class="col-md-6">
                <label for="Company" class="form-label">Company Name</label>
                <select name="Company" class="form-select">
                    <option value="">Select Company</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->Id }}" {{ old('Company') == $company->Id ? 'selected' : '' }}>
                            {{ $company->SupplierName }}
                        </option>
                    @endforeach
                </select>
            </div>

                <!-- New Contract Start Date -->
                <div class="col-md-6">
                    <label class="form-label">Contract Start Date</label>
                    <input type="date" name="ContractStartDate"
                           class="form-control @error('ContractStartDate') is-invalid @enderror"
                           value="{{ old('ContractStartDate', $driver->ContractStartDate ? \Carbon\Carbon::parse($driver->ContractStartDate)->format('Y-m-d') : '') }}">
                    @error('ContractStartDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- New Contract End Date -->
                <div class="col-md-6">
                    <label class="form-label">Contract End Date</label>
                    <input type="date" name="ContractEndDate"
                           class="form-control @error('ContractEndDate') is-invalid @enderror"
                           value="{{ old('ContractEndDate', $driver->ContractEndDate ? \Carbon\Carbon::parse($driver->ContractEndDate)->format('Y-m-d') : '') }}">
                    @error('ContractEndDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="Remarks" class="form-control @error('Remarks') is-invalid @enderror"
                           value="{{ old('Remarks', $driver->Remarks) }}" placeholder="Any remarks">
                    @error('Remarks')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
                        <div class="col-md-12">
                        <div class="form-check">
                            <input type="hidden" name="IsActive" value="0">
                            <input type="checkbox" name="IsActive" value="1" class="form-check-input" id="IsActive"
                                {{ old('IsActive', $driver->IsActive) ? 'checked' : '' }}>
                            <label class="form-check-label" for="IsActive">Active</label>
                        </div>
                        @error('IsActive')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">✅ Update</button>
            </div>
        </form>
    </div>
@endsection
