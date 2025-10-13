@extends('layouts.app')
@section('title', 'Edit Insurance Product')

@section('content')
<div class="container mt-5" style="max-width: 750px;">

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-info text-dark">
            <h5 class="mb-0"><i class="bi bi-pencil-square"></i>Product</h5>
        </div>

        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.products.update', $product->Id) }}">
                @csrf
                @method('PUT')

                {{-- Insurance Provider (read-only) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Insurance Provider</label>
                    <input type="text" 
                           class="form-control bg-light" 
                           value="{{ optional($providers->firstWhere('Id', $product->InsuranceProviderID))->Name ?? 'N/A' }}" 
                           readonly>
                    <input type="hidden" name="InsuranceProviderID" value="{{ $product->InsuranceProviderID }}">
                </div>

                {{-- Name --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="Name" 
                           class="form-control" 
                           value="{{ old('Name', $product->Name) }}" 
                           placeholder="Enter product name" 
                           maxlength="150" 
                           required>
                </div>

                {{-- Type --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select name="Type" class="form-select" required>
                        <option value="" disabled>-- Select Type --</option>
                        @foreach ($producttypes as $type)
                            <option value="{{ $type->ID }}" 
                                {{ old('Type', $product->Type) == $type->ID ? 'selected' : '' }}>
                                {{ $type->Description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="Description" 
                              class="form-control" 
                              rows="3" 
                              placeholder="Enter product description">{{ old('Description', $product->Description) }}</textarea>
                </div>

                {{-- Active Checkbox --}}
                <div class="form-check mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="IsActive" 
                           value="1" 
                           id="isActiveCheck" 
                           {{ old('IsActive', $product->IsActive) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActiveCheck">
                        Active Product
                    </label>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('bancassurance.products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
