@extends('layouts.app')
@section('title', 'Insurance Product')

@section('content')

{{-- ================= STYLES ================= --}}
<style>
    .section-title {
        color: #000;
        font-weight: 600;
        font-size: .9rem;
        padding-bottom: .35rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
</style>

<div class="container mt-4" style="max-width: 850px;">

    {{-- ================= ERRORS ================= --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-lg border-0 rounded-4">

        {{-- Header --}}
        <div class="card-header border-bottom bg-primary">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-pencil-square me-2"></i>Edit Insurance Product
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.products.update', $product->Id) }}">
                @csrf
                @method('PUT')

                {{-- ================= PRODUCT DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Product Details</h6>

                    <div class="row g-3">

                        {{-- Insurance Provider (Read-only) --}}
                        <div class="col-md-4">
                            <label class="form-label small">
                                Insurance Provider
                            </label>
                            <input type="text"
                                class="form-control form-control-sm bg-light"
                                value="{{ optional($providers->firstWhere('Id', $product->InsuranceProviderID))->Name ?? 'N/A' }}"
                                readonly>
                            <input type="hidden"
                                name="InsuranceProviderID"
                                value="{{ $product->InsuranceProviderID }}">
                        </div>

                        {{-- Product Name --}}
                        <div class="col-md-4">
                            <label class="form-label small">
                                Product Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="Name"
                                class="form-control form-control-sm"
                                value="{{ old('Name', $product->Name) }}"
                                maxlength="150"
                                required>
                        </div>

                        {{-- Product Type --}}
                        <div class="col-md-4">
                            <label class="form-label small">
                                Product Type <span class="text-danger">*</span>
                            </label>
                            <select name="Type"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Type --</option>
                                @foreach ($producttypes as $type)
                                    <option value="{{ $type->ID }}"
                                        {{ old('Type', $product->Type) == $type->ID ? 'selected' : '' }}>
                                        {{ $type->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>


                {{-- ================= ADDITIONAL INFO ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Additional Information</h6>

                    <div class="mb-3">
                        <label class="form-label small ">
                            Description
                        </label>
                        <textarea name="Description"
                                  class="form-control form-control-sm"
                                  rows="4">{{ old('Description', $product->Description) }}</textarea>
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Product Status</h6>

                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="IsActive"
                               value="1"
                               id="IsActive"
                            {{ old('IsActive', $product->IsActive) ? 'checked' : '' }}>
                        <label class="form-check-label " for="IsActive">
                            Active Product
                            <small class="text-muted d-block">
                                Disable to hide this product from new policies
                            </small>
                        </label>
                    </div>
                </div>

                {{-- ================= ACTIONS ================= --}}
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('bancassurance.products.index') }}"
                       class="btn btn-sm btn-outline-secondary px-4">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-sm btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i> Update Product
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection
