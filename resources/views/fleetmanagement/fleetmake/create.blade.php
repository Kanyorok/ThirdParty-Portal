@extends('layouts.app')
@section('title', 'Add Fleet Make/Brand')
@section('content')


<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Fleet Make Management</h4>
        <a href="{{ route('fleetmake.index') }}" class="btn btn-secondary">Back to Fleet Make List</a>
    </div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Add New Fleet Make/Brand</h5>
            <form action="{{ route('fleetmake.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-3">
                    <label for="brandname">Brand Name</label>
                    <input type="text" class="form-control @error('BrandName') is-invalid @enderror"
                        id="brandname" name="BrandName"
                        value="{{ old('BrandName') }}" required>

                    @error('BrandName')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                </div>
                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-success">Save Fleet Brand</button>
                    <button type="reset" class="btn btn-secondary ml-2">Clear</button>
                </div>

            </form>
        </div>
    </div>          
</div>
@endsection