@extends('layouts.app')
@section('title', 'Edit Insurance Product')

@section('content')
    <div class="container mt-4">
        <h4>✏️ Edit Insurance Product</h4>

        <form method="POST" action="{{ route('bancassurance.products.update', $product->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Insurance Provider</label>
                <select name="InsuranceProviderID" class="form-select" required>
                    <option value="">--Select a provider--</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->Id }}"
                            {{ $product->InsuranceProviderID == $provider->Id ? 'selected' : '' }}>
                            {{ $provider->InsuranceProviderNO }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input
                    type="text"
                    name="Name"
                    class="form-control"
                    required
                    maxlength="150"
                    value="{{ old('Name', $product->Name) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Type</label>
                <input
                    type="text"
                    name="Type"
                    class="form-control"
                    required
                    maxlength="150"
                    value="{{ old('Type', $product->Type) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea
                    name="Description"
                    class="form-control"
                    rows="3">{{ old('Description', $product->Description) }}</textarea>
            </div>

            <div class="mb-3 form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="IsActive"
                    value="1"
                    id="primaryCheck"
                    {{ old('IsActive', $product->IsActive) ? 'checked' : '' }}>
                <label class="form-check-label" for="primaryCheck">Is Active</label>
            </div>

            <button type="submit" class="btn btn-primary">💾 Update Product</button>
        </form>
    </div>
@endsection

