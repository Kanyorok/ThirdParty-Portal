@extends('layouts.app')
@section('title', 'Edit Floor in Block')

@section('content')
    <div class="container mt-4" style="max-width: 850px;">

        {{-- Validation Errors --}}
    @if ($errors->any())
            <div class="alert alert-danger shadow-sm">
                <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following issues:
                </h6>
                <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('addfloor.update', $floor->Id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow border-0 rounded-3">
            <div class="card-header bg-primary fw-bold">
                <i class="bi bi-building"></i> Floor Details
            </div>

            <div class="card-body">
                <div class="row g-3 mb-3">
                    {{-- Property (readonly) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Property</label>
                        <input type="hidden" name="PropertyID" value="{{ $floor->PropertyID }}">
                        <input type="text" class="form-control"
                               value="{{ $floor->property->PropertyName ?? '' }}" readonly>
                    </div>

                    {{-- Block (readonly) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Block</label>
                        <input type="hidden" name="BlockID" value="{{ $floor->BlockID }}">
                        <input type="text" class="form-control"
                               value="{{ $floor->block->BlockName ?? '' }}" readonly>
                    </div>
                </div>

                {{-- Floor Label --}}
                <div class="mb-3">
                    <label for="FloorLabel" class="form-label fw-bold">Floor Label <span
                            class="text-danger">*</span></label>
                    <input type="text" name="FloorLabel" id="FloorLabel"
                           class="form-control @error('FloorLabel') is-invalid @enderror"
                           value="{{ old('FloorLabel', $floor->FloorLabel) }}"
                           placeholder="e.g. Ground Floor, 1st Floor" required>
                    @error('FloorLabel')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Floor Notes --}}
                <div class="mb-3">
                    <label for="FloorNotes" class="form-label fw-bold">Notes</label>
                    <textarea name="FloorNotes" id="FloorNotes" class="form-control" rows="3"
                              placeholder="Optional floor notes">{{ old('FloorNotes', $floor->FloorNotes) }}</textarea>
                </div>

                {{-- Form Actions --}}
                <div class="d-flex justify-content-between gap-2">
                    <a href="{{ route('addfloor.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">
                        <i class="bi bi-check-circle"></i> Update Floor
                    </button>
                </div>
            </div>
        </div>
    </form>
    </div>
@endsection
