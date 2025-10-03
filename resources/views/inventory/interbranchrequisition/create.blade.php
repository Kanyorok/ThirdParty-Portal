@extends('layouts.app')

@section('title', 'New Inter-Branch Requisition')

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
        <h4 class="fw-bold mb-3">New Inter-Branch Requisition</h4>

        <form action="{{ route('interbranchrequisition.store') }}" method="POST">
            @csrf

            <div class="card shadow">
                <div class="card-header bg-light fw-bold">➕ Request Stock from Another Branch</div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">From Branch <span class="text-danger">*</span></label>
                            <input type="hidden" id="FromBranch" name="FromBranch" value="{{ $fromBranch->Id }}">
                            <input type="text" class="form-control" value="{{ $fromBranch->Name }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">To Branch/ Requesting Branch <span class="text-danger">*</span></label>
                            <select name="ToBranch" class="form-select" required>
                                <option value="">Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option
                                        value="{{ $branch->Id }}" {{ old('ToBranch') == $branch->Id ? 'selected' : '' }}>
                                        {{ $branch->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="CreatedOn" class="form-control"
                                   value="{{ old('CreatedOn', now()->toDateString()) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="addItemBtn">➕ Add Item</button>
                    </div>

                    <div id="itemsContainer"></div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success" onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Submit Requisition</button>
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
                        <label class="form-label">Parent Category <span class="text-danger">*</span></label>
                        <select name="items[__INDEX__][Category]" class="form-select category-select" data-initial=""
                                required>
                            <option value="">-- Select Category --</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="items[__INDEX__][Subcategory]" class="form-select subcategory-select"
                                data-initial="">
                            <option value="">-- Select Subcategory --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Item <span class="text-danger">*</span></label>
                        <select name="items[__INDEX__][Item]" class="form-select item-select" data-initial="" required>
                            <option value="">-- Select Item --</option>
                        </select>
                        {{-- Hidden input for item_name, required for server-side validation error message --}}
                        <input type="hidden" name="items[__INDEX__][item_name]" class="item-name-hidden">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Item Code</label>
                        <input type="text" name="items[__INDEX__][ItemCode]" class="form-control item-code" value=""
                               readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">UOM</label>
                        <input type="text" class="form-control item-uom" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Requested Qty <span class="text-danger">*</span></label>
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
            let itemCounter = 0;

            // New: Function to populate categories based on selected branch's stock
            function populateCategoriesByBranch(entry, selectedCategory = null, callback = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('FromBranch');
                const fromBranchId = fromBranchSelect.value;

                categorySelect.innerHTML = ''; // Clear previous options
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

                // Using the specific route: /inventory/get-categories-by-branch
                fetch(`/inventory/get-categories-by-branch?from_branch_id=${fromBranchId}`)
                    .then(response => response.json())
                    .then(data => {
                        categorySelect.disabled = false;
                        if (data.categories.length === 0) {
                            categorySelect.innerHTML = `<option value="">${data.message || '-- No Categories Available --'}</option>`;
                            categorySelect.disabled = true;
                        } else {
                            categorySelect.innerHTML = '<option value="">-- Select Category --</option>';
                            data.categories.forEach(cat => {
                                const option = new Option(cat.Name, cat.Id);
                                if (selectedCategory == cat.Id) option.selected = true;
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


            // Modified: Function to populate subcategories by branch and category
            function populateSubcategoriesByBranchAndCategory(entry, selectedSubcat = null, callback = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('FromBranch');

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

                // Using the specific route: /inventory/get-subcategories-by-branch-and-category
                fetch(`/inventory/get-subcategories-by-branch-and-category?from_branch_id=${fromBranchId}&category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        subcategorySelect.disabled = false;
                        if (data.subcategories.length === 0) {
                            subcategorySelect.innerHTML = `<option value="">${data.message || '-- No Subcategories Available --'}</option>`;
                            // If no subcategories, load items directly from the parent category
                            populateItemsByBranchAndCategoryOrSubcategory(entry, null, categoryId);
                            subcategorySelect.disabled = true; // Keep subcategory disabled as there are no options
                        } else {
                            subcategorySelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                            data.subcategories.forEach(subcat => {
                                const option = new Option(subcat.Name, subcat.Id);
                                if (selectedSubcat == subcat.Id) option.selected = true;
                                subcategorySelect.add(option);
                            });
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

            // Renamed: Function to populate items by branch and category/subcategory
            function populateItemsByBranchAndCategoryOrSubcategory(entry, selectedItem = null, fallbackCategoryId = null) {
                const categorySelect = entry.querySelector('.category-select');
                const subcategorySelect = entry.querySelector('.subcategory-select');
                const itemSelect = entry.querySelector('.item-select');
                const fromBranchSelect = document.getElementById('FromBranch');

                const categoryId = fallbackCategoryId || categorySelect.value; // Use fallback if provided
                const subcategoryId = subcategorySelect.value;
                const fromBranchId = fromBranchSelect.value;

                itemSelect.innerHTML = '';
                itemSelect.disabled = true;
                entry.querySelector('.item-code').value = '';
                entry.querySelector('.item-uom').value = '';
                entry.querySelector('.item-name-hidden').value = '';

                let fetchUrl = '';
                // Using the specific route: /inventory/get-items
                if (fromBranchId && (subcategoryId || categoryId)) {
                    fetchUrl = `/inventory/get-items?`;
                    if (subcategoryId) {
                        fetchUrl += `subcategory_id=${subcategoryId}`;
                    } else {
                        fetchUrl += `category_id=${categoryId}`;
                    }
                    fetchUrl += `&from_branch_id=${fromBranchId}`;
                } else {
                    itemSelect.innerHTML = '<option value="">Select Category/Subcategory</option>';
                    return;
                }

                fetch(fetchUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        itemSelect.disabled = false;
                        if (data.items.length === 0) {
                            itemSelect.innerHTML = `<option value="">${data.message || '-- No Items Available --'}</option>`;
                            itemSelect.disabled = true;
                        } else {
                            itemSelect.innerHTML = '<option value="">-- Select Item --</option>';
                        }

                        const currentItem = selectedItem || itemSelect.getAttribute('data-initial');
                        let hasCurrent = false;

                        data.items.forEach(item => {
                            const option = new Option(item.ItemName, item.Id);
                            if (item.Id == currentItem) {
                                option.selected = true;
                                hasCurrent = true;
                            }
                            itemSelect.add(option);
                        });

                        if (currentItem && !hasCurrent && data.items.length > 0) {
                            const originalItemName = entry.querySelector('.item-name-hidden').value || '[Original Item]';
                            const option = new Option(originalItemName, currentItem, true, true);
                            itemSelect.add(option);
                            fetchItemCodeAndUom(currentItem, entry);
                        } else if (itemSelect.value) {
                            fetchItemCodeAndUom(itemSelect.value, entry);
                        } else {
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

            // Function to fetch Item Code and UOM (uses the exact route format /items/code/{Id})
            function fetchItemCodeAndUom(itemId, entry) {
                if (!itemId) {
                    entry.querySelector('.item-code').value = '';
                    entry.querySelector('.item-uom').value = '';
                    entry.querySelector('.item-name-hidden').value = '';
                    return;
                }
                // Using the specific route: /items/code/{Id}
                fetch(`/inventory/items/code/${itemId}`)
                    .then(response => response.json())
                    .then(data => {
                        entry.querySelector('.item-code').value = data.item_code ?? 'N/A';
                        entry.querySelector('.item-uom').value = data.item_uom ?? 'N/A';
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

            function addItem(values = {}) {
                const template = document.getElementById('itemTemplate');
                const clone = template.content.cloneNode(true);
                const currentItemIndex = itemCounter;

                // Replace __INDEX__ in all names
                const fields = clone.querySelectorAll('[name]');
                fields.forEach(element => {
                    element.name = element.name.replace('__INDEX__', currentItemIndex);
                });

                document.getElementById('itemsContainer').appendChild(clone);
                const newEntry = document.getElementById('itemsContainer').lastElementChild;

                const categorySelect = newEntry.querySelector('.category-select');
                const subcategorySelect = newEntry.querySelector('.subcategory-select');
                const itemSelect = newEntry.querySelector('.item-select');
                const itemCodeInput = newEntry.querySelector('.item-code');
                const itemUomInput = newEntry.querySelector('.item-uom');
                const itemNameHiddenInput = newEntry.querySelector('.item-name-hidden');
                const requestedQtyInput = newEntry.querySelector('.item-qty');
                const remarksInput = newEntry.querySelector('[name$="[Remarks]"]');

                // Set initial values from old input or provided values
                if (values.Category) {
                    categorySelect.setAttribute('data-initial', values.Category);
                }
                if (values.Subcategory) {
                    subcategorySelect.setAttribute('data-initial', values.Subcategory);
                }
                if (values.Item) {
                    itemSelect.setAttribute('data-initial', values.Item);
                }
                if (values.ItemCode) {
                    itemCodeInput.value = values.ItemCode;
                }
                if (values.item_uom) { // Assuming item_uom might be passed in old values
                    itemUomInput.value = values.item_uom;
                }
                if (values.item_name) {
                    itemNameHiddenInput.value = values.item_name;
                }
                if (values.RequestedQty) {
                    requestedQtyInput.value = values.RequestedQty;
                }
                if (values.Remarks) {
                    remarksInput.value = values.Remarks;
                }

                // Populate categories and then subcategories/items based on old values
                populateCategoriesByBranch(newEntry, values.Category, () => {
                    if (values.Category) {
                        categorySelect.value = values.Category; // Ensure selected
                        populateSubcategoriesByBranchAndCategory(newEntry, values.Subcategory, () => {
                            if (values.Subcategory) {
                                subcategorySelect.value = values.Subcategory; // Ensure selected
                                populateItemsByBranchAndCategoryOrSubcategory(newEntry, values.Item);
                            } else if (values.Category) {
                                // If subcategory was not set, but category was, load items for that category
                                populateItemsByBranchAndCategoryOrSubcategory(newEntry, values.Item, values.Category);
                            }
                        });
                    }
                });

                newEntry.querySelector('.remove-item-btn').addEventListener('click', function () {
                    this.closest('.item-entry').remove();
                });

                itemCounter++;
            }

            document.getElementById('addItemBtn').addEventListener('click', function () {
                addItem();
            });

            // Handle changes on Requesting Branch select to update all item categories
            document.getElementById('FromBranch').addEventListener('change', function () {
                const itemEntries = document.querySelectorAll('.item-entry');
                itemEntries.forEach(entry => {
                    populateCategoriesByBranch(entry);
                });
            });


            // Delegated event listener for category and subcategory changes
            document.addEventListener('change', function (e) {
                const entry = e.target.closest('.item-entry');
                if (!entry) return;

                if (e.target.classList.contains('category-select')) {
                    populateSubcategoriesByBranchAndCategory(entry);
                    populateItemsByBranchAndCategoryOrSubcategory(entry); // Reset items when category changes
                } else if (e.target.classList.contains('subcategory-select')) {
                    populateItemsByBranchAndCategoryOrSubcategory(entry);
                } else if (e.target.classList.contains('item-select')) {
                    // Call the dedicated function to fetch item code and UOM
                    fetchItemCodeAndUom(e.target.value, entry);
                }
            });

            // Initial load: Add first item automatically if no old inputs (fresh form) or repopulate from old input
            @if (!old('items'))
            addItem();
            @else
            @foreach (old('items', []) as $index => $oldItem)
            addItem(@json($oldItem));
            @endforeach
            @endif
        </script>

        <style>
            .text-danger {
                font-weight: bold;
            }
            .form-label {
                font-weight: 500;
            }
        </style>
    @endpush
@endsection