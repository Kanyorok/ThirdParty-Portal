@extends('layouts.app')
@section('title', 'Edit Fleet Make/Brand')
@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Edit Fleet Make/Brand</h4>
            <a href="{{ route('fleetmake.index') }}" class="btn btn-secondary">Back to Fleet Make List</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('fleetmake.update', $fleetMake->Id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="brandname">Fleet Make/Brand Name</label>
                            <input type="text" class="form-control" name="BrandName"
                                   value="{{ old('BrandName', $fleetMake->BrandName) }}" required>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">💾 Update Fleet Make</button>
                        <a href="{{ route('fleetmake.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
