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
                    <div class="row mb-3">
                        <div class="mb-3">
                            <label for="BudgetLineCategoryID" class="form-label">Category <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" name="BudgetLineCategoryID" id="BudgetLineCategoryID" required>
                                <option value="">-- Select Category --</option>
                                @foreach($budgetCategories as $category)
                                    <option
                                        value="{{ $category->Id }}" {{ $category->Id == $budgetLine->BudgetLineCategoryID ? 'selected' : '' }}>{{ $category->CategoryName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="LineName" class="form-label">Line Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="LineName" id="LineName" class="form-control"
                                   value="{{ old('LineName', $budgetLine->LineName) }}" required maxlength="255">
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="DepartmentID" required>
                                <option selected disabled>-- Select Department --</option>
                                @foreach ($departments as $department)
                                    <option
                                        value="{{ $department->Id }}" {{ $department->Id == $budgetLine->DepartmentID ? 'selected' : '' }}>{{ $department->Name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">GL Account Type</label>
                            <select class="form-select" name="GLAccountTypeID" id="glAccountTypeSelect" required>
                                <option selected disabled>-- Select Account type --</option>
                                @foreach ($glAccountTypes as $item)
                                    <option
                                        value="{{ $item->Value }}" {{ $item->Value == $budgetLine->GLAccountTypeID ? 'selected' : '' }}>{{ $item->Description }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">GL Sub-Type</label>
                            <select class="form-select" name="GLAccountSubTypeID" id="glAccountSubTypeSelect" required>
                                <option selected disabled>-- Select Sub-Type --</option>
                                @foreach ($glSubtype as $subtype)
                                    <option
                                        value="{{ $subtype->Id }}" {{ $subtype->Id == $budgetLine->GLAccountSubTypeID ? 'selected' : '' }}>{{ $subtype->GLAccountSubTypeName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 ">
                            <label for="Description" class="form-label">Description <span
                                    class="text-danger">*</span></label>
                            <textarea name="Description" id="Description" class="form-control" rows="2"
                                      required>{{ old('Description', $budgetLine->Description) }}</textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="GLS" class="form-label">GL Accounts <span class="text-danger">*</span></label>
                        <select class="form-select" name="GLS[]" id="GLS" multiple required>
                            @foreach($gls as $gl)
                                <option
                                    value="{{ $gl->Id }}" {{ in_array($gl->Id, $selectedGLs ?? []) ? 'selected' : '' }}>{{ $gl->Description }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
                    </div>


                    <div class="mb-3">
                        <label for="IsProductDriven" class="form-label">Is this Budget Line Product Driven?</label>
                        <select class="form-select" name="IsProductDriven" id="IsProductDriven" required>
                            <option value="1" {{ $budgetLine->IsProductDriven ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !$budgetLine->IsProductDriven ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- 🔗 Product Type Mapping -->
                    <div class="mb-3" id="productTypeSection"
                         style="display: {{ $budgetLine->IsProductDriven ? 'block' : 'none' }};">
                        <h6>🔗 Product Types (Multiple)</h6>
                        <label class="form-label">Select Product Types</label>
                        <select multiple class="form-select" name="ProductTypes[]"
                                id="ProductTypesSelect" {{ $budgetLine->IsProductDriven ? '' : 'disabled' }}>
                            @foreach($productTypes as $type)
                                <option
                                    value="{{ $type->Id }}" {{ isset($selectedProductTypes) && in_array($type->Id, $selectedProductTypes) ? 'selected' : '' }}>{{ $type->Name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple Product Types.</div>
                    </div>

                    {{-- <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="IsDefault" id="IsDefault" value="1" {{ $budgetLine->IsDefault ? 'checked' : '' }}>
                        <label class="form-check-label" for="IsDefault">Set as Default</label>
                    </div> --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('budgetlinemapping.index') }}" class="btn btn-outline-secondary">⬅ Back</a>
                        <button type="submit" class="btn btn-primary"
                                onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">💾 Update
                            Mapping
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const IsProductDriven = document.getElementById('IsProductDriven');
            const productTypeSection = document.getElementById('productTypeSection');
            const productTypesSelect = document.getElementById('ProductTypesSelect');

            IsProductDriven.addEventListener('change', function () {
                if (this.value === '1') {
                    productTypeSection.style.display = 'block';
                    productTypesSelect.disabled = false;
                } else {
                    productTypeSection.style.display = 'none';
                    productTypesSelect.disabled = true;
                }
            });

            const typeSelect = document.getElementById('glAccountTypeSelect');
            const subTypeSelect = document.getElementById('glAccountSubTypeSelect');
            typeSelect.addEventListener('change', function () {
                const typeId = this.value;
                subTypeSelect.innerHTML = '<option selected disabled>Loading...</option>';
                fetch(`/budgetlinemapping/gl-subtypes/${typeId}`)
                    .then(response => response.json())
                    .then(data => {
                        subTypeSelect.innerHTML = '<option selected disabled>-- Select Sub-Type --</option>';
                        data.forEach(function (subType) {
                            subTypeSelect.innerHTML += `<option value="${subType.Id}">${subType.GLAccountSubTypeName}</option>`;
                        });
                    });
            });
        });
    </script>
@endsection
