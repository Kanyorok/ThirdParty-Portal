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
                        <div class="col-md-4">
                            <label class="form-label">From Branch</label>
                            <input type="hidden" id="FromBranch" name="FromBranch" value="{{ $fromBranch->Id }}">
                            <input type="text" class="form-control" value="{{ $fromBranch->Name }}" readonly>
                        </div>
                        </div>

                        <div class="col-md-6">
                            <label for="ToBranch" class="form-label">To Branch</label>
                            <select name="ToBranch" id="ToBranch" class="form-select" required>
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $branch)
                                    <option
                                        value="{{ $branch->Id }}" {{ $item->ToBranch == $branch->Id ? 'selected' : '' }}>
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
                            <tr data-row-index="{{ $index }}">
                                <td>
                                    <select name="items[{{ $index }}][Category]" class="form-select category" required
                                            data-selected="{{ $reqItem->item->category->ParentId ? $reqItem->item->category->parent->Id : $reqItem->item->category->Id }}">
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $cat)
                                            @if($cat->ParentId === null)
                                                <option value="{{ $cat->Id }}"
                                                    {{ ($reqItem->item->category->ParentId ? $reqItem->item->category->parent->Id : $reqItem->item->category->Id) == $cat->Id ? 'selected' : '' }}>
                                                    {{ $cat->Name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $index }}][Subcategory]" class="form-select subcategory"
                                            data-selected="{{ $reqItem->item->category->ParentId ? $reqItem->item->category->Id : '' }}">
                                        <option value="">-- Select Subcategory --</option>
                                        @if($reqItem->item->category->ParentId)
                                            <option value="{{ $reqItem->item->category->Id }}" selected>
                                                {{ $reqItem->item->category->Name }}
                                            </option>
                                        @endif
                                    </select>
                                </td>

                                <td>
                                    <select name="items[{{ $index }}][Item]"
                                            class="form-select item" required
                                            data-selected="{{ $reqItem->Item }}"
                                            data-preserved-name="{{ $reqItem->item->ItemName }}">
                                        <option value="{{ $reqItem->Item }}" selected>
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

