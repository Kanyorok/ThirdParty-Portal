@extends('layouts.app')
@section('title', 'Edit Transaction Type')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">✏️ Edit Transaction Type</h4>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Error:</strong> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('transactiontypes.update', $type->Id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="Code" class="form-label">Code</label>
                <input type="text" class="form-control" id="Code" name="Code" value="{{ $type->Code }}" disabled>
            </div>

            <div class="mb-3">
                <label for="Description" class="form-label">Description</label>
                <input type="text" class="form-control" id="Description" name="Description"
                       value="{{ $type->Description }}" required>
            </div>

            <div class="mb-3">
                <label for="Module" class="form-label">Module</label>
                <select class="form-select" name="Module" id="Module" required>
                    <option value="Finance" {{ $type->Module == 'Finance' ? 'selected' : '' }}>Finance</option>
                    <option value="Procurement" {{ $type->Module == 'Procurement' ? 'selected' : '' }}>Procurement
                    </option>
                    <option value="Inventory" {{ $type->Module == 'Inventory' ? 'selected' : '' }}>Inventory</option>
                    <option value="FixedAssets" {{ $type->Module == 'FixedAssets' ? 'selected' : '' }}>Fixed Assets
                    </option>
                    <option value="Others" {{ $type->Module == 'Others' ? 'selected' : '' }}>Others</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="IsActive" class="form-label">Status</label>
                <select class="form-select" name="IsActive" id="IsActive" required>
                    <option value="1" {{ $type->IsActive ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$type->IsActive ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Transaction Type</button>
            <a href="{{ route('transactiontypes.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
