@extends('layouts.app')
@section('title', 'Tax Type Setup')

@section('content')
    <div class="card p-4 shadow rounded-4">
        <!-- Header -->
        <div class="card-header bg-light px-3 py-2 mb-3 rounded-3 d-flex justify-content-between align-items-center">
            <h4 class="text-info mb-0"><i class="fas fa-plus-circle me-2"></i> Add Tax Type</h4>
        </div>

        <!-- Body -->
        <div class="card-body">
            <!-- Validation & Flash Messages -->
            @if ($errors->any())
                <div class="alert alert-danger rounded-3 shadow-sm">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger rounded-3 shadow-sm">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success rounded-3 shadow-sm">{{ session('success') }}</div>
            @endif

            <p class="text-muted">Fill in the details below to register a new tax type for your organization.</p>

            <!-- Form -->
            <form method="POST" action="{{ route('taxtypes.store') }}">
                @csrf

                <!-- Tax Type Name -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tax Type Name <span class="text-danger">*</span></label>
                    <input type="text"
                    name="TaxTypeName"
                    class="form-control @error('TaxTypeName') is-invalid @enderror"
                    value="{{ old('TaxTypeName') }}"
                           placeholder="e.g., VAT, Income Tax"
                           required>
                    @error('TaxTypeName')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea
                        name="Description"
                        class="form-control @error('Description') is-invalid @enderror"
                        rows="3"
                        placeholder="Brief description of the tax type"
                        required>{{ old('Description') }}</textarea>
                    @error('Description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('taxtypes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit"
                            class="btn btn-info px-4"
                            onclick="if(this.form.checkValidity()){this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i>Saving...'; this.form.submit();}">
                        <i class="fas fa-save me-1"></i> Save Tax Type
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
