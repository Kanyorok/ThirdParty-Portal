@extends('layouts.app')
@section('title', 'Edit Rider')
@section('content')
    <div class="container mt-4">
        <form method="POST" action="{{ route('bancassurance.riders.update', $rider->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Select Provider <span class="text-danger">*</span></label>
                <input type="text" class="form-control"
                       value="{{ optional($providers->firstWhere('Id', $rider->InsuranceProviderId))->Name ?? 'N/A' }}"
                       readonly>
                <input type="hidden" name="InsuranceProviderId" value="{{ $rider->InsuranceProviderId }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Product <span class="text-danger">*</span></label>
                <select name="Product" class="form-select" required>
                    <option value="">-- Select --</option>
                    @foreach($products as $product)
                        <option
                            value="{{ $product->Id }}" {{ $product->Id == $rider->Product ? 'selected' : '' }}>{{ $product->Name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Rider Name <span class="text-danger">*</span></label>
                <input type="text" name="RiderName" class="form-control" value="{{ $rider->RiderName }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <textarea name="Description" class="form-control" rows="2">{{ $rider->Description }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Additional Premium <span class="text-danger">*</span></label>
                <input type="number" name="AdditionalPremium" class="form-control" step="0.01" min="0"
                       value="{{ $rider->AdditionalPremium }}">
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsOptional" value="1"
                       id="optionalCheck" {{ $rider->IsOptional ? 'checked' : '' }}>
                <label class="form-check-label" for="optionalCheck">Is Optional</label>
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" value="1"
                       id="activeCheck" {{ $rider->IsActive ? 'checked' : '' }}>
                <label class="form-check-label" for="activeCheck">Is Active</label>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Update Rider</button>
            </div>
        </form>
    </div>

    <!-- No JS needed: provider and product are now static in edit mode -->
@endsection
