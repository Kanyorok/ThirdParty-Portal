@extends('layouts.app')
@section('title', 'Fleet Make/Brand Details')
@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title">{{ $fleetMake->BrandName }} Details</h4>
            <a href="{{ route('fleetmake.index') }}" class="btn btn-primary">Back to Fleet Make/Brand List</a>
        </div>

        <div class="card">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-4"><strong>Brand ID:</strong> {{ $fleetMake->BrandID }}</div>
                    <div class="col-md-4"><strong>Brand Name:</strong> {{ $fleetMake->BrandName }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
