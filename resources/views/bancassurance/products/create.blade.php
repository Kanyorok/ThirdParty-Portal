@extends('layouts.app')
@section('title', 'Add Insurance Product')

@section('content')
<div class="container mt-4">
    <h4>➕ Add Insurance Product</h4>

    <form method="POST" action="{{ route('bancassurance.products.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Insurance Provider</label>
            <select name="InsuranceProviderID" class="form-select" required>
                <option value="">-- Select Provider --</option>
                @foreach($providers as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Policy Type</label>
            <select name="PolicyTypeID" class="form-select" required>
                <option value="">-- Select Type --</option>
                @foreach($policyTypes as $id => $desc)
                    <option value="{{ $id }}">{{ $desc }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="ProductName" class="form-control" required maxlength="150">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="Description" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">💾 Save Product</button>
    </form>
</div>
@endsection
