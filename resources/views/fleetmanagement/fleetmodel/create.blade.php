@extends('layouts.app')
@section('title', 'Fleet Model Management')
@section('content')

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Model Management</h4>
            <a href="{{ route('fleetmodel.index') }}" class="btn btn-secondary">Back to Model List</a>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Add New Model</h5>
                <form action="{{ route('fleetmodel.store') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <input type="text" class="form-control @error('ModelName') is-invalid @enderror"
                                   name="ModelName" value="{{ old('ModelName') }}" required>
                            @error('ModelName')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="form-group col-md-3">
                            <label for="status">Brand Name</label>
                            <select class="form-control" id="brandid" name="BrandID" required>
                                <option value="">-- Select Brand --</option>
                                @foreach($brands as $brand)
                                    <option
                                        value="{{ $brand->Id }}" {{ old('BrandID') == $brand->Id ? 'selected' : '' }}>
                                        {{ $brand->BrandName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-success">➕ Save Model</button>
                        <button type="reset" class="btn btn-secondary ml-2">Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
