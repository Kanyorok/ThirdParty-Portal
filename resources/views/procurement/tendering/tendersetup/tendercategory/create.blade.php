@extends('layouts.app')

@section('title', 'Create Tender Category')
@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">+ New Tender Category</h5>
                <a href="{{ route('tendercategory.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('tendercategory.store') }}" method="POST" id="tenderCategoryForm">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="TenderCategory" class="form-label">Category Type <span class="text-danger">*</span></label>
                        <select name="TenderCategory" id="TenderCategory"
                                class="form-select @error('TenderCategory') is-invalid @enderror" required>
                            <option value="">-- Select Category Type --</option>
                            @foreach($tenderCatOptions as $option)
                                <option value="{{ $option->Value }}" {{ old('TenderCategory') == $option->Value ? 'selected' : '' }}>
                                    {{ $option->Description }}
                                </option>
                            @endforeach
                        </select>
                        @error('TenderCategory')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="CategoryCode" class="form-label">Category Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light" id="CategoryCode"
                                   name="CategoryCode" value="" readonly>
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

        // Generate code when page loads if there's an old value
        if (categorySelect.value) {
            generateCode(categorySelect.value);
        }

        // Update code when category changes
        categorySelect.addEventListener('change', function() {
            const selectedValue = this.value;

            if (!selectedValue) {
                codeInput.value = '';
                return;
            }

            generateCode(selectedValue);
        });

        // Function to generate category code
        function generateCode(category) {
            fetch(`{{ route('tendercategory.generateCode') }}?category_type=${encodeURIComponent(category)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.ok && data.code) {
                        codeInput.value = data.code;
                    } else {
                        console.error('Failed to generate code:', data);
                        codeInput.value = 'ERROR';
                    }
                })
                .catch(error => {
                    console.error('Error generating code:', error);
                    codeInput.value = 'ERROR';
                });
        }

        // Form validation
        const form = document.getElementById('tenderCategoryForm');
        form.addEventListener('submit', function(event) {
            if (!categorySelect.value) {
                event.preventDefault();
                categorySelect.classList.add('is-invalid');
                categorySelect.focus();
                return false;
            }

            if (!codeInput.value || codeInput.value === 'ERROR') {
                event.preventDefault();
                alert('Please wait for the category code to be generated.');
                return false;
            }
        });

        categorySelect.addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
            }
        });

        // Handle reset button
        form.addEventListener('reset', function() {
            codeInput.value = '';
        });
    });
</script>
@endsection
