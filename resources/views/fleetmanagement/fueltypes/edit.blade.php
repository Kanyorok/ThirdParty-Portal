@extends('layouts.app')

@section('title', 'Edit Fuel Type')

@section('content')
    <div class="container mt-4">
        <h4 class="fw-bold mb-3">Edit Fuel Type</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('fueltypes.update', $fuelType->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="FuelName" class="form-label">Fuel Name</label>
                <input type="text" name="FuelName" id="FuelName" class="form-control"
                       value="{{ old('FuelName', $fuelType->FuelName) }}" required>
            </div>

            <div class="mb-3">
                <label for="Description" class="form-label">Description</label>
                <textarea name="Description" id="Description"
                          class="form-control">{{ old('Description', $fuelType->Description) }}</textarea>
            </div>

            <div class="form-check mb-3">
                <input type="hidden" name="IsActive" value="0">
                <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input" value="1"
                    {{ old('IsActive', $fuelType->IsActive ?? 0) ? 'checked' : '' }}>
                <label for="IsActive" class="form-check-label">Active</label>
            </div>


            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('fueltypes.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
