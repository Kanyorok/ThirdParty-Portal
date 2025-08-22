@extends('layouts.app')

@section('title', 'Fuel Type Details')

@section('content')
    <div class="container mt-4">

        <div class="card">
            <div class="card-body">
                <p><strong>Fuel Name:</strong> {{ $fuelType->FuelName }}</p>
                <p><strong>Description:</strong> {{ $fuelType->Description ?? '-' }}</p>
                <p><strong>Status:</strong>
                    @if($fuelType->IsActive)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </p>
            </div>
        </div>

        <a href="{{ route('fueltypes.index') }}" class="btn btn-secondary mt-3">Back</a>
    </div>
@endsection
