@extends('layouts.app')
@section('title', 'Edit Insurance Product')

@section('content')
    <div class="container mt-4">
        <form method="POST" action="{{ route('bancassurance.products.update', $product->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Insurance Provider <span class="text-danger">*</span></label>
                <input type="text" class="form-control"
                       value="{{ optional($providers->firstWhere('Id', $product->InsuranceProviderID))->Name ?? 'N/A' }}"
                       readonly>
                <input type="hidden" name="InsuranceProviderID" value="{{ $product->InsuranceProviderID }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="Name" class="form-control" required maxlength="150"
                       value="{{ old('Name', $product->Name) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <input type="text" name="Type" class="form-control" required maxlength="150"
                       value="{{ old('Type', $product->Type) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Description </label>
                <textarea name="Description" class="form-control"
                          rows="3">{{ old('Description', $product->Description) }}</textarea>
            </div>

            <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" name="IsActive" value="1" id="primaryCheck"
                    {{ old('IsActive', $product->IsActive) ? 'checked' : '' }}>
                <label class="form-check-label" for="primaryCheck">Is Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </div>
@endsection

