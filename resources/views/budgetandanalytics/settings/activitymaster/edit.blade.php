@extends('layouts.app')
@section('title', 'Edit Activity')

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3" style="margin: 0.5rem;">
            <!-- Header -->
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-edit me-2"></i>  Activity
                </h5>
                <a href="{{ route('activitymaster.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

            <!-- Body -->
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    Update the details of this activity. Ensure that all required fields are filled, especially the budget line and activity name.
                </p>

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
                <form action="{{ route('activitymaster.update', $activity->Id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Budget Line -->
                        <div class="col-md-6 mb-3">
                            <label for="BudgetLineID" class="form-label">Budget Line</label>
                            <select name="BudgetLineID" id="BudgetLineID" class="form-select" required>
                                <option value="">-- Select Budget Line --</option>
                                @foreach($lines as $line)
                                    <option value="{{ $line->Id }}"
                                        {{ old('BudgetLineID', $activity->BudgetLineID) == $line->Id ? 'selected' : '' }}>
                                        {{ $line->LineName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Activity Name -->
                        <div class="col-md-6 mb-3">
                            <label for="ActivityName" class="form-label">Activity Name</label>
                            <input type="text" name="ActivityName" id="ActivityName"
                                   class="form-control @error('ActivityName') is-invalid @enderror"
                                   value="{{ old('ActivityName', $activity->ActivityName) }}" required>
                            @error('ActivityName')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="Description" class="form-label">Description</label>
                        <textarea name="Description" id="Description"
                                  class="form-control @error('Description') is-invalid @enderror"
                                  rows="4" required>{{ old('Description', $activity->Description) }}</textarea>
                        @error('Description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Checkbox -->
                    <div class="form-check mb-4">
                        <input type="checkbox" name="IsActive" id="IsActive" class="form-check-input"
                            {{ old('IsActive', $activity->IsActive) ? 'checked' : '' }}>
                        <label class="form-check-label" for="IsActive">Active</label>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-success"
                                onclick="if(this.form.checkValidity()){
                                    this.disabled = true;
                                    this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i>Updating...';
                                    this.form.submit();
                                }">
                            Update Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card {
            border: none;
            border-radius: 0.5rem;
        }
        .btn {
            font-size: 0.9rem;
        }
        .form-label {
            font-weight: 500;
        }
    </style>
@endsection
