@extends('layouts.app')
@section('title', 'Edit Product Rate')
@section('content')
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">✏️ Edit Product Rate</div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>There were some errors with your submission:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('yieldexpenserate.update', $driverRate->Id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="mb-4 col-md-6">
                        <label class="form-label">Product</label>
                        <select class="form-select" name="ProductTypeID" required>
                            <option value="">-- Select Product --</option>
                            @foreach($productTypes as $product)
                                <option
                                    value="{{ $product->Id }}" {{ $product->Id == old('ProductTypeID', $driverRate->ProductTypeID) ? 'selected' : '' }}>{{ $product->Name }}</option>
                            @endforeach
                        </select>
                        @error('ProductTypeID')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4 col-md-6">
                        <label class="form-label">Rate Type</label>
                        <select class="form-select" name="RateTypeID" required>
                            <option value="">-- Select Rate Type --</option>
                            @foreach($rateTypes as $rate)
                                <option
                                    value="{{ $rate->Id }}" {{ $rate->Id == old('RateTypeID', $driverRate->RateTypeID) ? 'selected' : '' }}>{{ $rate->RateTypeName }}</option>
                            @endforeach
                        </select>
                        @error('RateTypeID')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4 col-md-6">
                        <label class="form-label">Rate Value (%)</label>
                        <input type="number" step="0.01" class="form-control" name="RateValue"
                               value="{{ old('RateValue', $driverRate->RateValue) }}" placeholder="e.g. 10.5" required>
                        @error('RateValue')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4 col-md-6">
                        <label class="form-label">Source</label>
                        <input type="text" class="form-control" name="Source"
                               value="{{ old('Source', $driverRate->Source) }}" placeholder="e.g. CBS, Manual" required>
                        @error('Source')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('yieldexpenserate.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"
                            onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit(); }">
                        💾 Update Driver Rate
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
