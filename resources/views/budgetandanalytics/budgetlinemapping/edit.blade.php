@extends('layouts.app')
@section('title', 'Edit Budget Line Mapping')
@section('content')
<div class="container mt-4">
    <div class="card shadow-sm rounded-4">
        <div class="card-header bg-dark text-white mb-0">
            ✏️ Edit Budget Line Mapping
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('budgetlinemapping.update', $budgetLine->Id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="BudgetLineCategoryID" class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" name="BudgetLineCategoryID" id="BudgetLineCategoryID" required>
                        <option value="">-- Select Category --</option>
                        @foreach($budgetCategories as $cat)
                            <option value="{{ $cat->Id }}" {{ $cat->Id == $budgetLine->BudgetLineCategoryID ? 'selected' : '' }}>{{ $cat->CategoryName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="LineName" class="form-label">Line Name <span class="text-danger">*</span></label>
                    <input type="text" name="LineName" id="LineName" class="form-control" value="{{ old('LineName', $budgetLine->LineName) }}" required maxlength="255">
                </div>
                <div class="mb-3">
                    <label for="Description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="Description" id="Description" class="form-control" rows="2" required>{{ old('Description', $budgetLine->Description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="GLS" class="form-label">GL Accounts <span class="text-danger">*</span></label>
                    <select class="form-select" name="GLS[]" id="GLS" multiple required>
                        @foreach($gls as $gl)
                            <option value="{{ $gl->Id }}" {{ in_array($gl->Id, $selectedGLs ?? []) ? 'selected' : '' }}>{{ $gl->Description }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="IsDefault" id="IsDefault" value="1" {{ $budgetLine->IsDefault ? 'checked' : '' }}>
                    <label class="form-check-label" for="IsDefault">Set as Default</label>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('budgetlinemapping.index') }}" class="btn btn-outline-secondary">⬅ Back</a>
                    <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">💾 Update Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection