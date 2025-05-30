@extends('layouts.app')
@section('title', 'Tender Initiation Form')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Tender Initiation Form</h4>
<form action="{{ route('initiatetender.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('POST')

    <!-- Title -->
    <div class="mb-3">
        <label for="tenderTitle" class="form-label fw-bold">Tender Title:</label>
        <input type="text" class="form-control" id="tenderTitle" name="title" placeholder="Enter tender title">
        @error('title')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Tender Type -->
    <div class="mb-3">
        <label class="form-label fw-bold">Tender Type:</label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tender_type" id="openTender" value="op">
                <label class="form-check-label" for="openTender">Open Tender (Public posting)</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tender_type" id="restrictedTender" value="rs">
                <label class="form-check-label" for="restrictedTender">Restricted Tender (Selected vendors only)</label>
            </div>
        </div>
        @error('tender_type')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Category and Requisition -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="tenderCategory" class="form-label fw-bold">Tender Category:</label>
            <select class="form-select" id="tenderCategory" name="tender_category_id">
                <option selected disabled>-- Select Category --</option>
                @foreach ($tenderCategories as $item)
                    <option value="{{$item->Id}}">{{$item->TenderCategory}}</option>
                @endforeach
            </select>
            @error('tender_category_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="itemCategory" class="form-label fw-bold">Item Category</label>
            <select class="form-select" id="itemCategory" name="item_category_id">
                <option selected disabled>-- Item Categories --</option>
                @foreach ($AllItemsCategories as $item)
                    <option value="{{$item->Id}}">{{$item->Name}}</option>
                @endforeach
            </select>
            @error('item_category_id')
                <div class="text-danger">{{ $message }}</div>   
            @enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="itemCategory" class="form-label fw-bold">Currency</label>
            <select class="form-select" id="currencyType" name="currency_id">
                <option selected disabled>-- Select Your Currency --</option>
                @foreach ($allCurrency as $item)
                    <option value="{{$item->Id}}">{{$item->Name}} ({{$item->Code}})</option>
                @endforeach
            </select>
            @error('currency_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Tender Items Tabs -->
    <ul class="nav nav-tabs mt-4" id="itemEntryTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="fromPlan-tab" data-bs-toggle="tab" data-bs-target="#fromPlan" type="button" role="tab">From Procurement Plan</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manualEntry" type="button" role="tab">Manual Entry</button>
        </li>
    </ul>

    <div class="tab-content p-3 border border-top-0" id="itemEntryTabsContent">

        <!-- From Procurement Plan -->
        <div class="tab-pane fade show active" id="fromPlan" role="tabpanel">
            <div class="mb-3">
                <label class="form-label fw-bold">Select Procurement Plan:</label>
                <select class="form-select" id="selectedProcurementPlan" name="procurement_plan_id" onchange="loadPlanItemsForPlan()">
                    <option selected disabled>-- Choose Procurement Plan --</option>
                    @foreach ($procurementPlan as $item)
                        <option value="{{$item->PlanID}}">{{$item->Title}} - {{$item->ReferenceNumber}}</option>
                    @endforeach
                </select>
            </div>

            <div class="row mb-3">
                <div class="col-md-9">
                    <label class="form-label fw-bold">Select Procurement Plan Item:</label>
                    <select class="form-select" id="planItemSelect">
                        <option selected disabled>-- Select Item --</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" onclick="addPlanItemToGrid()">➕ Add to Grid</button>
                </div>
            </div>

            <!-- Dynamic Plan Items Grid -->
            <div class="mb-3">
                <label class="form-label fw-bold">Selected Items</label>
                <table class="table table-bordered" id="planItemsGrid">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Planned Qty</th>
                            <th>Qty to Tender</th>
                            <th>Specs</th>
                            <th>PR Ref (optional)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody name="plan_items">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Manual Entry -->
        <div class="tab-pane fade" id="manualEntry" role="tabpanel">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Qty</th>
                        <th>Specs</th>
                        <th>PR Ref (optional)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="manualItemsBody">
                    <tr>
                        <td>
                            <select class="form-select manual-item-select" name="manual_items[0][item_id]">
                                <option selected value="">-- Select Item --</option>
                                @foreach ($allItemsWithCategoryIds as $item)
                                    <option value="{{$item->Id}}" data-item-category="{{$item->Category}}">{{$item->ItemName}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" class="form-control" name="manual_items[0][qty]" value="1"></td>
                        <td><input type="file" class="form-control" name="manual_items[0][specs_file]"></td>
                        <td><input type="text" class="form-control" name="manual_items[0][pr_ref]" placeholder="PR/2025/002"></td>
                        <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">🗑</button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-primary" onclick="addManualItemRow()">➕ Add Item</button>
        </div>
    </div>

    <!-- Scope -->
    <div class="mb-3">
        <label for="scopeOfWork" class="form-label fw-bold">Scope of Work</label>
        <textarea class="form-control" id="scopeOfWork" name="scope_of_work" rows="3"></textarea>
        @error('scope_of_work')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Instructions -->
    <div class="mb-3">
        <label for="instructions" class="form-label fw-bold">Instructions to Bidders:</label>
        <textarea class="form-control" id="instructions" name="instructions" rows="3"></textarea>
        @error('instructions')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <!-- Dates -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="submissionDeadline" class="form-label fw-bold">Submission Deadline:</label>
            <input type="date" class="form-control" id="submissionDeadline" name="submission_deadline">
            @error('submission_deadline')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="openingDate" class="form-label fw-bold">Opening Date:</label>
            <input type="date" class="form-control" id="openingDate" name="opening_date">
            @error('opening_date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Upload -->
    <div class="mb-3">
        <label for="tenderDocuments" class="form-label fw-bold">Attach Tender Document:</label>
        <input class="form-control" type="file" id="tenderDocuments" name="documents[]" multiple>
    </div>

    <!-- Restricted Suppliers -->
    <div class="mb-3" id="restrictedSuppliersSection" style="display: none;">
        <label for="suppliersList" class="form-label fw-bold">Add Suppliers to Invite:</label>
        <select class="form-select" id="suppliersList" name="suppliers[]" multiple>
            <!-- Filled dynamically -->
        </select>
    </div>

    <!-- Buttons -->
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Save Tender</button>
        {{-- <button type="button" class="btn btn-outline-secondary">Save Draft</button> --}}
        {{-- <button type="reset" class="btn btn-outline-dark">Cancel</button>
        <button type="button" class="btn btn-outline-info">Edit</button> --}}
    </div>
</form>

</div>
 
<!-- Script Section -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const open = document.getElementById('openTender');
        const restricted = document.getElementById('restrictedTender');
        const section = document.getElementById('restrictedSuppliersSection');
 
        open.addEventListener('change', () => section.style.display = 'none');
        restricted.addEventListener('change', () => section.style.display = 'block');
    });
 
let manualItemIndex = 1;

function addManualItemRow() {
    const tableBody = document.getElementById('manualItemsBody');

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <select class="form-select manual-item-select" name="manual_items[${manualItemIndex}][item_id]">
                <option selected disabled>-- Select Item --</option>
                @foreach ($allItemsWithCategoryIds as $item)
                    <option value="{{ $item->Id }}" data-item-category="{{ $item->Category }}">{{ $item->ItemName }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" class="form-control" name="manual_items[${manualItemIndex}][qty]" value="1" min="1">
        </td>
        <td>
            <input type="file" class="form-control" name="manual_items[${manualItemIndex}][specs_file]">
        </td>
        <td>
            <input type="text" class="form-control" name="manual_items[${manualItemIndex}][pr_ref]" placeholder="PR/2025/XXX">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">🗑</button>
        </td>
    `;

    tableBody.appendChild(row);
    manualItemIndex++;
}


    const procurementPlans = @json($procurementPlansOutput);
    const planItemData = @json($planItemData);
    const addedPlanItems = new Set();
 
    function loadPlanItemsForPlan() {
        const planId = document.getElementById('selectedProcurementPlan').value;
        const select = document.getElementById('planItemSelect');
        select.innerHTML = `<option selected disabled>-- Select Item --</option>`;
 
        console.log(procurementPlans);
        
        if (procurementPlans[planId]) {
            procurementPlans[planId].forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = `${item.name} (${item.plannedQty})`;
                select.appendChild(opt);
            });
        }
    }
 
    function addPlanItemToGrid() {
        const select = document.getElementById('planItemSelect');
        console.log(select);
        
        const itemId = select.value;
        const item = planItemData[itemId];
        const tbody = document.querySelector('#planItemsGrid tbody');

        if (!itemId || addedPlanItems.has(itemId)) {
            alert('Item already added or not selected');
            return;
        }
 
        const row = `
            <tr data-id="${itemId}">
                <td>${item.name}</td>
                <td>${item.plannedQty}</td>
                <td>
                    <input type="hidden" name="plan_items[${itemId}][item_id]" value="${itemId}">
                    <input type="number" class="form-control" name="plan_items[${itemId}][qty]" value="${item.plannedQty}" min="1" max="${item.plannedQty}">
                </td>
                <td>
                    <input type="file" class="form-control" name="plan_items[${itemId}][file]">
                </td>
                <td>
                    <input type="text" class="form-control" name="plan_items[${itemId}][pr_ref]" placeholder="PR/2025/XXX">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePlanItemFromGrid(${itemId})">🗑</button>
                </td>
            </tr>
        `;

        tbody.insertAdjacentHTML('beforeend', row);
        addedPlanItems.add(itemId);
        select.selectedIndex = 0;
    }
 
    function removePlanItemFromGrid(itemId) {
        const row = document.querySelector(`#planItemsGrid tr[data-id="${itemId}"]`);
        if (row) row.remove();
        addedPlanItems.delete(itemId.toString());
    }  
</script>
 
    <script>
        // Pass supplier and category data from Laravel to JavaScript
        const suppliers = @json($suppliers);
        const categories = @json($AllItemsCategories);

        // Get DOM elements
        const openTender = document.getElementById('openTender');
        const restrictedTender = document.getElementById('restrictedTender');
        const suppliersSection = document.getElementById('restrictedSuppliersSection');
        const suppliersList = document.getElementById('suppliersList');
        const itemCategory = document.getElementById('itemCategory');

        // Function to populate suppliers list
        function populateSuppliers(categoryId = null) {
            // Clear existing options
            suppliersList.innerHTML = '';

            // Filter suppliers by categoryId (if provided) or show all suppliers
            const filteredSuppliers = categoryId
                ? suppliers.filter(supplier => String(supplier.CategoryId) === String(categoryId))
                : suppliers;

            // If no suppliers match, show a placeholder option
            if (filteredSuppliers.length === 0) {
                const option = document.createElement('option');
                option.disabled = true;
                option.textContent = categoryId
                    ? 'No suppliers available for this category'
                    : 'No suppliers available';
                suppliersList.appendChild(option);
                return;
            }

            // Add filtered suppliers as options
            filteredSuppliers.forEach(supplier => {
                const option = document.createElement('option');
                option.value = supplier.Id;
                option.textContent = supplier.SupplierName;
                suppliersList.appendChild(option);
            });
        }

        // Event listeners for radio buttons
        openTender.addEventListener('change', () => {
            suppliersSection.style.display = 'none';
            suppliersList.innerHTML = ''; // Clear options when hiding
            itemCategory.value = ''; // Reset category selection
        });

        restrictedTender.addEventListener('change', () => {
            suppliersSection.style.display = 'block';
            populateSuppliers(); // Show all suppliers initially
        });

        // Event listener for category dropdown
        itemCategory.addEventListener('change', () => {
            if (restrictedTender.checked) {
                suppliersSection.style.display = 'block';
                populateSuppliers(itemCategory.value); // Filter suppliers by selected category
            } else {
                suppliersSection.style.display = 'none';
                suppliersList.innerHTML = ''; // Clear options if restricted is not selected
            }
        });
    </script>

    <script>
        const originalItemOptions = [];

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.manual-item-select option').forEach(opt => {
                if (opt.value !== "" && opt.value !== "-- Select Item --") {
                    originalItemOptions.push(opt.cloneNode(true));
                }
            });
        });

            document.getElementById('itemCategory').addEventListener('change', (e) => {
        const selectedCategoryId = e.target.value;

        document.querySelectorAll('.manual-item-select').forEach(select => {
            // Clear all except default option
            const defaultOption = select.querySelector('option:first-child');
            select.innerHTML = '';
            select.appendChild(defaultOption.cloneNode(true));

            // Append only matching items
            originalItemOptions.forEach(opt => {
                if (opt.dataset.itemCategory === selectedCategoryId) {
                    select.appendChild(opt.cloneNode(true));
                }
            });
        });
        });
    </script>

@endsection