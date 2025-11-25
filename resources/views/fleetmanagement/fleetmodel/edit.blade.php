@extends('layouts.app')
@section('title', 'Fleet Model Management')
@section('content')
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Edit Fleet Model</h4>
            <a href="{{ route('fleetmodel.index') }}" class="btn btn-secondary">Back to Fleet Model List</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('fleetmodel.update', $fleetModel->Id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="modelname">Fleet Model Name</label>
                            <input type="text" class="form-control" name="ModelName"
                                   value="{{ old('ModelName', $fleetModel->ModelName) }}" required>
                        </div>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="status">Brand Name</label>
                        <select class="form-control" id="brandid" name="BrandID" required>
                            <option value="">-- Select Brand --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->Id }}"
                                    {{ old('BrandID', $fleetModel->BrandID) == $brand->Id ? 'selected' : '' }}>
                                    {{ $brand->BrandName }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="form-group mt-3">
                        <button type="submit" class="btn btn-primary">💾 Update Fleet Model</button>
                        <a href="{{ route('fleetmodel.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
