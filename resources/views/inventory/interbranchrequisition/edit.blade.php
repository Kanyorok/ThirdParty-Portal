@extends('layouts.app')

@section('title', 'Edit Inter-Branch Requisition')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

<div class="container mt-4">
    <h4 class="fw-bold mb-3">Edit Inter-Branch Requisition</h4>

        <form action="{{ route('interbranchrequisition.update', $item->Id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">✏️ Edit Stock Request</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Requesting Branch</label>
                            <select name="FromBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option
                                        value="{{ $branch->Id }}" {{ old('FromBranch', $item->FromBranch) == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Branch</label>
                            <select name="ToBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option
                                        value="{{ $branch->Id }}" {{ old('ToBranch', $item->ToBranch) == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="CreatedOn" class="form-control"
                                   value="{{ old('CreatedOn', \Carbon\Carbon::parse($item->CreatedOn)->format('Y-m-d')) }}"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">➕ Add Item</button>
                    </div>

                <div id="itemsContainer">
                    @foreach($item->items as $index => $line)
                        @php
                            $selectedItem = $line->item;
                            $selectedSubcategory = $selectedItem->category ?? null;
                            $selectedCategory = $selectedSubcategory && $selectedSubcategory->parent ? $selectedSubcategory->parent : null;
                            $selectedCategoryId = $selectedCategory->Id ?? '';
                            $selectedSubcategoryId = $selectedSubcategory->Id ?? '';
                        @endphp
                        <div class="card mb-3 item-entry">
                            <div class="card-body border">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label">Parent Category</label>
                                        <select name="items[{{ $index }}][Category]" class="form-select category-select" required>
                                            <option value="">-- Select Category --</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->Id }}" {{ $selectedCategoryId == $category->Id ? 'selected' : '' }}>
                                                    {{ $category->Name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Category</label>
                                        <select name="items[{{ $index }}][Subcategory]" class="form-select subcategory-select" data-initial="{{ $selectedSubcategoryId }}">
                                            <option value="">-- Select Subcategory --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Item</label>
                                        <select name="items[{{ $index }}][Item]" class="form-select item-select" data-initial="{{ $selectedItem->Id ?? '' }}" required>
                                            <option value="">-- Select Item --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Code</label>
                                        <input type="text" name="items[{{ $index }}][ItemCode]" class="form-control item-code" value="{{ $selectedItem->ItemCode ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">UOM</label>
                                        <input type="text" name="items[{{ $index }}][UOM]" class="form-control uom" value="{{ $selectedItem->uom->Name ?? '' }}" readonly>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Qty</label>
                                        <input type="number" name="items[{{ $index }}][RequestedQty]" class="form-control" value="{{ $line->RequestedQty }}" min="1" required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Remarks</label>
                                        <input type="text" name="items[{{ $index }}][Remarks]" class="form-control" value="{{ $line->Remarks }}">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm remove-item-btn">✖</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success">Update Requisition</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

<template id="itemTemplate">
    <div class="card mb-3 item-entry">
        <div class="card-body border">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="items[__INDEX__][Category]" class="form-select category-select" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->Id }}">{{ $category->Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subcategory</label>
                    <select name="items[__INDEX__][Subcategory]" class="form-select subcategory-select">
                        <option value="">-- Select Subcategory --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Item</label>
                    <select name="items[__INDEX__][Item]" class="form-select item-select" required>
                        <option value="">-- Select Item --</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Code</label>
                    <input type="text" name="items[__INDEX__][ItemCode]" class="form-control item-code" readonly>
                </div>
                <div class="col-md-1">
                    <label class="form-label">UOM</label>
                    <input type="text" name="items[__INDEX__][UOM]" class="form-control uom" readonly>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Qty</label>
                    <input type="number" name="items[__INDEX__][RequestedQty]" class="form-control" value="1" min="1" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="items[__INDEX__][Remarks]" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">✖</button>
                </div>
            </div>
        </div>
    </div>
</template>

    @push('scripts')
        <script>
            let itemCounter = {{ count($item->items) }};

    function populateSubcategories(categorySelect, subcategorySelect, selectedSubcat = null, callback = null) {
        const categoryId = categorySelect.value;
        subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
        subcategorySelect.disabled = false; // Enable subcategory select
        if (!categoryId) {
            subcategorySelect.disabled = true; // Disable if no category selected
            return callback?.();
        }

        fetch(`/inventory/get-subcategories?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(subcat => {
                    const option = new Option(subcat.Name, subcat.Id);
                    if (selectedSubcat == subcat.Id) option.selected = true;
                    subcategorySelect.add(option);
                });
                callback?.();
            })
            .catch(error => {
                console.error('Error fetching subcategories:', error);
                subcategorySelect.disabled = true; // Disable on error
            });
    }

    function populateItems(entry, selectedItem = null) {
        const categorySelect = entry.querySelector('.category-select');
        const subcategorySelect = entry.querySelector('.subcategory-select');
        const itemSelect = entry.querySelector('.item-select');

        const categoryId = categorySelect.value;
        const subcategoryId = subcategorySelect.value;

        itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
        itemSelect.disabled = false; // Enable item select

        let fetchUrl = '';
        if (subcategoryId) {
            fetchUrl = `/inventory/get-items?subcategory_id=${subcategoryId}`;
        } else if (categoryId) {
            fetchUrl = `/inventory/get-items?category_id=${categoryId}`;
        } else {
            itemSelect.disabled = true; // Disable if no category/subcategory selected
            return;
        }

        fetch(fetchUrl)
            .then(response => response.json())
            .then(data => {
                const currentItem = selectedItem || itemSelect.getAttribute('data-initial');
                const hasCurrent = data.some(item => item.Id == currentItem);

                data.forEach(item => {
                    const option = new Option(item.ItemName, item.Id);
                    if (item.Id == currentItem) option.selected = true;
                    itemSelect.add(option);
                });

                if (currentItem && !hasCurrent) {
                    // If the initial item is not in the fetched list (e.g., due to category change),
                    // add it as a selected option.
                    const option = new Option('[Original Item]', currentItem, true, true);
                    itemSelect.add(option);
                }
            })
            .catch(error => {
                console.error('Error fetching items:', error);
                itemSelect.disabled = true; // Disable on error
            });
    }

    function fetchItemCodeAndUOM(itemId, entry) {
        if (!itemId) {
            entry.querySelector('.item-code').value = '';
            entry.querySelector('.uom').value = '';
            return;
        }

        fetch(`/inventory/items/code/${itemId}`)
            .then(response => response.json())
            .then(data => {
                // Corrected property names
                entry.querySelector('.item-code').value = data.item_code ?? 'N/A';
                entry.querySelector('.uom').value = data.item_uom ?? 'N/A';
            })
            .catch(error => {
                console.error('Error fetching item data:', error);
                entry.querySelector('.item-code').value = 'Error';
                entry.querySelector('.uom').value = 'Error';
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.item-entry').forEach(entry => {
            const categorySelect = entry.querySelector('.category-select');
            const subcategorySelect = entry.querySelector('.subcategory-select');
            const itemSelect = entry.querySelector('.item-select');

            const selectedCategory = categorySelect.value;
            const selectedSubcategory = subcategorySelect.getAttribute('data-initial');
            const selectedItem = itemSelect.getAttribute('data-initial');

            // Populate subcategories and then items on load for each existing entry
            if (selectedCategory) {
                populateSubcategories(categorySelect, subcategorySelect, selectedSubcategory, () => {
                    populateItems(entry, selectedItem);
                });
            } else {
                // If no parent category is selected, disable subcategory and item selects
                subcategorySelect.disabled = true;
                itemSelect.disabled = true;
            }

            // Also fetch Item Code and UOM for initially selected items
            if (selectedItem) {
                fetchItemCodeAndUOM(selectedItem, entry);
            }
        });

        document.getElementById('addItemBtn').addEventListener('click', () => {
            const template = document.getElementById('itemTemplate');
            const clone = template.content.cloneNode(true);
            const newEntry = clone.firstElementChild; // Get the .item-entry div

            // Replace __INDEX__ in all names
            newEntry.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace('__INDEX__', itemCounter);
            });

            // Add event listener for remove button
            newEntry.querySelector('.remove-item-btn').addEventListener('click', function () {
                this.closest('.item-entry').remove();
            });

            // Disable subcategory and item dropdowns for newly added rows initially
            newEntry.querySelector('.subcategory-select').disabled = true;
            newEntry.querySelector('.item-select').disabled = true;
            newEntry.querySelector('.item-code').value = '';
            newEntry.querySelector('.uom').value = '';


            document.getElementById('itemsContainer').appendChild(newEntry);
            itemCounter++;
        });

        // Event delegation for dynamically added elements
        document.addEventListener('change', function (e) {
            const entry = e.target.closest('.item-entry');
            if (!entry) return; // Not an event from an item entry

            if (e.target.classList.contains('category-select')) {
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                populateSubcategories(e.target, subcategorySelect, null, () => {
                    itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                    itemSelect.disabled = true; // Disable items until subcategory or category is selected
                    entry.querySelector('.item-code').value = '';
                    entry.querySelector('.uom').value = '';
                });
                // After category changes, repopulate items directly if no subcategory is chosen
                populateItems(entry); // This will load items based on the category if no subcategory is selected
            }

            if (e.target.classList.contains('subcategory-select')) {
                const itemSelect = entry.querySelector('.item-select');
                populateItems(entry); // Pass the entire entry to populateItems
                entry.querySelector('.item-code').value = '';
                entry.querySelector('.uom').value = '';
            }

            if (e.target.classList.contains('item-select')) {
                fetchItemCodeAndUOM(e.target.value, entry);
            }
        });
    });
</script>
@endpush
@endsection
