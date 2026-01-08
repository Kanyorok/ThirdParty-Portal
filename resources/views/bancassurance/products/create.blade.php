@extends('layouts.app')
@section('title', 'Add Insurance Product')

@section('content')
    <div class="container mt-5" style="max-width: 850px;">

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-primary text-white rounded-top-4 py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-box-seam me-2"></i>Insurance Product Information
                </h5>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="{{ route('bancassurance.products.store') }}">
                    @csrf

                    {{-- Product Details Section --}}
                    <div class="mb-4">
                        <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                            <i class="bi bi-info-circle me-2"></i>Product Details
                        </h6>

                        {{-- Insurance Provider --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Insurance Provider <span class="text-danger">*</span></label>
                            <select name="InsuranceProviderID" class="form-select rounded-pill shadow-sm" required>
                                <option value="" disabled selected>-- Select Insurance Provider --</option>
                                @foreach ($providers as $provider)
                                    <option value="{{ $provider->Id }}" {{ old('InsuranceProviderID') == $provider->Id ? 'selected' : '' }}>
                                        {{ $provider->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Product Name & Type in row --}}
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="Name" class="form-control rounded-pill shadow-sm"
                                    value="{{ old('Name') }}" placeholder="Enter product name"
                                    maxlength="150" required>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Product Type <span class="text-danger">*</span></label>
                                <select name="Type" class="form-select rounded-pill shadow-sm" required>
                                    <option value="" disabled selected>-- Select Type --</option>
                                    @foreach ($producttypes as $type)
                                        <option value="{{ $type->ID }}" {{ old('Type') == $type->ID ? 'selected' : '' }}>
                                            {{ $type->Description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Additional Information Section --}}
                    <div class="mb-4">
                        <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                            <i class="bi bi-card-text me-2"></i>Additional Information
                        </h6>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="Description" class="form-control rounded-4 shadow-sm" rows="4"
                                placeholder="Enter product description (optional)">{{ old('Description') }}</textarea>
                            <small class="text-muted">Provide details about coverage, benefits, or features</small>
                        </div>
                    </div>

                    {{-- Product Status Section --}}
                    <div class="mb-4">
                        <h6 class="text-primary fw-semibold mb-3 border-bottom pb-2">
                            <i class="bi bi-toggles me-2"></i>Product Status
                        </h6>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="IsActive" value="1"
                                id="isActiveCheck" {{ old('IsActive', 1) ? 'checked' : '' }} style="cursor: pointer;">
                            <label class="form-check-label fw-semibold" for="isActiveCheck" style="cursor: pointer;">
                                Active Product
                                <small class="text-muted d-block">Enable this product to be available for policies</small>
                            </label>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                        <a href="{{ route('bancassurance.products.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                            <i class="bi bi-check-circle me-2"></i>Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
</div>
@endsection
