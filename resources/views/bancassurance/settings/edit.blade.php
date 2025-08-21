@extends('layouts.app')
@section('title', 'Edit Setting')

@section('content')
<div class="container mt-4">
    <h4>✏️ Edit Setting</h4>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('bancassurance.settings.update', $record->ID) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- CodeID -->
            <div class="col-md-6 mb-3">
                <label for="CodeID" class="form-label">Setting Type (CodeID)</label>
                <select name="CodeID" id="CodeID" class="form-select" required disabled>
                    @foreach($codeTypes as $type)
                        <option value="{{ $type }}" {{ $record->CodeID == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Cannot change CodeID during editing.</div>
            </div>

            <!-- Description -->
            <div class="col-md-6 mb-3">
                <label for="Description" class="form-label">Description</label>
                <input type="text" name="Description" id="Description"
                    class="form-control @error('Description') is-invalid @enderror"
                    value="{{ old('Description', $record->Description) }}" required>
                @error('Description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Display Order -->
            <div class="col-md-4 mb-3">
                <label for="DisplayOrder" class="form-label">Display Order</label>
                <input type="number" name="DisplayOrder" id="DisplayOrder"
                    class="form-control @error('DisplayOrder') is-invalid @enderror"
                    value="{{ old('DisplayOrder', $record->DisplayOrder) }}">
                @error('DisplayOrder')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Status -->
            <div class="col-md-4 mb-3">
                <label for="IsActive" class="form-label">Status</label>
                <select name="IsActive" id="IsActive" class="form-select">
                    <option value="1" {{ $record->IsActive ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$record->IsActive ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">💾 Update</button>
            <a href="{{ route('bancassurance.settings.index') }}" class="btn btn-secondary">🔙 Back</a>
        </div>
    </form>
</div>
@endsection
