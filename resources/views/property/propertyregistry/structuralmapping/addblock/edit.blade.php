@extends('layouts.app')
@section('title', 'Edit Property Block')

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

        <div class="card shadow border-0 rounded-3">
            <div class="card-header bg-light fw-bold">
                <i class="bi bi-pencil-square"></i> Block Details
            </div>

            <div class="card-body">
                <form action="{{ route('addblock.update', $block->Id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        {{-- Property Dropdown --}}
                        <div class="col-md-6">
                            <label for="PropertyID" class="form-label fw-bold">Property <span
                                    class="text-danger">*</span></label>
                            <select name="PropertyID" id="PropertyID" class="form-select" required>
                                <option value="">-- Select a property --</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->Id }}"
                                        {{ old('PropertyID', $block->PropertyID) == $property->Id ? 'selected' : '' }}>
                                        {{ $property->PropertyName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Block Name --}}
                        <div class="col-md-6">
                            <label for="BlockName" class="form-label fw-bold">Block Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="BlockName" id="BlockName"
                                   class="form-control @error('BlockName') is-invalid @enderror"
                                   value="{{ old('BlockName', $block->BlockName) }}" placeholder="e.g. Block A, Tower 1"
                                   required>
                            @error('BlockName')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="Description" class="form-label fw-bold">Description</label>
                        <textarea name="Description" id="Description"
                                  class="form-control" rows="3"
                                  placeholder="Optional description">{{ old('Description', $block->Description) }}</textarea>
                    </div>

                    {{-- Form Actions --}}
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit"
                                class="btn btn-success"
                                onclick="this.disabled=true; this.innerText='Updating...'; this.form.submit();">
                            <i class="bi bi-check-circle"></i> Update Block
                        </button>
                        <a href="{{ route('addblock.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
