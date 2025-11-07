@extends('layouts.app')
@section('title', 'Add License – Contracted Driver')

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
        <h4 class="mb-4">➕ Add License – {{ $driver->FullName }}</h4>

        <form method="POST" action="{{ route('fleet.contracted_driver_licenses.store', $driver->Id) }}">
            @csrf

            <!-- Hidden field for driver ID -->
            <input type="hidden" name="ContractedDriverID" value="{{ $driver->Id }}">

            <div class="row g-3">
                {{-- License Number --}}
                <div class="col-md-6">
                    <label class="form-label">License Number</label>
                    <input
                        type="text"
                        name="LicenseNumber"
                        value="{{ old('LicenseNumber') }}"
                        class="form-control @error('LicenseNumber') is-invalid @enderror"
                        required>
                    @error('LicenseNumber')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Category --}}
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <input
                        type="text"
                        name="LicenseCategory"
                        value="{{ old('LicenseCategory') }}"
                        class="form-control @error('LicenseCategory') is-invalid @enderror"
                        required>
                    @error('LicenseCategory')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Issue Date --}}
                <div class="col-md-4">
                    <label class="form-label">Issue Date</label>
                    <input
                        type="date"
                        name="IssueDate"
                        value="{{ old('IssueDate') }}"
                        class="form-control @error('IssueDate') is-invalid @enderror"
                        required>
                    @error('IssueDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Expiry Date --}}
                <div class="col-md-4">
                    <label class="form-label">Expiry Date</label>
                    <input
                        type="date"
                        name="ExpiryDate"
                        value="{{ old('ExpiryDate') }}"
                        class="form-control @error('ExpiryDate') is-invalid @enderror"
                        required>
                    @error('ExpiryDate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Notes --}}
                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea
                        name="Notes"
                        class="form-control @error('Notes') is-invalid @enderror"
                        rows="2">{{ old('Notes') }}</textarea>
                    @error('Notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Submit --}}
            <div class="mt-4">
                <button type="submit" class="btn btn-success">💾 Save License</button>
                <a href="{{ route('fleet.contracted_drivers.show', $driver->Id) }}" class="btn btn-secondary">↩ Back</a>
            </div>
        </form>
    </div>
@endsection
