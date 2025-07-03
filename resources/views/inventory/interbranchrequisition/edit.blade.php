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
                            <select name="FromBranch" id="from_branch_select" class="form-select" required>
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
                        {{-- Existing items will be rendered here by Laravel --}}
                        @foreach(old('items', $item->items) as $index => $line)
                            @php
                                // Handle both model objects (from $item->items) and arrays (from old input)
                                $lineObject = (object) $line; // Cast to object for consistent property access
                                $selectedItem = property_exists($lineObject, 'item') ? $lineObject->item : null;
                                $selectedSubcategory = ($selectedItem && property_exists($selectedItem, 'category')) ? $selectedItem->category : null;
                                $selectedCategory = ($selectedSubcategory && property_exists($selectedSubcategory, 'parent')) ? $selectedSubcategory->parent : null;

                                $selectedCategoryId = $selectedCategory->Id ?? (property_exists($lineObject, 'Category') ? $lineObject->Category : '');
                                $selectedSubcategoryId = $selectedSubcategory->Id ?? (property_exists($lineObject, 'Subcategory') ? $lineObject->Subcategory : '');
                                $selectedItemId = $selectedItem->Id ?? (property_exists($lineObject, 'Item') ? $lineObject->Item : '');
                            @endphp
                            <div class="card mb-3 item-entry" data-index="{{ $index }}">
                                <div class="card-body border">
                                    <div class="row g-3 align-items-end">
                                        {{-- Hidden input for existing item ID, or empty for new items from old input --}}
                                        <input type="hidden" name="items[{{ $index }}][Id]"
                                               value="{{ $lineObject->Id ?? '' }}">

                                        <div class="col-md-2">
                                            <label class="form-label">Parent Category</label>
                                            <select name="items[{{ $index }}][Category]"
                                                    class="form-select category-select"
                                                    data-initial="{{ $selectedCategoryId }}" required>
                                                <option value="">-- Select Category --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Category</label>
                                            <select name="items[{{ $index }}][Subcategory]"
                                                    class="form-select subcategory-select"
                                                    data-initial="{{ $selectedSubcategoryId }}">
                                                <option value="">-- Select Subcategory --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Item</label>
                                            <select name="items[{{ $index }}][Item]" class="form-select item-select"
                                                    data-initial="{{ $selectedItemId }}" required>
                                                <option value="">-- Select Item --</option>
                                            </select>
                                            <input type="hidden" name="items[{{ $index }}][item_name]"
                                                   class="item-name-hidden"
                                                   value="{{ $selectedItem->ItemName ?? (property_exists($lineObject, 'item_name') ? $lineObject->item_name : '') }}">
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">Code</label>
                                            <input type="text" name="items[{{ $index }}][ItemCode]"
                                                   class="form-control item-code"
                                                   value="{{ $selectedItem->ItemCode ?? (property_exists($lineObject, 'ItemCode') ? $lineObject->ItemCode : '') }}"
                                                   readonly>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">UOM</label>
                                            <input type="text" class="form-control item-uom"
                                                   value="{{ $selectedItem->UOM ?? (property_exists($lineObject, 'item_uom') ? $lineObject->item_uom : '') }}"
                                                   readonly>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">Qty</label>
                                            <input type="number" name="items[{{ $index }}][RequestedQty]"
                                                   class="form-control item-qty"
                                                   value="{{ old("items.{$index}.RequestedQty", $lineObject->RequestedQty) }}"
                                                   min="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Remarks</label>
                                            <input type="text" name="items[{{ $index }}][Remarks]" class="form-control"
                                                   value="{{ old("items.{$index}.Remarks", $lineObject->Remarks) }}">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger btn-sm remove-item-btn">✖
                                            </button>
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
                    {{-- No hidden ID for new items initially, it will be added when they are saved --}}
                    <input type="hidden" name="items[__INDEX__][Id]" value="">
                    <div class="col-md-2">
                        <label class="form-label">Parent Category</label>
                        <select name="items[__INDEX__][Category]" class="form-select category-select" required>
                            <option value="">-- Select Category --</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="items[__INDEX__][Subcategory]" class="form-select subcategory-select">
                            <option value="">-- Select Subcategory --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item</label>
                        <select name="items[__INDEX__][Item]" class="form-select item-select" required>
                            <option value="">-- Select Item --</option>
                        </select>
                        <input type="hidden" name="items[__INDEX__][item_name]" class="item-name-hidden">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Code</label>
                        <input type="text" name="items[__INDEX__][ItemCode]" class="form-control item-code" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">UOM</label>
                        <input type="text" class="form-control item-uom" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Qty</label>
                        <input type="number" name="items[__INDEX__][RequestedQty]" class="form-control item-qty"
                               value="1" min="1" required>
                    </div>
                    <div class="col-md-2">
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
            // Initialize counter with the maximum index from existing items (or old input) plus one.
            // This ensures new items added get unique, non-conflicting array keys.
            let itemCounter = {{ count(old('items', $item->items)) > 0 ? (collect(old('items', $item->items))->keys()->max() + 1) : 0 }};

            // Function to populate categories based on selected branch's stock
            function populateCategoriesByBranch(entry, selectedCategoryId = null, callback = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('from_branch_select');
                const fromBranchId = fromBranchSelect.value;

                categorySelect.innerHTML = '<option value="">-- Select Category --</option>'; // Always start with default option
                categorySelect.disabled = true; // Disable until loaded
                subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                subcategorySelect.disabled = true;
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                itemSelect.disabled = true;
                entry.querySelector('.item-code').value = '';
                entry.querySelector('.item-uom').value = '';
                entry.querySelector('.item-name-hidden').value = '';

                if (!fromBranchId) {
                    categorySelect.innerHTML = '<option value="">Select Requesting Branch First</option>';
                    return callback?.();
                }

                fetch(`/inventory/get-categories-by-branch?from_branch_id=${fromBranchId}`)
                    .then(response => response.json())
                    .then(data => {
                        categorySelect.disabled = false;
                        if (data.categories.length === 0) {
                            categorySelect.innerHTML = `<option value="">${data.message || '-- No Categories Available --'}</option>`;
                            categorySelect.disabled = true;
                        } else {
                            data.categories.forEach(cat => {
                                const option = new Option(cat.Name, cat.Id);
                                if (selectedCategoryId == cat.Id) option.selected = true;
                                categorySelect.add(option);
                            });
                        }
                        callback?.();
                    })
                    .catch(error => {
                        console.error('Error fetching categories by branch:', error);
                        categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                        categorySelect.disabled = true;
                        callback?.();
                    });
            }

            // Function to populate subcategories by branch and category
            function populateSubcategoriesByBranchAndCategory(entry, selectedSubcatId = null, callback = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('from_branch_select');

                const categoryId = categorySelect.value;
                const fromBranchId = fromBranchSelect.value;

                subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                subcategorySelect.disabled = true;
                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                itemSelect.disabled = true;
                entry.querySelector('.item-code').value = '';
                entry.querySelector('.item-uom').value = '';
                entry.querySelector('.item-name-hidden').value = '';

                if (!categoryId || !fromBranchId) {
                    return callback?.();
                }

                fetch(`/inventory/get-subcategories-by-branch-and-category?from_branch_id=${fromBranchId}&category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        subcategorySelect.disabled = false;
                        if (data.subcategories.length === 0) {
                            subcategorySelect.innerHTML = `<option value="">${data.message || '-- No Subcategories Available --'}</option>`;
                            subcategorySelect.disabled = true;
                            // If no subcategories, load items directly from the parent category
                            populateItemsByBranchAndCategoryOrSubcategory(entry, null, categoryId);
                        } else {
                            data.subcategories.forEach(subcat => {
                                const option = new Option(subcat.Name, subcat.Id);
                                if (selectedSubcatId == subcat.Id) option.selected = true;
                                subcategorySelect.add(option);
                            });
                            // After populating subcategories, clear and disable item select until a subcategory is chosen
                            itemSelect.innerHTML = '<option value="">Select Subcategory</option>';
                            itemSelect.disabled = true;
                        }
                        callback?.();
                    })
                    .catch(error => {
                        console.error('Error fetching subcategories by branch and category:', error);
                        subcategorySelect.innerHTML = '<option value="">Error loading subcategories</option>';
                        subcategorySelect.disabled = true;
                        callback?.();
                    });
            }

            // Function to populate items based on category/subcategory and fromBranchId for stock check
            function populateItemsByBranchAndCategoryOrSubcategory(entry, selectedItemId = null, fallbackCategoryId = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('from_branch_select');

                const categoryId = fallbackCategoryId || categorySelect.value;
                const subcategoryId = subcategorySelect.value;
                const fromBranchId = fromBranchSelect.value;

                itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                itemSelect.disabled = true;

                entry.querySelector('.item-code').value = '';
                entry.querySelector('.item-uom').value = '';
                entry.querySelector('.item-name-hidden').value = '';

                let fetchUrl = '';
                if (fromBranchId && (subcategoryId || categoryId)) {
                    fetchUrl = `/inventory/get-items?from_branch_id=${fromBranchId}`;
                    if (subcategoryId) {
                        fetchUrl += `&subcategory_id=${subcategoryId}`;
                    } else if (categoryId) {
                        fetchUrl += `&category_id=${categoryId}`;
                    }
                } else {
                    itemSelect.innerHTML = '<option value="">Select Category/Subcategory</option>';
                    return;
                }

                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(data => {
                        itemSelect.disabled = false;
                        let hasInitialSelectionInNewList = false;

                        if (data.items.length === 0) {
                            itemSelect.innerHTML = `<option value="">${data.message || '-- No Items Available --'}</option>`;
                            itemSelect.disabled = true;
                        } else {
                            data.items.forEach(item => {
                                const option = new Option(item.ItemName, item.Id);
                                if (item.Id == selectedItemId) {
                                    option.selected = true;
                                    hasInitialSelectionInNewList = true;
                                }
                                itemSelect.add(option);
                            });
                        }

                        // If an item was initially selected (for existing rows or old input) but is not in the current list
                        // (e.g., due to stock changes or branch/category change), re-add it as a selected option
                        // if it's explicitly provided as a `selectedItemId`.
                        if (selectedItemId && !hasInitialSelectionInNewList) {
                            const originalItemName = entry.querySelector('.item-name-hidden').value || '[Original Item]';
                            const option = new Option(originalItemName, selectedItemId, true, true);
                            itemSelect.add(option);
                            fetchItemDetails(selectedItemId, entry); // Fetch details for the re-added item
                        } else if (itemSelect.value) { // If an item is selected from the new list (or was just set)
                            fetchItemDetails(itemSelect.value, entry);
                        } else { // No item selected, clear details
                            entry.querySelector('.item-code').value = '';
                            entry.querySelector('.item-uom').value = '';
                            entry.querySelector('.item-name-hidden').value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching items:', error);
                        itemSelect.innerHTML = '<option value="">Error loading items</option>';
                        itemSelect.disabled = true;
                        entry.querySelector('.item-code').value = 'Error';
                        entry.querySelector('.item-uom').value = 'Error';
                        entry.querySelector('.item-name-hidden').value = '';
                    });
            }

            // Function to fetch Item Code and UOM
            function fetchItemDetails(itemId, entry) {
                if (!itemId) {
                    entry.querySelector('.item-code').value = '';
                    entry.querySelector('.item-uom').value = '';
                    entry.querySelector('.item-name-hidden').value = '';
                    return;
                }

                fetch(`/inventory/items/code/${itemId}`)
                    .then(response => response.json())
                    .then(data => {
                        entry.querySelector('.item-code').value = data.item_code ?? 'N/A';
                        entry.querySelector('.item-uom').value = data.item_uom ?? 'N/A';
                        // Update the hidden item_name field based on the currently selected option's text
                        const selectedText = entry.querySelector('.item-select option:checked')?.textContent;
                        entry.querySelector('.item-name-hidden').value = selectedText || '';
                    })
                    .catch(error => {
                        console.error('Error fetching item data:', error);
                        entry.querySelector('.item-code').value = 'Error';
                        entry.querySelector('.item-uom').value = 'Error';
                        entry.querySelector('.item-name-hidden').value = '';
                    });
            }

            // Function to add a new item row
            function addNewItemRow(values = {}) {
                const template = document.getElementById('itemTemplate');
                const clone = template.content.cloneNode(true);
                const newEntry = clone.firstElementChild;

                // Replace __INDEX__ in all names
                newEntry.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace('__INDEX__', itemCounter);
                });

                // Set data-index attribute for the new entry
                newEntry.setAttribute('data-index', itemCounter);

                // Add event listener for remove button
                newEntry.querySelector('.remove-item-btn').addEventListener('click', function () {
                    this.closest('.item-entry').remove();
                });

                document.getElementById('itemsContainer').appendChild(newEntry);

                // Populate fields based on old values or defaults
                if (values.Id) {
                    newEntry.querySelector('[name$="[Id]"]').value = values.Id;
                }
                if (values.Category) {
                    newEntry.querySelector('.category-select').setAttribute('data-initial', values.Category);
                }
                if (values.Subcategory) {
                    newEntry.querySelector('.subcategory-select').setAttribute('data-initial', values.Subcategory);
                }
                if (values.Item) {
                    newEntry.querySelector('.item-select').setAttribute('data-initial', values.Item);
                }
                if (values.RequestedQty) {
                    newEntry.querySelector('.item-qty').value = values.RequestedQty;
                }
                if (values.Remarks) {
                    newEntry.querySelector('[name$="[Remarks]"]').value = values.Remarks;
                }
                if (values.ItemCode) {
                    newEntry.querySelector('.item-code').value = values.ItemCode;
                }
                if (values.item_uom) { // Note: 'item_uom' comes from old input, 'UOM' from model
                    newEntry.querySelector('.item-uom').value = values.item_uom;
                }
                if (values.UOM) { // For cases where it might be a model object
                    newEntry.querySelector('.item-uom').value = values.UOM;
                }
                if (values.item_name) {
                    newEntry.querySelector('.item-name-hidden').value = values.item_name;
                }

                // Initial population of categories for the new row, then cascade
                populateCategoriesByBranch(newEntry, values.Category, () => {
                    if (values.Category) {
                        populateSubcategoriesByBranchAndCategory(newEntry, values.Subcategory, () => {
                            if (values.Subcategory || values.Category) { // Populate items if subcategory or category was present
                                populateItemsByBranchAndCategoryOrSubcategory(newEntry, values.Item, values.Category);
                            }
                        });
                    }
                });

                itemCounter++;
            }


            document.addEventListener('DOMContentLoaded', () => {
                // Attach event listeners to existing items and handle their initial population
                document.querySelectorAll('.item-entry').forEach(entry => {
                    const categorySelect = entry.querySelector('.category-select');
                    const subcategorySelect = entry.querySelector('.subcategory-select');
                    const itemSelect = entry.querySelector('.item-select');

                    const selectedCategoryId = categorySelect.getAttribute('data-initial');
                    const selectedSubcategoryId = subcategorySelect.getAttribute('data-initial');
                    const selectedItemId = itemSelect.getAttribute('data-initial');

                    // Add event listener for remove button for existing items
                    entry.querySelector('.remove-item-btn').addEventListener('click', function () {
                        this.closest('.item-entry').remove();
                    });

                    // Populate dropdowns in sequence, passing initial values for selection
                    populateCategoriesByBranch(entry, selectedCategoryId, () => {
                        if (selectedCategoryId) {
                            populateSubcategoriesByBranchAndCategory(entry, selectedSubcategoryId, () => {
                                if (selectedSubcategoryId || selectedCategoryId) { // Populate items if subcategory or category was selected
                                    populateItemsByBranchAndCategoryOrSubcategory(entry, selectedItemId, selectedCategoryId);
                                }
                            });
                        } else {
                            subcategorySelect.disabled = true;
                            itemSelect.disabled = true;
                        }
                    });
                });

                // Add Item Button functionality for new items
                document.getElementById('addItemBtn').addEventListener('click', () => {
                    addNewItemRow(); // Call the unified function without initial values
                });

                // Event delegation for dynamically added elements and the main branch select
                document.addEventListener('change', function (e) {
                    const target = e.target;
                    const entry = target.closest('.item-entry'); // Find the closest item entry container

                    // Handle changes to the 'Requesting Branch' select
                    if (target.id === 'from_branch_select') {
                        // When the requesting branch changes, repopulate all item entries
                        document.querySelectorAll('.item-entry').forEach(itemEntry => {
                            // Clear and repopulate categories, then cascade to subcategories and items
                            // Reset data-initial attributes for the new branch selection,
                            // to ensure old selections from a different branch aren't incorrectly re-applied.
                            itemEntry.querySelector('.category-select').setAttribute('data-initial', '');
                            itemEntry.querySelector('.subcategory-select').setAttribute('data-initial', '');
                            itemEntry.querySelector('.item-select').setAttribute('data-initial', '');

                            populateCategoriesByBranch(itemEntry, null, () => {
                                // After categories are populated, clear subcategory and item dropdowns
                                itemEntry.querySelector('.subcategory-select').innerHTML = '<option value="">-- Select Subcategory --</option>';
                                itemEntry.querySelector('.subcategory-select').disabled = true;
                                itemEntry.querySelector('.item-select').innerHTML = '<option value="">-- Select Item --</option>';
                                itemEntry.querySelector('.item-select').disabled = true;
                                itemEntry.querySelector('.item-code').value = '';
                                itemEntry.querySelector('.item-uom').value = '';
                                itemEntry.querySelector('.item-name-hidden').value = '';
                            });
                        });
                        return; // Exit as this change affects all rows, not just one.
                    }

                    // If the change happened within an item entry
                    if (entry) {
                        // When Parent Category changes
                        if (target.classList.contains('category-select')) {
                            // Reset subcategory and item initial selections for the cascade
                            entry.querySelector('.subcategory-select').setAttribute('data-initial', '');
                            entry.querySelector('.item-select').setAttribute('data-initial', '');
                            populateSubcategoriesByBranchAndCategory(entry); // Populate subcategories for this entry
                            populateItemsByBranchAndCategoryOrSubcategory(entry); // Also populate items directly under this category
                        }

                        // When Subcategory changes
                        else if (target.classList.contains('subcategory-select')) {
                            // Reset item initial selection for the cascade
                            entry.querySelector('.item-select').setAttribute('data-initial', '');
                            populateItemsByBranchAndCategoryOrSubcategory(entry); // Populate items for this subcategory
                        }

                        // When Item changes
                        else if (target.classList.contains('item-select')) {
                            fetchItemDetails(target.value, entry);
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
