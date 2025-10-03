@extends('layouts.app')

@section('title', 'Edit Interbranch Requisition')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4" style="background-color:#add8e6;">
            <h4 class="mb-0">Edit Interbranch Requisition</h4>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('interbranch-requisitions.update', $requisition->id) }}">
                @csrf
                @method('PUT')

                <!-- From Branch -->
                <div class="mb-3">
                    <label for="from_branch_id" class="form-label">From Branch</label>
                    <select name="from_branch_id" id="from_branch_id" class="form-select" required>
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $requisition->from_branch_id == $branch->id ? 'selected' : '' }}>
                                {{ $branch->BranchName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- To Branch -->
                <div class="mb-3">
                    <label for="to_branch_id" class="form-label">To Branch</label>
                    <select name="to_branch_id" id="to_branch_id" class="form-select" required>
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $requisition->to_branch_id == $branch->id ? 'selected' : '' }}>
                                {{ $branch->BranchName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Items Table -->
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width: 25%">Category</th>
                            <th style="width: 25%">Subcategory</th>
                            <th style="width: 25%">Item</th>
                            <th style="width: 15%">Quantity</th>
                            <th style="width: 10%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="items-table">
                        @foreach($requisition->items as $item)
                            <tr>
                                <!-- Category -->
                                <td>
                                    <select name="categories[]" class="form-select category" required data-selected="{{ $item->category_id }}">
                                        <option value="{{ $item->category_id }}" selected>{{ $item->category->CategoryName ?? 'Loading...' }}</option>
                                    </select>
                                </td>

                                <!-- Subcategory -->
                                <td>
                                    <select name="subcategories[]" class="form-select subcategory" required data-selected="{{ $item->subcategory_id }}">
                                        <option value="{{ $item->subcategory_id }}" selected>{{ $item->subcategory->SubcategoryName ?? 'Loading...' }}</option>
                                    </select>
                                </td>

                                <!-- Item -->
                                <td>
                                    <select name="items[]" class="form-select item" required data-selected="{{ $item->item_id }}">
                                        <option value="{{ $item->item_id }}" selected>{{ $item->item->ItemName ?? 'Loading...' }}</option>
                                    </select>
                                </td>

                                <!-- Quantity -->
                                <td>
                                    <input type="number" name="quantities[]" class="form-control" value="{{ $item->Quantity }}" required min="1">
                                </td>

                                <!-- Remove -->
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Add Item -->
                <button type="button" id="add-row" class="btn btn-primary mb-3">+ Add Item</button>

                <!-- Submit -->
                <div>
                    <button type="submit" class="btn btn-success">Update Requisition</button>
                    <a href="{{ route('interbranch-requisitions.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    // -------------------------------
    // Dynamic loaders
    // -------------------------------
    function loadCategories(row, selectedCategory = null) {
        let select = row.find('.category');
        let currentVal = selectedCategory || select.data('selected') || select.val();
        let currentText = select.find('option:selected').text();

        select.html(`<option value="${currentVal}" selected>${currentText || '-- Loading --'}</option>`);

        $.get("{{ route('inventory.get-categories') }}", function (res) {
            let found = false;
            let options = '<option value="">-- Select Category --</option>';

            res.categories.forEach(cat => {
                let selected = (currentVal == cat.Id) ? 'selected' : '';
                if (selected) found = true;
                options += `<option value="${cat.Id}" ${selected}>${cat.CategoryName}</option>`;
            });

            if (currentVal && !found) {
                options += `<option value="${currentVal}" selected>${currentText}</option>`;
            }

            select.html(options);
        });
    }

    function loadSubcategories(categoryId, row, selectedSubcategory = null) {
        let select = row.find('.subcategory');
        let currentVal = selectedSubcategory || select.data('selected') || select.val();
        let currentText = select.find('option:selected').text();

        select.html(`<option value="${currentVal}" selected>${currentText || '-- Loading --'}</option>`);

        $.get("{{ route('inventory.get-subcategories') }}", { category_id: categoryId }, function (res) {
            let found = false;
            let options = '<option value="">-- Select Subcategory --</option>';

            res.subcategories.forEach(sub => {
                let selected = (currentVal == sub.Id) ? 'selected' : '';
                if (selected) found = true;
                options += `<option value="${sub.Id}" ${selected}>${sub.SubcategoryName}</option>`;
            });

            if (currentVal && !found) {
                options += `<option value="${currentVal}" selected>${currentText}</option>`;
            }

            select.html(options);
        });
    }

    function loadItems(branchId, categoryId, subcategoryId, row, selectedItem = null) {
        let select = row.find('.item');
        let currentVal = selectedItem || select.data('selected') || select.val();
        let currentText = select.find('option:selected').text();

        select.html(`<option value="${currentVal}" selected>${currentText || '-- Loading --'}</option>`);

        $.get("{{ route('inventory.get-items') }}", {
            from_branch_id: branchId,
            category_id: categoryId,
            subcategory_id: subcategoryId
        }, function (res) {
            let found = false;
            let options = '<option value="">-- Select Item --</option>';

            res.items.forEach(item => {
                let selected = (currentVal == item.Id) ? 'selected' : '';
                if (selected) found = true;
                options += `<option value="${item.Id}" ${selected}>${item.ItemName}</option>`;
            });

            if (currentVal && !found) {
                options += `<option value="${currentVal}" selected>${currentText}</option>`;
            }

            select.html(options);
        });
    }

    // -------------------------------
    // Initial load
    // -------------------------------
    $('#items-table tr').each(function () {
        let row = $(this);
        loadCategories(row);
        loadSubcategories(row.find('.category').val(), row);
        loadItems($('#from_branch_id').val(), row.find('.category').val(), row.find('.subcategory').val(), row);
    });

    // -------------------------------
    // Add new row
    // -------------------------------
    $('#add-row').click(function () {
        let newRow = `
        <tr>
            <td><select name="categories[]" class="form-select category" required><option value="">-- Select Category --</option></select></td>
            <td><select name="subcategories[]" class="form-select subcategory" required><option value="">-- Select Subcategory --</option></select></td>
            <td><select name="items[]" class="form-select item" required><option value="">-- Select Item --</option></select></td>
            <td><input type="number" name="quantities[]" class="form-control" required min="1"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
        </tr>`;
        $('#items-table').append(newRow);
        let row = $('#items-table tr').last();
        loadCategories(row);
    });

    // -------------------------------
    // Remove row
    // -------------------------------
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });

    // -------------------------------
    // Cascading events
    // -------------------------------
    $(document).on('change', '.category', function () {
        let row = $(this).closest('tr');
        loadSubcategories($(this).val(), row);
        row.find('.item').html('<option value="">-- Select Item --</option>');
    });

    $(document).on('change', '.subcategory', function () {
        let row = $(this).closest('tr');
        loadItems($('#from_branch_id').val(), row.find('.category').val(), $(this).val(), row);
    });

    $('#from_branch_id').change(function () {
        $('#items-table tr').each(function () {
            let row = $(this);
            loadItems($('#from_branch_id').val(), row.find('.category').val(), row.find('.subcategory').val(), row);
        });
    });
});
</script>
@endsection
