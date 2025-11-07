@extends('layouts.app')
@section('title', 'Edit Product Rate')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <!-- Header -->
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-edit me-2"></i>
                </h5>
                <a href="{{ route('yieldexpenserate.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <!-- Body -->
            <div class="card-body p-4">
                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>There were some errors with your submission:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('yieldexpenserate.update', $driverRate->Id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Product -->
                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select class="form-select @error('ProductTypeID') is-invalid @enderror"
                                    name="ProductTypeID" required>
                                <option value="">-- Select Product --</option>
                                @foreach($productTypes as $product)
                                    <option value="{{ $product->Id }}"
                                        {{ $product->Id == old('ProductTypeID', $driverRate->ProductTypeID) ? 'selected' : '' }}>
                                        {{ $product->Name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ProductTypeID')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Rate Type -->
                        <div class="col-md-6">
                            <label class="form-label">Rate Type</label>
                            <select class="form-select @error('RateTypeID') is-invalid @enderror"
                                    name="RateTypeID" required>
                                <option value="">-- Select Rate Type --</option>
                                @foreach($rateTypes as $rate)
                                    <option value="{{ $rate->Id }}"
                                        {{ $rate->Id == old('RateTypeID', $driverRate->RateTypeID) ? 'selected' : '' }}>
                                        {{ $rate->RateTypeName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('RateTypeID')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Rate Value -->
                        <div class="col-md-6">
                            <label class="form-label">Rate Value (%)</label>
                            <input type="number" step="0.01"
                                   class="form-control @error('RateValue') is-invalid @enderror"
                                   name="RateValue"
                                   value="{{ old('RateValue', $driverRate->RateValue) }}"
                                   placeholder="e.g. 10.50" required>
                            @error('RateValue')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Source -->
                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <input type="text"
                                   class="form-control @error('Source') is-invalid @enderror"
                                   name="Source"
                                   value="{{ old('Source', $driverRate->Source) }}"
                                   placeholder="e.g. CBS, Manual" required>
                            @error('Source')
                            <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('yieldexpenserate.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerHTML='<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Updating...'; this.form.submit(); }">
                            <i class="fas fa-save me-1"></i> Update Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        .btn {
            font-size: 0.9rem;
        }

        .form-label {
            font-weight: 500;
        }
    </style>
@endsection
