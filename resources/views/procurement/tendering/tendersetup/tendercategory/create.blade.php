@extends('layouts.app')

@section('title', 'Create Tender Category')
@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">+ New Tender Category</h5>
                <a href="{{ route('tender-categories.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('tender-categories.store') }}" method="POST" id="tenderCategoryForm">
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="TenderCategory" class="form-label">Category Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('TenderCategory') is-invalid @enderror" 
                                id="TenderCategory" name="TenderCategory" required>
                            <option value="" disabled selected>Select a category type</option>
                            @foreach($tenderCatOptions as $category)
                                <option value="{{ $category->value }}" 
                                    @selected(old('TenderCategory', $defaultCategory) == $category->value)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('TenderCategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Select the category of tender</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="CategoryCode" class="form-label">Category Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light" id="CategoryCode" 
                                   name="CategoryCode" value="{{ $newCatCode }}" readonly>
                            <span class="input-group-text bg-light">
                                <i class="fas fa-hashtag"></i>
                            </span>
                        </div>
                        <small class="text-muted">Automatically generated code</small>
                    </div>
                </div>

                <div class="mt-3">
                    <label for="Description" class="form-label">Description</label>
                    <textarea class="form-control @error('Description') is-invalid @enderror" 
                              id="Description" name="Description" rows="4"
                              placeholder="Enter a detailed description of this tender category...">{{ old('Description') }}</textarea>
                    @error('Description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-4 border-top pt-3">
                    <button type="reset" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('TenderCategory');
        const codeInput = document.getElementById('CategoryCode');
        
        // Update code when category changes
        categorySelect.addEventListener('change', function() {
            fetch(`/tender-categories/generate-code?category=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    codeInput.value = data.code;
                })
                .catch(error => console.error('Error:', error));
        });

        // Form validation
        const form = document.getElementById('tenderCategoryForm');
        form.addEventListener('submit', function(event) {
            if (!categorySelect.value) {
                event.preventDefault();
                categorySelect.classList.add('is-invalid');
                categorySelect.focus();
            }
        });
        
        categorySelect.addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
            }
        });
    });
</script>
@endsection