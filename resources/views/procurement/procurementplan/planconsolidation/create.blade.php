@extends('layouts.app') {{-- Assuming you have a main layout file --}}

@section('title', 'Create New Procurement Plan') {{-- Sets the page title --}}

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create New Procurement Plan</h1>
        <div>
            {{-- Ensure this route name 'procurement-periods.index' correctly points to your ProcurementPeriodController@index method --}}
            <a href="{{ route('procurement-periods.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Plans
            </a>
        </div>
    </div>

    @include('partials.alerts') {{-- For displaying session success/error messages --}}

    {{-- The form will submit to ProcurementPeriodController@store (or equivalent) --}}
    <form action="{{ route('procurement-periods.store') }}" method="POST" id="createProcurementPlanForm">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Plan Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="PlanTitle" class="form-label required">Plan Title</label>
                                <input type="text" name="PlanTitle" id="PlanTitle" class="form-control @error('PlanTitle') is-invalid @enderror" value="{{ old('PlanTitle', 'e.g. Annual Procurement Plan - ' . date('Y')) }}" required>
                                @error('PlanTitle')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="PlanRefNo" class="form-label">Suggested Reference Number</label>
                                {{-- Server-side generation is crucial for uniqueness. This is a UX suggestion. --}}
                                <input type="text" name="PlanRefNo_suggestion" id="PlanRefNo" class="form-control @error('PlanRefNo') is-invalid @enderror"
                                       value="{{ old('PlanRefNo_suggestion', 'PLAN/'.strtoupper(Auth::user()->department?->code ?? 'DEPT').'/'.date('Y').'/'.str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT)) }}"
                                       readonly title="This is a suggested reference. Final reference will be confirmed upon saving.">
                                @error('PlanRefNo') {{-- If you validate a user-provided one, which is not typical for auto-generated --}}
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="PlanYear" class="form-label required">Plan Year</label>
                                <select name="PlanYear" id="PlanYear" class="form-select @error('PlanYear') is-invalid @enderror" required>
                                    {{-- $availableYears should be passed from the controller --}}
                                    {{-- Example: $availableYears = range(date('Y') - 1, date('Y') + 3); --}}
                                    @php $currentSelectedYear = old('PlanYear', date('Y')); @endphp
                                    @foreach($availableYears ?? [date('Y'), date('Y')+1, date('Y')+2] as $year)
                                        <option value="{{ $year }}" {{ $currentSelectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                                @error('PlanYear')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Department</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->department?->name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="department_id" value="{{ Auth::user()->department_id }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Branch</label>
                                <input type="text" class="form-control" value="{{ Auth::user()->branch?->name ?? 'N/A' }}" readonly>
                                <input type="hidden" name="branch_id" value="{{ Auth::user()->branch_id }}">
                            </div>

                            <div class="col-12">
                                <label for="Description" class="form-label">Description / Scope</label>
                                <textarea name="Description" id="Description" class="form-control @error('Description') is-invalid @enderror" rows="3">{{ old('Description') }}</textarea>
                                @error('Description')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="col-12">
                                 <label class="form-label required">How would you like to add items?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="item_creation_method" id="manualItemEntryRadio" value="manual" {{ old('item_creation_method', 'manual') == 'manual' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="manualItemEntryRadio">
                                        Add Items Manually
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="item_creation_method" id="importFromNeedsRadio" value="import" {{ old('item_creation_method') == 'import' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="importFromNeedsRadio">
                                        Import from Approved Needs
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Plan Items</h5>
                        <button type="button" class="btn btn-sm btn-primary" id="triggerImportNeedsModal" data-bs-toggle="modal" data-bs-target="#importNeedsModal" style="display: {{ old('item_creation_method') == 'import' ? 'block' : 'none' }};">
                            <i class="fas fa-download"></i> Select from Approved Needs
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="25%">Item <span class="text-danger">*</span></th>
                                        <th width="15%">Category</th>
                                        <th width="10%">UOM</th>
                                        <th width="10%">Qty <span class="text-danger">*</span></th>
                                        <th width="10%">Unit Cost <span class="text-danger">*</span></th>
                                        <th width="10%">Total Cost</th>
                                        <th width="15%">Planned Qtr</th>
                                        <th width="15%">Delivery Date</th>
                                        <th width="5%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Dynamic rows will be added here by JavaScript --}}
                                    {{-- Handle old input for items if validation fails --}}
                                    @if(is_array(old('items')))
                                        @foreach(old('items') as $key => $oldItem)
                                            <tr class="item-row">
                                                <td>
                                                    <select name="items[{{ $key }}][ItemId]" class="form-select form-select-sm item-select @error('items.'.$key.'.ItemId') is-invalid @enderror" required>
                                                        <option value="">Select Item</option>
                                                        @foreach($availableItems ?? [] as $item)
                                                            <option value="{{ $item->Id }}" data-unit-cost="{{ $item->UnitPrice ?? 0 }}" data-category="{{ $item->Category ?? '' }}" data-uom="{{ $item->UOM ?? '' }}" {{ (isset($oldItem['ItemId']) && $oldItem['ItemId'] == $item->Id) ? 'selected' : '' }}>
                                                                {{ $item->ItemName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('items.'.$key.'.ItemId') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                                </td>
                                                <td><input type="text" name="items[{{ $key }}][Category]" value="{{ $oldItem['Category'] ?? '' }}" class="form-control form-control-sm item-category @error('items.'.$key.'.Category') is-invalid @enderror" readonly></td>
                                                <td><input type="text" name="items[{{ $key }}][UOM]" value="{{ $oldItem['UOM'] ?? '' }}" class="form-control form-control-sm item-uom @error('items.'.$key.'.UOM') is-invalid @enderror" readonly></td>
                                                <td><input type="number" name="items[{{ $key }}][Quantity]" value="{{ $oldItem['Quantity'] ?? 1 }}" class="form-control form-control-sm item-quantity @error('items.'.$key.'.Quantity') is-invalid @enderror" min="0.01" step="0.01" required></td>
                                                <td><input type="number" name="items[{{ $key }}][UnitCost]" value="{{ $oldItem['UnitCost'] ?? 0 }}" class="form-control form-control-sm item-unit-cost @error('items.'.$key.'.UnitCost') is-invalid @enderror" min="0" step="0.01" required readonly></td>
                                                <td class="item-total-display text-end">0.00</td>
                                                <td>
                                                    <select name="items[{{ $key }}][PlannedQuarter]" class="form-select form-select-sm @error('items.'.$key.'.PlannedQuarter') is-invalid @enderror">
                                                        <option value="">Select</option>
                                                        <option value="Q1" {{ (isset($oldItem['PlannedQuarter']) && $oldItem['PlannedQuarter'] == 'Q1') ? 'selected' : '' }}>Q1</option>
                                                        <option value="Q2" {{ (isset($oldItem['PlannedQuarter']) && $oldItem['PlannedQuarter'] == 'Q2') ? 'selected' : '' }}>Q2</option>
                                                        <option value="Q3" {{ (isset($oldItem['PlannedQuarter']) && $oldItem['PlannedQuarter'] == 'Q3') ? 'selected' : '' }}>Q3</option>
                                                        <option value="Q4" {{ (isset($oldItem['PlannedQuarter']) && $oldItem['PlannedQuarter'] == 'Q4') ? 'selected' : '' }}>Q4</option>
                                                    </select>
                                                </td>
                                                <td><input type="date" name="items[{{ $key }}][ExpectedDeliveryDate]" value="{{ $oldItem['ExpectedDeliveryDate'] ?? '' }}" class="form-control form-control-sm @error('items.'.$key.'.ExpectedDeliveryDate') is-invalid @enderror"></td>
                                                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    <tr class="empty-row" style="{{ is_array(old('items')) && count(old('items')) > 0 ? 'display:none;' : '' }}">
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            No items added yet. Use "Add Item Manually" or "Select from Approved Needs".
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end"><strong>Overall Total:</strong></td>
                                        <td id="overallTotalCostDisplay" class="text-end fw-bold">0.00</td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-start mt-3" id="manualAddItemButtonContainer" style="display: {{ old('item_creation_method', 'manual') == 'manual' ? 'block' : 'none' }};">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addManualItemButton">
                                <i class="fas fa-plus"></i> Add Item Manually
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Plan Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong class="form-label">Status:</strong>
                            <div class="form-control-plaintext py-0 fw-bold text-primary">DRAFT</div>
                        </div>
                        <hr class="my-2">
                        <div class="mb-2">
                            <strong class="form-label">Total Items:</strong>
                            <div class="form-control-plaintext py-0" id="summaryTotalItems">0</div>
                        </div>
                        <hr class="my-2">
                        <div class="mb-2">
                            <strong class="form-label">Estimated Cost (KES):</strong>
                            <div class="form-control-plaintext py-0 fw-bold" id="summaryOverallTotalCost">0.00</div>
                        </div>
                        <hr class="my-2">
                        <div class="mb-2">
                            <strong class="form-label">Prepared By:</strong>
                            <div class="form-control-plaintext py-0">{{ Auth::user()->name }}</div>
                        </div>
                        <hr class="my-2">
                        <div class="mb-2">
                            <strong class="form-label">Date Prepared:</strong>
                            <div class="form-control-plaintext py-0">{{ now()->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg" name="form_action" value="save_draft">
                        <i class="fas fa-save"></i> Save as Draft
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg" name="form_action" value="submit_for_approval">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@include('procurement_periods.modals.import_needs_modal')
{{-- Make sure this path is correct and the modal is designed to work with the JS below --}}

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemKeyCounter = {{ is_array(old('items')) ? count(old('items')) : 0 }}; // Start counter after old items
    const itemsTableBody = document.getElementById('itemsTable').querySelector('tbody');
    const emptyRow = itemsTableBody.querySelector('.empty-row');
    const availableItemsData = @json($availableItems ?? []); // Pass items from controller

    const manualItemEntryRadio = document.getElementById('manualItemEntryRadio');
    const importFromNeedsRadio = document.getElementById('importFromNeedsRadio');
    const manualAddItemButtonContainer = document.getElementById('manualAddItemButtonContainer');
    const triggerImportNeedsModalButton = document.getElementById('triggerImportNeedsModal');

    function toggleItemEntryMethodControls() {
        if (manualItemEntryRadio.checked) {
            manualAddItemButtonContainer.style.display = 'block';
            triggerImportNeedsModalButton.style.display = 'none';
        } else if (importFromNeedsRadio.checked) {
            manualAddItemButtonContainer.style.display = 'none';
            triggerImportNeedsModalButton.style.display = 'block';
        }
    }

    manualItemEntryRadio.addEventListener('change', toggleItemEntryMethodControls);
    importFromNeedsRadio.addEventListener('change', toggleItemEntryMethodControls);
    toggleItemEntryMethodControls(); // Initial call


    function calculateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const unitCost = parseFloat(row.querySelector('.item-unit-cost').value) || 0;
        const total = quantity * unitCost;
        row.querySelector('.item-total-display').textContent = total.toFixed(2);
        updateOverallTotal();
    }

    function updateOverallTotal() {
        let overallTotal = 0;
        let totalItemsCount = 0;
        itemsTableBody.querySelectorAll('tr.item-row').forEach(row => {
            totalItemsCount++;
            const rowTotalText = row.querySelector('.item-total-display').textContent;
            overallTotal += parseFloat(rowTotalText) || 0;
        });

        document.getElementById('overallTotalCostDisplay').textContent = overallTotal.toFixed(2);
        document.getElementById('summaryTotalItems').textContent = totalItemsCount;
        document.getElementById('summaryOverallTotalCost').textContent = overallTotal.toFixed(2);

        if (emptyRow) {
            emptyRow.style.display = totalItemsCount > 0 ? 'none' : '';
        }
    }

    function addRowEventListeners(row) {
        row.querySelector('.item-quantity').addEventListener('input', function() { calculateRowTotal(row); });
        // Unit cost is readonly for selected items, but might be editable if item not found or for pure manual entry
        // row.querySelector('.item-unit-cost').addEventListener('input', function() { calculateRowTotal(row); });

        row.querySelector('.remove-item').addEventListener('click', function() {
            row.remove();
            updateOverallTotal();
        });

        const itemSelect = row.querySelector('.item-select');
        if (itemSelect) {
            itemSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                row.querySelector('.item-unit-cost').value = parseFloat(selectedOption.dataset.unitCost || 0).toFixed(2);
                row.querySelector('.item-category').value = selectedOption.dataset.category || '';
                row.querySelector('.item-uom').value = selectedOption.dataset.uom || '';
                calculateRowTotal(row);
            });
            // Trigger change if a value is pre-selected (e.g., from old input)
            if(itemSelect.value) {
                itemSelect.dispatchEvent(new Event('change'));
            }
        }
    }

    // Add event listeners to existing rows (e.g., from old input)
    itemsTableBody.querySelectorAll('tr.item-row').forEach(row => {
        addRowEventListeners(row);
        calculateRowTotal(row); // Calculate total for existing rows
    });
    updateOverallTotal(); // Initial calculation for page load


    document.getElementById('addManualItemButton').addEventListener('click', function() {
        itemKeyCounter++;
        const newRow = document.createElement('tr');
        newRow.classList.add('item-row');
        let itemOptions = '<option value="">Select Item</option>';
        availableItemsData.forEach(item => {
            itemOptions += `<option value="${item.Id}" data-unit-cost="${item.UnitPrice || 0}" data-category="${item.Category || ''}" data-uom="${item.UOM || ''}">${item.ItemName}</option>`;
        });

        newRow.innerHTML = `
            <td>
                <select name="items[${itemKeyCounter}][ItemId]" class="form-select form-select-sm item-select" required>
                    ${itemOptions}
                </select>
            </td>
            <td><input type="text" name="items[${itemKeyCounter}][Category]" class="form-control form-control-sm item-category" readonly></td>
            <td><input type="text" name="items[${itemKeyCounter}][UOM]" class="form-control form-control-sm item-uom" readonly></td>
            <td><input type="number" name="items[${itemKeyCounter}][Quantity]" class="form-control form-control-sm item-quantity" min="0.01" step="0.01" value="1" required></td>
            <td><input type="number" name="items[${itemKeyCounter}][UnitCost]" class="form-control form-control-sm item-unit-cost" min="0" step="0.01" value="0.00" required readonly></td>
            <td class="item-total-display text-end">0.00</td>
            <td>
                <select name="items[${itemKeyCounter}][PlannedQuarter]" class="form-select form-select-sm">
                    <option value="">Select</option>
                    <option value="Q1">Q1</option><option value="Q2">Q2</option><option value="Q3">Q3</option><option value="Q4">Q4</option>
                </select>
            </td>
            <td><input type="date" name="items[${itemKeyCounter}][ExpectedDeliveryDate]" class="form-control form-control-sm"></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
        `;
        itemsTableBody.insertBefore(newRow, emptyRow);
        addRowEventListeners(newRow);
        updateOverallTotal();

        // Initialize select2 on the new row if you are using it
        // $(newRow.querySelector('.item-select')).select2({ placeholder: "Select Item", allowClear: true });
    });

    // --- JavaScript for "Import from Needs" Modal ---
    // This is a placeholder. You'll need to implement the modal interaction.
    // When items are selected in the modal and "Add Selected Needs" is clicked:
    // 1. Get the data for each selected need.
    // 2. For each need, call a function similar to 'addManualItemButton' click handler,
    //    but populate the fields with data from the 'need'.
    // Example (conceptual):
    window.addItemsFromNeeds = function(selectedNeedsDetails) { // Call this from your modal's JS
        selectedNeedsDetails.forEach(need => {
            itemKeyCounter++;
            const newRow = document.createElement('tr');
            newRow.classList.add('item-row');

            // Find matching item in availableItemsData or use need's description
            let itemOptions = '<option value="">Select Item</option>';
            let matchedItemId = null;
            let unitCost = parseFloat(need.unit_cost || need.estimated_cost / (need.quantity || 1) || 0).toFixed(2);
            let category = need.category || '';
            let uom = need.uom || '';

            availableItemsData.forEach(item => {
                // Attempt to match by name or a potential item_id from the 'need' object
                if (item.Id == need.master_item_id || item.ItemName.toLowerCase() === (need.item_description || need.item_name || '').toLowerCase()) {
                    matchedItemId = item.Id;
                    unitCost = parseFloat(item.UnitPrice || unitCost).toFixed(2); // Prioritize master item's unit cost
                    category = item.Category || category;
                    uom = item.UOM || uom;
                }
                itemOptions += `<option value="${item.Id}" data-unit-cost="${item.UnitPrice || 0}" data-category="${item.Category || ''}" data-uom="${item.UOM || ''}" ${matchedItemId == item.Id ? 'selected' : ''}>${item.ItemName}</option>`;
            });
            if (!matchedItemId && need.item_description) { // If no match, add a placeholder or handle as new item
                 itemOptions += `<option value="" data-description-temp="${need.item_description}" selected>NEEDS: ${need.item_description} (Review)</option>`;
            }


            newRow.innerHTML = `
                <td>
                    <select name="items[${itemKeyCounter}][ItemId]" class="form-select form-select-sm item-select" required>
                        ${itemOptions}
                    </select>
                    ${!matchedItemId && need.item_description ? `<input type="hidden" name="items[${itemKeyCounter}][new_item_description]" value="${need.item_description}">` : ''}
                </td>
                <td><input type="text" name="items[${itemKeyCounter}][Category]" value="${category}" class="form-control form-control-sm item-category" readonly></td>
                <td><input type="text" name="items[${itemKeyCounter}][UOM]" value="${uom}" class="form-control form-control-sm item-uom" readonly></td>
                <td><input type="number" name="items[${itemKeyCounter}][Quantity]" value="${need.quantity || 1}" class="form-control form-control-sm item-quantity" min="0.01" step="0.01" required></td>
                <td><input type="number" name="items[${itemKeyCounter}][UnitCost]" value="${unitCost}" class="form-control form-control-sm item-unit-cost" min="0" step="0.01" required readonly></td>
                <td class="item-total-display text-end">0.00</td>
                <td>
                    <select name="items[${itemKeyCounter}][PlannedQuarter]" class="form-select form-select-sm">
                        <option value="">Select</option>
                        <option value="Q1" ${ (need.planned_quarter == 'Q1') ? 'selected' : '' }>Q1</option>
                        <option value="Q2" ${ (need.planned_quarter == 'Q2') ? 'selected' : '' }>Q2</option>
                        <option value="Q3" ${ (need.planned_quarter == 'Q3') ? 'selected' : '' }>Q3</option>
                        <option value="Q4" ${ (need.planned_quarter == 'Q4') ? 'selected' : '' }>Q4</option>
                    </select>
                </td>
                <td><input type="date" name="items[${itemKeyCounter}][ExpectedDeliveryDate]" value="${need.expected_delivery_date || ''}" class="form-control form-control-sm"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="fas fa-trash"></i></button></td>
            `;
            itemsTableBody.insertBefore(newRow, emptyRow);
            addRowEventListeners(newRow);
            // Trigger change on the new select to populate dependent fields
            const newSelect = newRow.querySelector('.item-select');
            if (newSelect) newSelect.dispatchEvent(new Event('change'));

        });
        updateOverallTotal();
        // $('#importNeedsModal').modal('hide'); // If using jQuery and Bootstrap's JS for modal
        const importModalInstance = bootstrap.Modal.getInstance(document.getElementById('importNeedsModal'));
        if (importModalInstance) importModalInstance.hide();
    };

    // Handle form submission to include item data correctly
    // No specific JS needed for submission if input names are correct,
    // Laravel will handle the array of items.
});
</script>
@endpush
