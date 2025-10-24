@extends('layouts.app')
@section('title', 'Tax Type Setup')
@section('content')

    <div class="container mt-3">
        <div class="card shadow-sm rounded-4">
            <div class="card-body p-4">

                {{-- Page intro --}}
                <h5 class="mb-3 text-info">
                    <i class="fas fa-tags me-2"></i> Edit Tax Type
                </h5>
                <p class="text-muted small">
                    Update the details of the tax type for your organization.
                </p>

                {{-- Error messages --}}
            @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

                {{-- Form --}}
            <form method="POST" action="{{ route('taxtypes.update', $taxType->Id) }}">
                @csrf
                @method('PUT')

                {{-- Tax Type Name --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tax Type Name</label>
                    <input type="text"
                           name="TaxTypeName"
                           class="form-control @error('TaxTypeName') is-invalid @enderror"
                           value="{{ old('TaxTypeName', $taxType->TaxTypeName) }}"
                           placeholder="e.g., VAT, Income Tax"
                           required>
                    @error('TaxTypeName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="Description"
                              class="form-control @error('Description') is-invalid @enderror"
                              rows="3"
                              placeholder="Brief description of the tax type"
                              required>{{ old('Description', $taxType->Description) }}</textarea>
                    @error('Description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Action buttons --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('taxtypes.index') }}" class="btn btn-outline-secondary px-3">
                        <i class="fas fa-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit"
                            class="btn btn-info px-4"
                            onclick="if(this.form.checkValidity()){this.disabled=true; this.innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i>Updating...'; this.form.submit();}">
                        <i class="fas fa-save me-1"></i> Update Tax Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