@push('scripts')
    <script>
        $(document).ready(function () {
            let rowIdx = {{ $item->items->count() }};

            // Initialize all existing rows sequentially
            $('#items-table tr').each(function (index) {
                let row = $(this);
                // Add a small delay to prevent race conditions
                setTimeout(() => {
                    initializeRow(row);
                }, index * 100); // Stagger initialization
            });

            function initializeRow(row) {
                let branchId = $('#FromBranch').val();
                let categorySelect = row.find('.category');
                let subcategorySelect = row.find('.subcategory');
                let itemSelect = row.find('.item');

                // Get the stored values from data attributes or current selections
                let selectedCategory = categorySelect.data('selected') || categorySelect.val();
                let selectedSubcategory = subcategorySelect.data('selected') || subcategorySelect.val();
                let selectedItem = itemSelect.data('selected') || itemSelect.val();

                console.log('Initializing row:', {selectedCategory, selectedSubcategory, selectedItem});

                // Load categories for this row
                loadCategories(branchId, row, selectedCategory, selectedSubcategory, selectedItem);
            }

            function loadCategories(branchId, row, selectedCategory = null, selectedSubcategory = null, selectedItem = null) {
                if (!branchId) {
                    console.log('No branch ID provided');
                    return;
                }

                console.log('Loading categories for branch:', branchId);

                $.get("{{ route('inventory.get-categories-by-branch') }}", {
                    from_branch_id: branchId
                }, function (res) {
                    let categorySelect = row.find('.category');
                    let currentVal = selectedCategory || categorySelect.data('selected');

                    categorySelect.empty().append('<option value="">-- Select Category --</option>');

                    if (res.categories && res.categories.length > 0) {
                        res.categories.forEach(cat => {
                            let selected = currentVal == cat.Id ? 'selected' : '';
                            categorySelect.append(`<option value="${cat.Id}" ${selected}>${cat.Name}</option>`);
                        });

                        // If we have a selected category, load its subcategories
                        if (currentVal) {
                            console.log('Loading subcategories for category:', currentVal);
                            loadSubcategories(branchId, currentVal, row, selectedSubcategory, selectedItem);
                        } else {
                            // Clear dependent fields if no category selected
                            row.find('.subcategory').empty().append('<option value="">-- Select Subcategory --</option>');
                            row.find('.item').empty().append('<option value="">-- Select Item --</option>');
                            row.find('.item-code').val('');
                            row.find('.item-uom').val('');
                        }
                    } else {
                        categorySelect.append('<option value="">No categories available</option>');
                    }
                }).fail(function (xhr, status, error) {
                    console.error('Error loading categories:', error);
                });
            }

            function loadSubcategories(branchId, categoryId, row, selectedSubcategory = null, selectedItem = null) {
                if (!branchId || !categoryId) {
                    console.log('Missing branch or category ID');
                    return;
                }

                console.log('Loading subcategories for category:', categoryId);

                $.get("{{ route('inventory.get-subcategories-by-branch-and-category') }}", {
                    from_branch_id: branchId,
                    category_id: categoryId
                }, function (res) {
                    let subSelect = row.find('.subcategory');
                    let currentVal = selectedSubcategory || subSelect.data('selected');

                    subSelect.empty().append('<option value="">-- Select Subcategory --</option>');

                    if (res.subcategories && res.subcategories.length > 0) {
                        res.subcategories.forEach(sub => {
                            let selected = currentVal == sub.Id ? 'selected' : '';
                            subSelect.append(`<option value="${sub.Id}" ${selected}>${sub.Name}</option>`);
                        });

                        // If we have a selected subcategory, load its items
                        if (currentVal) {
                            console.log('Loading items for subcategory:', currentVal);
                            loadItems(branchId, categoryId, currentVal, row, selectedItem);
                        } else {
                            // If no subcategory selected but we have category, load items from category
                            console.log('Loading items for category (no subcategory):', categoryId);
                            loadItems(branchId, categoryId, null, row, selectedItem);
                        }
                    } else {
                        console.log('No subcategories found, loading items for category:', categoryId);
                        loadItems(branchId, categoryId, null, row, selectedItem);
                    }
                }).fail(function (xhr, status, error) {
                    console.error('Error loading subcategories:', error);
                });
            }

            function loadItems(branchId, categoryId, subcategoryId, row, selectedItem = null) {
                let itemSelect = row.find('.item');
                let currentVal = selectedItem || itemSelect.data('selected') || itemSelect.val();

                // itemSelect.empty().append('<option value="">-- Select Item --</option>');

                $.get("{{ route('inventory.get-items') }}", {
                    from_branch_id: branchId,
                    category_id: categoryId,
                    subcategory_id: subcategoryId
                }, function (res) {
                    let found = false;

                    if (res.items && res.items.length > 0) {
                        res.items.forEach(item => {
                            let selected = (currentVal == item.Id) ? 'selected' : '';
                            if (selected) found = true;
                            itemSelect.append(`<option value="${item.Id}" ${selected}>${item.ItemName}</option>`);
                        });
                    }

                    // If pre-saved item was not in the response, keep it
                    if (currentVal && !found) {
                        let preservedText = itemSelect.data('preserved-name')
                            || row.find('.item option[selected]').text()
                            || 'Previously selected item';

                        itemSelect.append(`<option value="${currentVal}" selected>${preservedText}</option>`);
                    }

                    // Restore selection
                    if (currentVal) {
                        itemSelect.val(currentVal);

                        // 🔑 Load the item details immediately (fixes empty Item Code + UOM)
                        loadItemDetails(currentVal, row);
                    }
                }).fail(function (xhr, status, error) {
                    console.error('Error loading items:', error);

                    if (currentVal) {
                        let preservedText = itemSelect.data('preserved-name')
                            || row.find('.item option[selected]').text()
                            || 'Previously selected item';

                        itemSelect.html(`<option value="${currentVal}" selected>${preservedText}</option>`);
                        // 🔑 Also load details even on fail
                        loadItemDetails(currentVal, row);
                    } else {
                        itemSelect.html('<option value="">Error loading items</option>');
                    }
                });
            }


            function loadItemDetails(itemId, row) {
                if (!itemId) {
                    console.log('No item ID provided for details');
                    return;
                }

                console.log('Loading item details for:', itemId);

                $.get("{{ url('inventory/items/code') }}/" + itemId, function (res) {
                    console.log('Item details response:', res);
                    if (res.item_code) {
                        row.find('.item-code').val(res.item_code);
                    } else {
                        row.find('.item-code').val('N/A');
                    }
                    if (res.item_uom) {
                        row.find('.item-uom').val(res.item_uom);
                    } else {
                        row.find('.item-uom').val('N/A');
                    }
                }).fail(function (xhr, status, error) {
                    console.error('Error loading item details:', error);
                    row.find('.item-code').val('Error');
                    row.find('.item-uom').val('Error');
                });
            }

            // Event handlers - use more specific selectors
            $(document).on('change', '.category', function () {
                let row = $(this).closest('tr');
                let branchId = $('#FromBranch').val();
                let categoryId = $(this).val();

                console.log('Category changed to:', categoryId);

                // Only clear dependent fields if category actually changed
                if (categoryId) {
                    row.find('.subcategory').empty().append('<option value="">-- Select Subcategory --</option>');
                    row.find('.item').empty().append('<option value="">-- Select Item --</option>');
                    row.find('.item-code').val('');
                    row.find('.item-uom').val('');

                    loadSubcategories(branchId, categoryId, row);
                } else {
                    // Clear everything if no category selected
                    row.find('.subcategory').empty().append('<option value="">-- Select Subcategory --</option>');
                    row.find('.item').empty().append('<option value="">-- Select Item --</option>');
                    row.find('.item-code').val('');
                    row.find('.item-uom').val('');
                }
            });

            $(document).on('change', '.subcategory', function () {
                let row = $(this).closest('tr');
                let branchId = $('#FromBranch').val();
                let categoryId = row.find('.category').val();
                let subcategoryId = $(this).val();

                console.log('Subcategory changed to:', subcategoryId);

                if (categoryId && subcategoryId) {
                    row.find('.item').empty().append('<option value="">-- Select Item --</option>');
                    row.find('.item-code').val('');
                    row.find('.item-uom').val('');

                    loadItems(branchId, categoryId, subcategoryId, row);
                }
            });

            $(document).on('change', '.item', function () {
                let row = $(this).closest('tr');
                let itemId = $(this).val();
                console.log('Item changed to:', itemId);
                loadItemDetails(itemId, row);
            });

            // Add new row
            $('#add-row').on('click', function () {
                let newRow = `
            <tr data-row-index="${rowIdx}">
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

                // Initialize the new row after a brief delay
                setTimeout(() => {
                    let branchId = $('#FromBranch').val();
                    loadCategories(branchId, $('#items-table tr').last());
                }, 100);

                rowIdx++;
            });

            // Remove row
            $(document).on('click', '.remove-row', function () {
                if ($('#items-table tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    alert('At least one item is required.');
                }
            });
        });
    </script>
@endpush
