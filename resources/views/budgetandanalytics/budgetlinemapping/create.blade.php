@extends('layouts.app')
@section('title', 'New Budget Line & GL Mapping')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some errors with your submission:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">➕ Add Budget Line & GL Mapping</div>
        <div class="card-body">
            <p class="text-muted">
                Use this form to create a new Budget Line. A Budget Line defines a specific category under your budget,
                which can be linked to CBS GL accounts and optionally to product types if it's projection-driven.
                You can select multiple GL accounts and, if applicable, associate relevant product types to help guide
                future projections and allocations under this line.
            </p>
            <form action="{{ route('budgetlinemapping.store') }}" method="POST">
                @csrf
                @method('POST')
                <div class="row">
                    <!-- 🧾 Budget Line Entry -->
                    <div class="mb-3">
                        <label class="form-label">Budget Line Category</label>
                        <select class="form-select" name="BudgetLineCategoryID" required>
                            <option selected disabled>-- Select Budget Line Category --</option>
                            @foreach ($budgetCategories as $category)
                                <option value="{{ $category->Id }}">{{ $category->CategoryName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Budget Line Name</label>
                        <input type="text" class="form-control" name="LineName"
                               placeholder="e.g. Interest Income, Loan Fees" required>
                        @error('LineName')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="DepartmentID" required>
                            <option selected disabled>-- Select Department --</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->Id }}">{{ $department->Name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">GL Account Type</label>
                        <select class="form-select" name="GLAccountTypeID" id="glAccountTypeSelect" required>
                            <option selected disabled>-- Select Account type --</option>
                            @foreach ($glAccountTypes as $item)
                                <option value="{{ $item->Value }}">{{ $item->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">GL Sub-Type</label>
                        <select class="form-select" name="GLAccountSubTypeID" id="glAccountSubTypeSelect" required>
                            <option selected disabled>-- Select Sub-Type --</option>
                            {{-- Options will be loaded dynamically --}}
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="2" name="Description"
                                  placeholder="Describe this budget line..."></textarea>
                        @error('Description')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 🔗 CBS GL Mapping -->
                    <h6>🔗 CBS GL Accounts (Multiple)</h6>
                    <div class="mb-3">
                        <label class="form-label">Select CBS GLs</label>
                        <select multiple class="form-select" name="GLS[]" required>
                        @foreach ($gls as $item)
                            <option value="{{ $item->Id }}">GL00{{ $item->Id }} - {{ $item->Description }}</option>
                        @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple GLs.</div>
                    </div>

                    <!-- ❓ Is Projection Product Driven -->
                    <div class="mb-3">
                        <label for="IsProductDriven" class="form-label">Is this Budget Line Product Driven?</label>
                        <select class="form-select" id="IsProductDriven" name="IsProductDriven" required>
                            <option value="" selected disabled>-- Select Option --</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <!-- 🔗 Product Type Mapping -->
                    <div class="mb-3" id="productTypeSection" style="display: none;">
                        <h6>🔗 Products (Multiple)</h6>
                        <label class="form-label">Select Product </label>
                        <select multiple class="form-select" name="ProductTypes[]">
                            @foreach ($productTypes as $type)
                                <option value="{{ $type->Id }}">{{ $type->Description }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple Product Types.</div>
                    </div>

                    <!-- 🔗 ERP GL Mapping -->
                    {{-- <div class="mb-3">
                      <label class="form-label">ERP GL Account (Optional)</label>
                      <select class="form-select">
                        <option selected disabled>-- Select ERP GL --</option>
                        <option value="ERP001">ERP001 - Interest Revenue</option>
                        <option value="ERP002">ERP002 - Other Income</option>
                      </select>
                    </div> --}}

                    <!-- 🔘 Primary Flag -->
                    {{-- <div class="form-check mb-3">
                      <input class="form-check-input" name="IsDefault" type="checkbox" id="primaryCheck">
                      <label class="form-check-label" for="primaryCheck">
                        Mark as Primary Mapping
                      </label>
                    </div> --}}
                </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success"
                    onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Submitting...'; this.form.submit(); }">
                💾 Save Budget Line & Mapping
            </button>
        </div>
            </form>
    </div>
</div>



    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const IsProductDriven = document.getElementById('IsProductDriven');
            const productTypeSection = document.getElementById('productTypeSection');
            const typeSelect = document.getElementById('glAccountTypeSelect');
            const subTypeSelect = document.getElementById('glAccountSubTypeSelect');

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
                fetch(`/budget/budgetlinemapping/gl-subtypes/${typeId}`)
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
