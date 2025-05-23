@extends('layouts.app')
@section('title', 'Tender Initiation Form')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Tender Initiation Form</h4>
    <form>
        <!-- Title -->
        <div class="mb-3">
            <label for="tenderTitle" class="form-label fw-bold">Tender Title:</label>
            <input type="text" class="form-control" id="tenderTitle" placeholder="Enter tender title">
        </div>
 
        <!-- Tender Type -->
        <div class="mb-3">
            <label class="form-label fw-bold">Tender Type:</label>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tenderType" id="openTender" value="Open">
                    <label class="form-check-label" for="openTender">Open Tender (Public posting)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tenderType" id="restrictedTender" value="Restricted">
                    <label class="form-check-label" for="restrictedTender">Restricted Tender (Selected vendors only)</label>
                </div>
            </div>
        </div>
 
        <!-- Category and Requisition -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tenderCategory" class="form-label fw-bold">Tender Category:</label>
                <select class="form-select" id="tenderCategory">
                    <option selected disabled>-- Select Category --</option>
                    <option>Goods</option>
                    <option>Services</option>
                    <option>Works</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="relatedPR" class="form-label fw-bold">Item Category</label>
                <select class="form-select" id="relatedPR">
                    <option selected disabled>-- Item Categories --</option>
                    <option>Technology</option>
                    <option>Stationery</option>
                </select>
            </div>
        </div>
 
        <!-- Tender Items Section -->
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
    <!-- Select Procurement Plan -->
    <div class="mb-3">
        <label class="form-label fw-bold">Select Procurement Plan:</label>
        <select class="form-select" id="selectedProcurementPlan" onchange="loadPlanItemsForPlan()">
            <option selected disabled>-- Choose Procurement Plan --</option>
            <option value="2025-DEP01">2025 - ICT Department</option>
            <option value="2025-DEP02">2025 - Finance Department</option>
        </select>
    </div>
 
    <!-- Select Plan Item -->
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
 
    <!-- PR Reference -->
    <div class="mb-3">
        <label class="form-label fw-bold">Optional PR Reference:</label>
        <select class="form-select" id="optionalPRRef">
            <option selected disabled>-- Select PR Reference --</option>
            <option>PR/2025/001</option>
            <option>PR/2025/002</option>
        </select>
    </div>
 
    <!-- Item Grid Table -->
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
            <tbody>
                <!-- Dynamic rows will be added here -->
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
                            <th>UOM</th>
                            <th>Specs</th>
                            <th>PR Ref (optional)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="manualItemsBody">
                        <tr>
                            <td><input type="text" class="form-control" placeholder="e.g. Laptop 16GB RAM"></td>
                            <td><input type="number" class="form-control" value="1"></td>
                            <td><input type="text" class="form-control" placeholder="pcs"></td>
                            <td><input type="file" class="form-control"></td>
                            <td><input type="text" class="form-control" placeholder="PR/2025/002"></td>
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
            <textarea class="form-control" id="scopeOfWork" rows="3"></textarea>
        </div>
 
        <!-- Instructions -->
        <div class="mb-3">
            <label for="instructions" class="form-label fw-bold">Instructions to Bidders:</label>
            <textarea class="form-control" id="instructions" rows="3"></textarea>
        </div>
 
        <!-- Dates -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="submissionDeadline" class="form-label fw-bold">Submission Deadline:</label>
                <input type="date" class="form-control" id="submissionDeadline">
            </div>
            <div class="col-md-6 mb-3">
                <label for="openingDate" class="form-label fw-bold">Opening Date:</label>
                <input type="date" class="form-control" id="openingDate">
            </div>
        </div>
 
        <!-- Upload -->
        <div class="mb-3">
            <label for="tenderDocuments" class="form-label fw-bold">Attach Tender Documents:</label>
            <input class="form-control" type="file" id="tenderDocuments" multiple>
        </div>
 
        <!-- Conditional Suppliers List -->
        <div class="mb-3" id="restrictedSuppliersSection" style="display: none;">
            <label for="suppliersList" class="form-label fw-bold">Add Suppliers to Invite:</label>
            <select class="form-select" id="suppliersList" multiple>
                <option>Supplier A - Tech Supplies Ltd</option>
                <option>Supplier B - Nova Solutions</option>
                <option>Supplier C - EquiBuild Ltd</option>
            </select>
        </div>
       
        <!-- Action Buttons -->
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">Publish Tender</button>
            <button type="button" class="btn btn-outline-secondary">Save Draft</button>
            <button type="reset" class="btn btn-outline-dark">Cancel</button>
            <button type="button" class="btn btn-outline-info">Edit</button>
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
 
    function addManualItemRow() {
        const row = `
        <tr>
            <td><input type="text" class="form-control" placeholder="e.g. Item description"></td>
            <td><input type="number" class="form-control" value="1"></td>
            <td><input type="text" class="form-control" placeholder="pcs"></td>
            <td><input type="file" class="form-control"></td>
            <td><input type="text" class="form-control" placeholder="PR/2025/XXX"></td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">🗑</button></td>
        </tr>`;
        document.getElementById('manualItemsBody').insertAdjacentHTML('beforeend', row);
    }
 
  const procurementPlans = {
        '2025-DEP01': [
            { id: 1, name: 'Office Desks', plannedQty: 100 },
            { id: 2, name: 'UPS Systems', plannedQty: 10 }
        ],
        '2025-DEP02': [
            { id: 3, name: 'Audit Software', plannedQty: 5 },
            { id: 4, name: 'Laptops', plannedQty: 20 }
        ]
    };
 
    // Full item lookup for metadata
    const planItemData = {
        1: { name: 'Office Desks', plannedQty: 100 },
        2: { name: 'UPS Systems', plannedQty: 10 },
        3: { name: 'Audit Software', plannedQty: 5 },
        4: { name: 'Laptops', plannedQty: 20 }
    };
 
    const addedPlanItems = new Set();
 
    function loadPlanItemsForPlan() {
        const planId = document.getElementById('selectedProcurementPlan').value;
        const select = document.getElementById('planItemSelect');
        select.innerHTML = `<option selected disabled>-- Select Item --</option>`;
 
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
                <td><input type="number" class="form-control" name="qty_${itemId}" value="${item.plannedQty}" min="1" max="${item.plannedQty}"></td>
                <td><input type="file" class="form-control"></td>
                <td><input type="text" class="form-control" placeholder="PR/2025/XXX"></td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removePlanItemFromGrid(${itemId})">🗑</button></td>
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
 
@endsection