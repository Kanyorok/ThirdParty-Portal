@extends('layouts.app')
@section('title', 'Edit Rider')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Rider</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('bancassurance.riders.update', $rider->Id) }}">
                @csrf
                @method('PUT')

                <!-- Provider & Product (same row) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="Provider-select" class="form-label">
                            Select Provider <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control"
                               value="{{ optional($providers->firstWhere('Id', $rider->InsuranceProviderId))->Name ?? 'N/A' }}"
                               readonly>
                        <input type="hidden" name="InsuranceProviderId" value="{{ $rider->InsuranceProviderId }}">
                    </div>
                    <div class="col-md-6">
                        <label for="Product-select" class="form-label">
                            Product <span class="text-danger">*</span>
                        </label>
                        <select name="Product" id="Product-select" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->Id }}"
                                    {{ $product->Id == $rider->Product ? 'selected' : '' }}>
                                    {{ $product->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Rider Name & Additional Premium (same row) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="RiderName" class="form-label">
                            Rider Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="RiderName" id="RiderName"
                               class="form-control"
                               value="{{ old('RiderName', $rider->RiderName) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="AdditionalPremium" class="form-label">
                            Additional Premium <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="AdditionalPremium" id="AdditionalPremium"
                               class="form-control" step="0.01" min="0"
                               value="{{ old('AdditionalPremium', $rider->AdditionalPremium) }}" required>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="Description" class="form-label">Description</label>
                    <textarea name="Description" id="Description" class="form-control"
                              rows="2">{{ old('Description', $rider->Description) }}</textarea>
                </div>

                <!-- Checkboxes -->
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="IsOptional" value="1"
                           id="IsOptional" {{ $rider->IsOptional ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsOptional">Is Optional</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="IsActive" value="1"
                           id="IsActive" {{ $rider->IsActive ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsActive">Is Active</label>
                </div>

                <!-- Submit -->
                <div class="text-end">
                    <a href="{{ route('bancassurance.riders.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Rider
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
