@extends('layouts.app')

@section('title', 'Create Tender Type')
@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Create New Tender Type</h5>
                <a href="{{ route('tender-types.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('tender-types.store') }}" method="POST" id="tenderTypeForm">
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="TenderType" class="form-label">Tender Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('TenderType') is-invalid @enderror" 
                                id="TenderType" name="TenderType" required>
                            <option value="" disabled selected>Select a tender type</option>
                            @foreach(App\Enums\TenderTypeEnum::cases() as $type)
                                <option value="{{ $type->value }}" @selected(old('TenderType') == $type->value)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('TenderType')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Select the type of tender from the dropdown</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="TypeCode" class="form-label">Type Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control bg-light" id="TypeCode" name="TypeCode" value="{{ $newTypeCode }}" readonly>
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
                              placeholder="Enter a detailed description of this tender type...">{{ old('Description') }}</textarea>
                    @error('Description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end mt-4 border-top pt-3">
                    <button type="reset" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Tender Type
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
        const form = document.getElementById('tenderTypeForm');
        const tenderTypeSelect = document.getElementById('TenderType');
        
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            if (!tenderTypeSelect.value) {
                tenderTypeSelect.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                //  Panda juu - to first invalid field
                document.querySelector('.is-invalid').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });

        // Clear validation when user makes selection
        tenderTypeSelect.addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
            }
        });
    });
</script>
@endsection