@extends('layouts.app')
@section('title', 'Add Insurance Product')

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

<div class="container mt-4" style="max-width: 900px;">

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
        <div class="card-header bg-primary text-white rounded-top-4 py-3">
            <h5 class="mb-0 fw-bold">
                <i class="bi bi-box-seam me-2"></i> Add Insurance Product
            </h5>
        </div>

        {{-- Body --}}
        <div class="card-body p-4">
            <form method="POST" action="{{ route('bancassurance.products.store') }}">
                @csrf

                {{-- ================= PRODUCT DETAILS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Product Details</h6>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small ">
                                Insurance Provider <span class="text-danger">*</span>
                            </label>
                            <select name="InsuranceProviderID"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Provider --</option>
                                @foreach ($providers as $provider)
                                    <option value="{{ $provider->Id }}"
                                        {{ old('InsuranceProviderID') == $provider->Id ? 'selected' : '' }}>
                                        {{ $provider->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label small ">
                                Product Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="Name"
                                   class="form-control form-control-sm"
                                   value="{{ old('Name') }}"
                                   maxlength="150"
                                   placeholder="Enter product name"
                                   required>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small ">
                                Product Type <span class="text-danger">*</span>
                            </label>
                            <select name="Type"
                                    class="form-select form-select-sm"
                                    required>
                                <option value="">-- Select Type --</option>
                                @foreach ($producttypes as $type)
                                    <option value="{{ $type->ID }}"
                                        {{ old('Type') == $type->ID ? 'selected' : '' }}>
                                        {{ $type->Description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ================= ADDITIONAL INFORMATION ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Additional Information</h6>

                    <div class="mb-3">
                        <label class="form-label small ">
                            Description
                        </label>
                        <textarea name="Description"
                                  class="form-control form-control-sm"
                                  rows="4"
                                  placeholder="Optional product description">{{ old('Description') }}</textarea>
                        <small class="text-muted">
                            Provide coverage, benefits, or feature details
                        </small>
                    </div>
                </div>

                {{-- ================= STATUS ================= --}}
                <div class="mb-4">
                    <h6 class="section-title">Product Status</h6>

                    <div class="form-check form-switch">
                        <input class="form-check-input"
                               type="checkbox"
                               name="IsActive"
                               id="IsActive"
                               value="1"
                               {{ old('IsActive', 1) ? 'checked' : '' }}>
                        <label class="form-check-label " for="IsActive">
                            Active Product
                            <small class="text-muted d-block">
                                Enable this product for policy creation
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
                        <i class="bi bi-check-circle me-1"></i> Save Product
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection
