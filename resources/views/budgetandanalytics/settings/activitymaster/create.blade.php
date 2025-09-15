@extends('layouts.app')
@section('title', 'Create Activity')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <!-- Header -->
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-plus-circle me-2"></i> Activity
                </h5>
                <a href="{{ route('activitymaster.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <!-- Body -->
            <div class="card-body p-4">
                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>There were some errors with your submission:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('activitymaster.store') }}" method="POST">
                    @csrf
                    @method('POST')

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="BudgetLineID" class="form-label">Budget Line</label>
                            <select name="BudgetLineID" id="BudgetLineID" class="form-select" required>
                                <option value="">-- Select Budget Line --</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line->Id }}" {{ old('BudgetLineID') == $line->Id ? 'selected' : '' }}>
                                        {{ $line->LineName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="ActivityName" class="form-label">Activity Name</label>
                            <input type="text" name="ActivityName" id="ActivityName"
                                   class="form-control @error('ActivityName') is-invalid @enderror"
                                   value="{{ old('ActivityName') }}" required>
                            @error('ActivityName')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="Description" class="form-label">Description</label>
                        <textarea name="Description" id="Description"
                                  class="form-control @error('Description') is-invalid @enderror"
                                  rows="4" required>{{ old('Description') }}</textarea>
                        @error('Description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                            {{ old('IsActive', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="IsActive">Active</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>

                        <button type="submit" class="btn btn-success"
                                onclick="if(this.form.checkValidity()){
                                    this.disabled = true;
                                    this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i>Saving...';
                                    this.form.submit();
                                }">
                            Save Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Card styling */
        .card {
            border: none;
            border-radius: 0.5rem;
        }

        /* Buttons */
        .btn {
            font-size: 0.9rem;
        }

        /* Form spacing */
        .form-label {
            font-weight: 500;
        }
    </style>
@endsection
