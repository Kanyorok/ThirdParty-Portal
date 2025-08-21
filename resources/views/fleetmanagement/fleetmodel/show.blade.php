@extends('layouts.app')
@section('title', 'Fleet Model Details')
@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="card-title">{{ $fleetModel->ModelName }} Details</h4>
        <a href="{{ route('fleetmodel.index') }}" class="btn btn-secondary">Back to Fleet Model List</a>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="row">
                <div class="col-md-4"><strong>Model ID:</strong> {{ $fleetModel->ModelID }}</div>
                <div class="col-md-4"><strong>Model Name:</strong> {{ $fleetModel->ModelName }}</div>
                <div class="col-md-4"><strong>Brand Name:</strong> {{ $fleetModel->brand->BrandName ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
