@extends('layouts.app')
@section('title', 'Edit Budget Line Mapping')
@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm rounded-4">
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
                                    <option data-type-id="{{ $subtype->GLSubAccountTypeID }}"
                                            value="{{ $subtype->Id }}" {{ $subtype->Id == $budgetLine->GLAccountSubTypeID ? 'selected' : '' }}>{{ $subtype->Description }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="Description" class="form-label">Description <span
                                    class="text-danger">*</span></label>
                            <textarea name="Description" id="Description" class="form-control" rows="2"
                                      required>{{ old('Description', $budgetLine->Description) }}</textarea>
                        </div>
                    </div>

                    <!-- Updated GL Accounts section -->
                    <div class="mb-3">
                        <h6>🔗 CBS GL Accounts</h6>
                        <label class="form-label">Select CBS GLs</label>
                        <select multiple class="form-select" id="glTypes" name="GLS[]" required size="8">
                            @foreach($gls as $gl)
                                <option
                                    value="{{ $gl->Id }}" {{ in_array($gl->Id, $selectedGLs ?? []) ? 'selected' : '' }}>{{ $gl->Description }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select GL.</div>
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
                        <h6>🔗 Product (Multiple)</h6>
                        <label class="form-label">Select Product</label>
                        <select multiple class="form-select" name="ProductTypes[]"
                                id="ProductTypesSelect" {{ $budgetLine->IsProductDriven ? '' : 'disabled' }}>
                            @foreach($productTypes as $type)
                                <option
                                    value="{{ $type->Id }}" {{ isset($selectedProductTypes) && in_array($type->Id, $selectedProductTypes) ? 'selected' : '' }}>{{ $type->Description }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple Product.</div>
                    </div>

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
            const typeSelect = document.getElementById('glAccountTypeSelect');
            const subTypeSelect = document.getElementById('glAccountSubTypeSelect');
            const glTypesSelect = document.getElementById('glTypes');

            // Store initially selected GL values on page load
            const initiallySelectedGLs = Array.from(glTypesSelect.selectedOptions).map(opt => opt.value);

            IsProductDriven.addEventListener('change', function () {
                if (this.value === '1') {
                    productTypeSection.style.display = 'block';
                } else {
                    productTypeSection.style.display = 'none';
                }
            });

            typeSelect.addEventListener('change', function () {
                const typeId = this.value;
                subTypeSelect.innerHTML = '<option selected disabled>Loading...</option>';

                // Clear GL Types when account type changes
                glTypesSelect.innerHTML = '<option disabled>Select Sub-Type first</option>';

                fetch(`/budget/budgetlinemapping/gl-subtypes/${typeId}`)
                    .then(response => response.json())
                    .then(data => {
                        subTypeSelect.innerHTML = '<option selected disabled>-- Select Sub-Type --</option>';
                        data.forEach(function (subType) {
                            subTypeSelect.innerHTML += `<option data-type-id="${subType.GLSubAccountTypeID}" value="${subType.Id}">${subType.Description}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Error loading GL Sub Types:', error);
                        subTypeSelect.innerHTML = '<option disabled>Error loading sub-types</option>';
                    });
            });

            subTypeSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const typeId = selectedOption.dataset.typeId;

                // Clear GL Types first
                glTypesSelect.innerHTML = '<option disabled>Loading...</option>';

                fetch(`/budget/budgetlinemapping/gl-types/${typeId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Reset and populate GL Types dropdown
                        glTypesSelect.innerHTML = '';
                        if (data.length === 0) {
                            glTypesSelect.innerHTML = '<option disabled>No GLs found</option>';
                        } else {
                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.BudgetGLID;
                                option.textContent = item.Description.trim();

                                // Restore selections if this is the initial load or if previously selected
                                if (initiallySelectedGLs.includes(item.BudgetGLID.toString())) {
                                    option.selected = true;
                                }

                                glTypesSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading GL Types:', error);
                        glTypesSelect.innerHTML = '<option disabled>Error loading GLs</option>';
                    });
            });

            // Trigger GL Types loading on page load if sub-type is already selected
            if (subTypeSelect.value && subTypeSelect.selectedIndex > 0) {
                const selectedOption = subTypeSelect.options[subTypeSelect.selectedIndex];
                const typeId = selectedOption.dataset.typeId;

                if (typeId) {
                    // Don't show loading indicator since we already have GLs loaded
                    fetch(`/budget/budgetlinemapping/gl-types/${typeId}`)
                        .then(response => response.json())
                        .then(data => {
                            glTypesSelect.innerHTML = '';

                            data.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.BudgetGLID;
                                option.textContent = item.Description.trim();

                                // Restore previous selections using the initially selected values
                                if (initiallySelectedGLs.includes(item.BudgetGLID.toString())) {
                                    option.selected = true;
                                }

                                glTypesSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error loading GL Types on page load:', error);
                        });
                }
            }
        });
    </script>
@endsection
