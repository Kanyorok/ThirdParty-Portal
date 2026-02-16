@extends('layouts.app')
@section('title', 'Item Type List')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 15px;
        }
        .danger-box {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 15px;
        }
        .success-box {
            background-color: #d1e7dd;
            border-left: 4px solid #198754;
            padding: 15px;
            margin-bottom: 15px;
        }
    </style>
@endsection
@section('content')
@if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="container mt-5">
    <div class="card shadow rounded-4">
        <div class="card-header text-dark rounded-top-4 d-flex justify-content-between align-items-center"
             style="background-color: #add8e6;">
            <h4 class="mb-0">Item Type</h4>
            <a href="{{ route('itemtype.create') }}" class="btn btn-success">➕ Add New Item Type</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="itemtypeTable" class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Type Name</th>
                            <th>Stock Tracked?</th>
                            <th>Requires Tagging?</th>
                            <th>Is Active?</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($itemTypes as $key => $itemtype)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $itemtype->type->Description ?? 'N/A' }}</td>
                                <td>{!! $itemtype->StockTracked ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                                <td>{!! $itemtype->RequiresTagging ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                                <td>{!! $itemtype->Active ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-info"
                                            onclick="showItemModal('{{ $itemtype->type->Description ?? 'N/A' }}', '{{ $itemtype->StockTracked ? 'Yes' : 'No' }}', '{{ $itemtype->RequiresTagging ? 'Yes' : 'No' }}', '{{ $itemtype->Active ? 'Yes' : 'No' }}')"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-warning"
                                            onclick="editItemModal('{{ route('itemtype.update', $itemtype->Id) }}', '{{ $itemtype->TypeName }}', {{ $itemtype->StockTracked ? 1 : 0 }}, {{ $itemtype->RequiresTagging ? 1 : 0 }}, {{ $itemtype->Active ? 1 : 0 }}, '{{ $itemtype->Id }}', '{{ route('itemtype.checkItems', $itemtype->Id) }}')"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        @php
                                            $hasItems = $itemtype->items()->whereNull('DeletedOn')->exists();
                                        @endphp
                                        <button type="button" 
                                            class="btn btn-sm btn-danger {{ $hasItems ? 'disabled' : '' }}" 
                                            onclick="{{ $hasItems ? 'return false;' : "confirmDelete('{$itemtype->Id}')" }}" 
                                            title="{{ $hasItems ? 'Cannot delete - Item type is in use' : 'Delete' }}"
                                            {{ $hasItems ? 'disabled' : '' }}>
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <form id="delete-form-{{ $itemtype->Id }}"
                                              action="{{ route('itemtype.destroy', $itemtype->Id) }}"
                                              method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="showItemModal" tabindex="-1" aria-labelledby="showItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showItemModalLabel">Item Type Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Type Name:</strong> <span id="showTypeName"></span></p>
                <p><strong>Stock Tracked:</strong> <span id="showStockTracked"></span></p>
                <p><strong>Requires Tagging:</strong> <span id="showRequiresTagging"></span></p>
                <p><strong>Active:</strong> <span id="showActive"></span></p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editItemForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="enable_related_items" id="enableRelatedItems" value="0">
                <input type="hidden" name="disable_related_items" id="disableRelatedItems" value="0">

                <div class="modal-header">
                    <h5 class="modal-title" id="editItemModalLabel">Edit Item Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTypeName" class="form-label">Type Name<span class="text-danger">*</span></label>
                        <select class="form-select" id="editTypeName" name="TypeName" required>
                            <option value="">Select Item Type</option>
                            @foreach($itmTypes as $itmType)
                                <option value="{{ $itmType->ID }}">{{ $itmType->Description ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="StockTracked" value="0">
                        <input class="form-check-input" type="checkbox" id="editStockTracked" name="StockTracked" value="1">
                        <label class="form-check-label" for="editStockTracked">Stock Tracked</label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="RequiresTagging" value="0">
                        <input class="form-check-input" type="checkbox" id="editRequiresTagging" name="RequiresTagging" value="1">
                        <label class="form-check-label" for="editRequiresTagging">Requires Tagging</label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="Active" value="0">
                        <input class="form-check-input" type="checkbox" id="editActive" name="Active" value="1">
                        <label class="form-check-label" for="editActive">Active</label>
                    </div>

                    <div id="relatedItemsWarning" class="d-none">
                        <div class="warning-box">
                            <h6 class="mb-2">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <strong>Warning: Items Assigned</strong>
                            </h6>
                            <p class="mb-0" id="warningMessage"></p>
                        </div>
                    </div>

                    <div id="activationInfo" class="d-none">
                        <div class="success-box">
                            <h6 class="mb-2">
                                <i class="fas fa-info-circle text-success"></i>
                                <strong>Items Will Be Activated</strong>
                            </h6>
                            <p class="mb-0" id="activationMessage"></p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDisableModal" tabindex="-1" aria-labelledby="confirmDisableModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDisableModalLabel">
                    <i class="fas fa-exclamation-circle"></i> Critical Action Required
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="danger-box">
                    <h6 class="text-danger mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Impact Assessment</strong>
                    </h6>
                    <p class="mb-2">
                        This item type is currently assigned to 
                        <strong class="text-danger fs-5" id="itemCount"></strong> 
                        active item(s).
                    </p>
                </div>

                <div class="alert alert-warning mb-3">
                    <h6 class="alert-heading">
                        <i class="fas fa-bolt"></i> Automatic Actions
                    </h6>
                    <p class="mb-0">If you proceed, the following will happen automatically:</p>
                    <ul class="mb-0 mt-2">
                        <li>This item type will be <strong>deactivated</strong></li>
                        <li>All <span id="itemCount2"></span> related item(s) will be <strong>deactivated</strong></li>
                        <li>This action will be logged in the system</li>
                    </ul>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Note:</strong> You can reactivate these items individually later if needed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDisableBtn">
                    <i class="fas fa-check"></i> Yes, Deactivate All
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmEnableModal" tabindex="-1" aria-labelledby="confirmEnableModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="confirmEnableModalLabel">
                    <i class="fas fa-check-circle"></i> Confirm Activation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="success-box">
                    <h6 class="text-success mb-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Items Found</strong>
                    </h6>
                    <p class="mb-2">
                        This item type is assigned to 
                        <strong class="text-success fs-5" id="inactiveItemCount"></strong> 
                        inactive item(s).
                    </p>
                </div>

                <div class="alert alert-success mb-3">
                    <h6 class="alert-heading">
                        <i class="fas fa-arrow-up"></i> Automatic Actions
                    </h6>
                    <p class="mb-0">If you proceed, the following will happen automatically:</p>
                    <ul class="mb-0 mt-2">
                        <li>This item type will be <strong>activated</strong></li>
                        <li>All <span id="inactiveItemCount2"></span> related inactive item(s) will be <strong>activated</strong></li>
                        <li>This action will be logged in the system</li>
                    </ul>
                </div>

                <div class="alert alert-info mb-0">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Note:</strong> Only items that were previously inactive will be activated.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="confirmEnableBtn">
                    <i class="fas fa-check"></i> Yes, Activate All
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    let currentItemTypeId = null;
    let currentCheckUrl = null;
    let originalActive = null;
    let hasRelatedItems = false;
    let relatedItemsCount = 0;
    let hasInactiveItems = false;
    let inactiveItemsCount = 0;

    function showItemModal(typeName, stockTracked, requiresTagging, active) {
        document.getElementById('showTypeName').textContent = typeName;
        document.getElementById('showStockTracked').textContent = stockTracked;
        document.getElementById('showRequiresTagging').textContent = requiresTagging;
        document.getElementById('showActive').textContent = active;

        new bootstrap.Modal(document.getElementById('showItemModal')).show();
    }

    function editItemModal(action, typeId, stockTracked, requiresTagging, active, itemTypeId, checkUrl) {
        const form = document.getElementById('editItemForm');
        const typeSelect = document.getElementById('editTypeName');

        form.action = action;
        typeSelect.value = typeId;
        document.getElementById('editStockTracked').checked = stockTracked == 1;
        document.getElementById('editRequiresTagging').checked = requiresTagging == 1;
        document.getElementById('editActive').checked = active == 1;
        document.getElementById('disableRelatedItems').value = '0';
        document.getElementById('enableRelatedItems').value = '0';
        document.getElementById('relatedItemsWarning').classList.add('d-none');
        document.getElementById('activationInfo').classList.add('d-none');

        currentItemTypeId = itemTypeId;
        currentCheckUrl = checkUrl;
        originalActive = active;
        hasRelatedItems = false;
        relatedItemsCount = 0;
        hasInactiveItems = false;
        inactiveItemsCount = 0;

        new bootstrap.Modal(document.getElementById('editItemModal')).show();
    }

    document.getElementById('editActive').addEventListener('change', function() {
        const isChecked = this.checked;
        const wasActive = originalActive == 1;
        const wasInactive = originalActive == 0;
        
        if (wasActive && !isChecked && currentCheckUrl) {
            checkActiveItems();
        } 
        else if (wasInactive && isChecked && currentCheckUrl) {
            checkInactiveItems();
        } 
        else {
            document.getElementById('relatedItemsWarning').classList.add('d-none');
            document.getElementById('activationInfo').classList.add('d-none');
            document.getElementById('disableRelatedItems').value = '0';
            document.getElementById('enableRelatedItems').value = '0';
            hasRelatedItems = false;
            hasInactiveItems = false;
            relatedItemsCount = 0;
            inactiveItemsCount = 0;
        }
    });

    function checkActiveItems() {
        const warningDiv = document.getElementById('relatedItemsWarning');
        const warningMessage = document.getElementById('warningMessage');
        const activationDiv = document.getElementById('activationInfo');
        
        activationDiv.classList.add('d-none');
        warningMessage.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking for related items...';
        warningDiv.classList.remove('d-none');

        fetch(currentCheckUrl + '?check_type=active')
            .then(response => response.json())
            .then(data => {
                if (data.hasItems) {
                    hasRelatedItems = true;
                    relatedItemsCount = data.count;
                    
                    warningMessage.innerHTML = `
                        This item type is currently assigned to <strong class="text-danger">${data.count}</strong> active item(s).<br>
                        <small class="text-muted">
                            When you click "Update", you will be asked to confirm whether to deactivate all related items.
                        </small>
                    `;
                    
                    warningDiv.classList.remove('d-none');
                } else {
                    hasRelatedItems = false;
                    relatedItemsCount = 0;
                    warningDiv.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Error checking related items:', error);
                warningMessage.innerHTML = `
                    <span class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error checking related items. Please try again.
                    </span>
                `;
            });
    }

    function checkInactiveItems() {
        const activationDiv = document.getElementById('activationInfo');
        const activationMessage = document.getElementById('activationMessage');
        const warningDiv = document.getElementById('relatedItemsWarning');
        
        warningDiv.classList.add('d-none');
        activationMessage.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking for inactive items...';
        activationDiv.classList.remove('d-none');

        fetch(currentCheckUrl + '?check_type=inactive')
            .then(response => response.json())
            .then(data => {
                if (data.hasItems) {
                    hasInactiveItems = true;
                    inactiveItemsCount = data.count;
                    
                    activationMessage.innerHTML = `
                        This item type is assigned to <strong class="text-success">${data.count}</strong> inactive item(s).<br>
                        <small class="text-muted">
                            When you click "Update", you will be asked to confirm whether to activate all related items.
                        </small>
                    `;
                    
                    activationDiv.classList.remove('d-none');
                } else {
                    hasInactiveItems = false;
                    inactiveItemsCount = 0;
                    activationDiv.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Error checking inactive items:', error);
                activationMessage.innerHTML = `
                    <span class="text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error checking inactive items. Please try again.
                    </span>
                `;
            });
    }

    document.getElementById('editItemForm').addEventListener('submit', function(e) {
        const activeChecked = document.getElementById('editActive').checked;
        const wasActive = originalActive == 1;
        const wasInactive = originalActive == 0;
        
        if (wasActive && !activeChecked) {
            e.preventDefault();
            
            if (hasRelatedItems && relatedItemsCount > 0) {
                showDeactivationConfirmation();
            } else {
                fetch(currentCheckUrl + '?check_type=active')
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasItems) {
                            hasRelatedItems = true;
                            relatedItemsCount = data.count;
                            showDeactivationConfirmation();
                        } else {
                            document.getElementById('editItemForm').submit();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while checking related items. Please try again.');
                    });
            }
        }
        else if (wasInactive && activeChecked) {
            e.preventDefault();
            
            if (hasInactiveItems && inactiveItemsCount > 0) {
                showActivationConfirmation();
            } else {
                fetch(currentCheckUrl + '?check_type=inactive')
                    .then(response => response.json())
                    .then(data => {
                        if (data.hasItems) {
                            hasInactiveItems = true;
                            inactiveItemsCount = data.count;
                            showActivationConfirmation();
                        } else {
                            document.getElementById('editItemForm').submit();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while checking inactive items. Please try again.');
                    });
            }
        }
    });

    function showDeactivationConfirmation() {
        document.getElementById('itemCount').textContent = relatedItemsCount;
        document.getElementById('itemCount2').textContent = relatedItemsCount;
        
        const editModal = bootstrap.Modal.getInstance(document.getElementById('editItemModal'));
        editModal.hide();
        
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmDisableModal'));
        confirmModal.show();
    }

    function showActivationConfirmation() {
        document.getElementById('inactiveItemCount').textContent = inactiveItemsCount;
        document.getElementById('inactiveItemCount2').textContent = inactiveItemsCount;
        
        const editModal = bootstrap.Modal.getInstance(document.getElementById('editItemModal'));
        editModal.hide();
        
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmEnableModal'));
        confirmModal.show();
    }

    document.getElementById('confirmDisableBtn').addEventListener('click', function() {
        document.getElementById('disableRelatedItems').value = '1';
        
        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDisableModal'));
        confirmModal.hide();
        
        document.getElementById('editItemForm').submit();
    });

    document.getElementById('confirmEnableBtn').addEventListener('click', function() {
        document.getElementById('enableRelatedItems').value = '1';
        
        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmEnableModal'));
        confirmModal.hide();
        
        document.getElementById('editItemForm').submit();
    });

    document.getElementById('confirmDisableModal').addEventListener('hidden.bs.modal', function(e) {
        if (document.getElementById('disableRelatedItems').value === '0') {
            const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
            editModal.show();
        }
    });

    document.getElementById('confirmEnableModal').addEventListener('hidden.bs.modal', function(e) {
        if (document.getElementById('enableRelatedItems').value === '0') {
            const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
            editModal.show();
        }
    });

    function confirmDelete(Id) {
        if (confirm('⚠️ Are you sure you want to delete this item type?')) {
            document.getElementById('delete-form-' + Id).submit();
        }
    }

    $(document).ready(function () {
        $('#itemtypeTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            language: { emptyTable: "No item types found" }
        });
    });
</script>
@endsection