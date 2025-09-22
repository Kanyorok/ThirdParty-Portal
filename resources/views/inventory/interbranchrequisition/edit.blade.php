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

            <form method="POST" action="{{ route('interbranchrequisition.update', $item->Id) }}">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="FromBranch" class="form-label">From Branch</label>
                        <select name="FromBranch" id="FromBranch" class="form-select" required>
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}" {{ $item->FromBranch == $branch->Id ? 'selected' : '' }}>
                                    {{ $branch->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="ToBranch" class="form-label">To Branch</label>
                        <select name="ToBranch" id="ToBranch" class="form-select" required>
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->Id }}" {{ $item->ToBranch == $branch->Id ? 'selected' : '' }}>
                                    {{ $branch->Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Requisition Items</h5>
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th>Subcategory</th>
                            <th>Item</th>
                            <th>Item Code</th>
                            <th>UOM</th>
                            <th>Requested Qty</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="items-table">
                        @foreach($item->items as $index => $reqItem)
                            <tr>
                                <td>
                                    <select name="items[{{ $index }}][Category]" class="form-select category" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->Id }}" 
                                                {{ $reqItem->item->category->ParentId === null && $reqItem->item->category->Id == $cat->Id ? 'selected' : '' }}>
                                                {{ $cat->Name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $index }}][Subcategory]" class="form-select subcategory">
                                        <option value="">-- Select Subcategory --</option>
                                        @if($reqItem->item->category->ParentId)
                                            <option value="{{ $reqItem->item->category->Id }}" selected>
                                                {{ $reqItem->item->category->Name }}
                                            </option>
                                        @endif
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $index }}][Item]" class="form-select item" required>
                                        <option value="{{ $reqItem->ItemId }}" selected>
                                            {{ $reqItem->item->ItemName }}
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][ItemCode]" 
                                           class="form-control item-code" 
                                           value="{{ $reqItem->item->ItemCode }}" readonly>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][UOM]" 
                                           class="form-control item-uom" 
                                           value="{{ $reqItem->item->uom->Code ?? 'N/A' }}" readonly>
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $index }}][RequestedQty]" 
                                           class="form-control" min="1" 
                                           value="{{ $reqItem->RequestedQty }}" required>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-row">X</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="button" id="add-row" class="btn btn-outline-primary btn-sm">+ Add Item</button>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Update Requisition</button>
                    <a href="{{ route('interbranchrequisition.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- Script Section --}}
@push('scripts')
<script>
$(document).ready(function () {
    let rowIdx = {{ $item->items->count() }};

    function loadCategories(branchId, row) {
        if (!branchId) return;
        $.get("{{ route('inventory.get-categories-by-branch') }}", { from_branch_id: branchId }, function (res) {
            let categorySelect = row.find('.category');
            let currentVal = categorySelect.data('selected');
            categorySelect.empty().append('<option value="">-- Select Category --</option>');
            res.categories.forEach(cat => {
                let selected = currentVal == cat.Id ? 'selected' : '';
                categorySelect.append(`<option value="${cat.Id}" ${selected}>${cat.Name}</option>`);
            });
        });
    }

    function loadSubcategories(branchId, categoryId, row) {
        if (!branchId || !categoryId) return;
        $.get("{{ route('inventory.get-subcategories-by-branch-and-category') }}", {
            from_branch_id: branchId,
            category_id: categoryId
        }, function (res) {
            let subSelect = row.find('.subcategory');
            let currentVal = subSelect.data('selected');
            subSelect.empty().append('<option value="">-- Select Subcategory --</option>');
            res.subcategories.forEach(sub => {
                let selected = currentVal == sub.Id ? 'selected' : '';
                subSelect.append(`<option value="${sub.Id}" ${selected}>${sub.Name}</option>`);
            });
        });
    }

    function loadItems(branchId, categoryId, subcategoryId, row) {
        if (!branchId || !categoryId) return;
        $.get("{{ route('inventory.get-items') }}", {
            from_branch_id: branchId,
            category_id: categoryId,
            subcategory_id: subcategoryId
        }, function (res) {
            let itemSelect = row.find('.item');
            let currentVal = itemSelect.data('selected');
            itemSelect.empty().append('<option value="">-- Select Item --</option>');
            res.items.forEach(item => {
                let selected = currentVal == item.Id ? 'selected' : '';
                itemSelect.append(`<option value="${item.Id}" ${selected}>${item.ItemName}</option>`);
            });
        });
    }

    function loadItemDetails(itemId, row) {
        if (!itemId) return;
        $.get("{{ url('inventory/items/code') }}/" + itemId, function (res) {
            
            row.find('.item-code').val(res.item_code);
            row.find('.item-uom').val(res.item_uom);
        });
    }

    // Branch change reloads categories
    $('#FromBranch').on('change', function () {
        let branchId = $(this).val();
        $('#items-table tr').each(function () {
            loadCategories(branchId, $(this));
        });
    });

    // Cascade handlers
    $(document).on('change', '.category', function () {
        let row = $(this).closest('tr');
        let branchId = $('#FromBranch').val();
        let categoryId = $(this).val();
        loadSubcategories(branchId, categoryId, row);
        row.find('.subcategory').data('selected', ''); // reset
        row.find('.item').empty().append('<option value="">-- Select Item --</option>');
    });

    $(document).on('change', '.subcategory', function () {
        let row = $(this).closest('tr');
        let branchId = $('#FromBranch').val();
        let categoryId = row.find('.category').val();
        let subId = $(this).val();
        loadItems(branchId, categoryId, subId, row);
    });

    $(document).on('change', '.item', function () {
        let row = $(this).closest('tr');
        let itemId = $(this).val();
        loadItemDetails(itemId, row);
    });

    // Add new row
    $('#add-row').on('click', function () {
        let newRow = `
            <tr>
                <td>
                    <select name="items[${rowIdx}][Category]" class="form-select category" required>
                        <option value="">-- Select Category --</option>
                    </select>
                </td>
                <td>
                    <select name="items[${rowIdx}][Subcategory]" class="form-select subcategory">
                        <option value="">-- Select Subcategory --</option>
                    </select>
                </td>
                <td>
                    <select name="items[${rowIdx}][Item]" class="form-select item" required>
                        <option value="">-- Select Item --</option>
                    </select>
                </td>
                <td><input type="text" name="items[${rowIdx}][ItemCode]" class="form-control item-code" readonly></td>
                <td><input type="text" name="items[${rowIdx}][UOM]" class="form-control item-uom" readonly></td>
                <td><input type="number" name="items[${rowIdx}][RequestedQty]" class="form-control" min="1" required></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
            </tr>
        `;
        $('#items-table').append(newRow);
        let branchId = $('#FromBranch').val();
        loadCategories(branchId, $('#items-table tr').last());
        rowIdx++;
    });

    // Remove row
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });
});
</script>
@endpush
