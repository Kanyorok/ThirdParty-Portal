@extends('layouts.app')

@section('title', 'Create Transfer Receipt')

@section('content')
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4">Transfer Receipt</h4>
    <div class="p-3 mb-4 rounded" style="background: linear-gradient(90deg,#e8f6ff,#f0f9ff); border: 1px solid #d0eaf8;">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle me-2 fs-4 text-primary"></i>
            <div>
                <div><i>Ensure you have an active main store set for your branch. All items will be received into your branch's main store.</i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">Select Transfer to Receive</h6>
        </div>
        <div class="card-body">
            @if(count($transfers) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Transfer ID</th>
                                <th>Transfer Date</th>
                                <th>From Branch</th>
                                <th>Items Count</th>
                                <th>GRN Allocations</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfers as $transfer)
                                @php
                                    $isHQ = $transfer->fromBranch && $transfer->fromBranch->IsHQ;
                                @endphp
                                <tr>
                                    <td>{{ $transfer->TransferID }}</td>
                                    <td>{{ $transfer->TransferDate }}</td>
                                    <td>{{ $transfer->fromBranch->Name ?? 'N/A' }}</td>
                                    <td>{{ $transfer->items->count() }}</td>
                                    <td>
                                        @if($isHQ)
                                            <span class="badge bg-info">
                                                <i class="fas fa-sort-amount-down"></i> FIFO Allocation
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Specific GRN Allocations
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                onclick="selectTransfer({{ $transfer->Id }})">
                                            Select
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No transfers available for receipt at your branch.
                </div>
            @endif
        </div>
    </div>

    <div id="receiptFormContainer" class="mt-4" style="display: none;">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Receipt Details</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('transactionsreceipts.store') }}" id="receiptForm">
                    @csrf
                    
                    <input type="hidden" name="TransferID" id="transferId">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="ReceivedDate" class="form-label">Receipt Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control" 
                                   id="ReceivedDate" 
                                   name="ReceivedDate" 
                                   value="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                                   required>
                        </div>
                        <div class="col-md-4">
                            <label for="ReceivedBy" class="form-label">Received By <span class="text-danger">*</span></label>
                            <input type="hidden" name="ReceivedBy" id="ReceivedBy" 
                                   value="{{ $currentUser->Id ?? auth()->id() }}">
                            <input type="text" class="form-control" 
                                   value="{{ $currentUser->Name ?? auth()->user()->Name }}" 
                                   readonly>
                        </div>
                        <div class="col-md-4">
                            <label for="GeneralRemarks" class="form-label">General Remarks</label>
                            <input type="text" class="form-control" 
                                   id="GeneralRemarks" 
                                   name="GeneralRemarks" 
                                   placeholder="Optional remarks">
                        </div>
                    </div>

                    <div class="alert alert-info mb-3">
                        <i class="fas fa-store me-2"></i>
                        <strong>Store Information:</strong> All items will be received into your branch's main store: 
                        <span id="storeInfo" class="fw-bold"></span>
                    </div>

                    <div id="itemsSection" class="mb-4">
                        <h5>Transfer Items</h5>
                        <div id="allocationInfo" class="alert alert-info mb-3" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="allocationInfoText"></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Dispatched Qty</th>
                                        <th>Store</th>
                                        <th>Received Qty <span class="text-danger">*</span></th>
                                        <th>Damaged Qty</th>
                                        <th>Discrepancy</th>
                                        <th>GRN Allocations</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-1"></i> Submit Receipt
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="cancelReceipt()">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="allocationModal" tabindex="-1" aria-labelledby="allocationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allocationModalLabel">GRN Batch Allocations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Item:</strong> <span id="modalItemName"></span><br>
                    <strong>Dispatched Quantity:</strong> <span id="modalDispatchedQty"></span><br>
                    <strong>Allocation Type:</strong> <span id="modalAllocationType" class="badge"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>GRN ID</th>
                                <th>Source</th>
                                <th>Unit Price</th>
                                <th>Allocated Quantity</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody id="allocationBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong id="totalAllocatedQty">0</strong></td>
                                <td><strong id="totalAllocatedValue">0.00</strong></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <span id="allocationFootNote"></span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let selectedTransferId = null;
    let transferItems = [];
    let mainStore = null;
    let isTransferFromHQ = false;
    let allocationModal = null; 
    $(document).ready(function() {
        const modalElement = document.getElementById('allocationModal');
        if (modalElement) {
            allocationModal = new bootstrap.Modal(modalElement);
        }
    });

    function selectTransfer(transferId) {
        selectedTransferId = transferId;
        
        $('#itemsBody').html('<tr><td colspan="8" class="text-center"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading transfer details...</td></tr>');
        
        fetch("{{ route('transactionsreceipts.get-transfer-items', '') }}/" + transferId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                transferItems = data.items || [];
                mainStore = data.main_store;
                isTransferFromHQ = data.is_hq || false; 
                
                console.log('Transfer data loaded:', {
                    itemsCount: transferItems.length,
                    isHQ: isTransferFromHQ,
                    mainStore: mainStore
                });
                
                $('#transferId').val(transferId);
                
                if (mainStore) {
                    $('#storeInfo').text(mainStore.StoreName);
                }
                
                $('#itemsBody').empty();
                
                transferItems.forEach((item, index) => {
                    const hasAllocations = item.batch_allocation && item.batch_allocation.length > 0;
                    const storeName = item.main_store ? item.main_store.StoreName : 'Main Store';
                    const storeId = item.main_store ? item.main_store.Id : '';
                    
                    let allocationButton = '';
                    if (hasAllocations) {
                        const batchCount = item.batch_allocation.length;
                        if (isTransferFromHQ) {
                            allocationButton = `
                                <button type="button" 
                                        class="btn btn-sm btn-info view-allocation-btn" 
                                        data-index="${index}">
                                    <i class="fas fa-layer-group"></i> View FIFO (${batchCount})
                                </button>
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-robot"></i> Auto-allocated
                                </div>
                            `;
                        } else {
                            allocationButton = `
                                <button type="button" 
                                        class="btn btn-sm btn-success view-allocation-btn" 
                                        data-index="${index}">
                                    <i class="fas fa-check-circle"></i> View Batches (${batchCount})
                                </button>
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-hand-pointer"></i> User-selected
                                </div>
                            `;
                        }
                    } else {
                        allocationButton = `<span class="text-muted small">No batches</span>`;
                    }
                    
                    const row = `
                        <tr>
                            <td>
                                ${item.item.ItemName}
                                <input type="hidden" name="items[${index}][item]" value="${item.Item}">
                                <input type="hidden" name="items[${index}][uom]" value="${item.UOM}">
                                <input type="hidden" name="items[${index}][unit_cost]" value="${item.UnitCost}">
                                <input type="hidden" name="items[${index}][dispatched_qty]" value="${item.DispatchedQty}">
                                <input type="hidden" name="items[${index}][store_id]" value="${storeId}">
                            </td>
                            <td>${item.DispatchedQty} ${item.UOMCode || ''}</td>
                            <td>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       value="${storeName}" 
                                       readonly>
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control form-control-sm received-qty" 
                                       name="items[${index}][received_qty]"
                                       value="${item.DispatchedQty}"
                                       min="0"
                                       step="0.01"
                                       required
                                       data-index="${index}">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control form-control-sm damaged-qty" 
                                       name="items[${index}][damaged_qty]"
                                       value="0"
                                       min="0"
                                       step="0.01"
                                       data-index="${index}">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control form-control-sm discrepancy-qty" 
                                       value="0"
                                       readonly
                                       style="background-color: #f8f9fa;"
                                       data-index="${index}">
                            </td>
                            <td>
                                ${allocationButton}
                            </td>
                            <td>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       name="items[${index}][remarks]"
                                       placeholder="Optional">
                            </td>
                        </tr>
                    `;
                    
                    $('#itemsBody').append(row);
                });
                
                let infoText = '';
                if (isTransferFromHQ) {
                    infoText = '<strong>FIFO Allocation:</strong> This transfer is from Headquarters. Batches were automatically allocated using First-In-First-Out method during approval.';
                } else {
                    infoText = '<strong>Manual Selection:</strong> This transfer has specific GRN batches that were manually selected by the sender during transfer creation.';
                }
                
                $('#allocationInfoText').html(infoText);
                $('#allocationInfo').show();
                
                $('#receiptFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#receiptFormContainer').offset().top - 20
                }, 500);
            })
            .catch(error => {
                console.error('Error loading transfer:', error);
                alert('Failed to load transfer details. Please try again.');
                $('#itemsBody').html('<tr><td colspan="8" class="text-center text-danger">Error loading transfer details</td></tr>');
            });
    }

    $(document).on('click', '.view-allocation-btn', function(e) {
        e.preventDefault();
        const index = $(this).data('index');
        console.log('View allocations clicked for index:', index);
        showAllocations(index);
    });

    function showAllocations(index) {
        console.log('showAllocations called with index:', index);
        console.log('Total items:', transferItems.length);
        
        if (index < 0 || index >= transferItems.length) {
            console.error('Invalid index:', index);
            alert('Error: Invalid item index');
            return;
        }
        
        const item = transferItems[index];
        console.log('Item data:', item);
        
        const allocations = item.batch_allocation || [];
        console.log('Allocations:', allocations);
        
        if (allocations.length === 0) {
            alert('No batch allocations found for this item');
            return;
        }
        
        $('#modalItemName').text(item.item.ItemName);
        $('#modalDispatchedQty').text(item.DispatchedQty + ' ' + (item.UOMCode || ''));
        
        const allocationTypeBadge = $('#modalAllocationType');
        if (isTransferFromHQ) {
            allocationTypeBadge.text('FIFO Allocation').removeClass().addClass('badge bg-info');
        } else {
            allocationTypeBadge.text('Manual Selection').removeClass().addClass('badge bg-success');
        }
        
        $('#allocationBody').empty();
        
        let totalQty = 0;
        let totalValue = 0;
        let procCount = 0;
        let trfCount = 0;
        
        allocations.forEach(allocation => {
            const quantity = parseFloat(allocation.quantity) || 0;
            const unitPrice = parseFloat(allocation.unit_price) || 0;
            const value = unitPrice * quantity;
            
            totalQty += quantity;
            totalValue += value;
            
            const sourceType = allocation.source_type || 'procurement';
            
            let sourceBadge = '';
            if (sourceType === 'transfer') {
                sourceBadge = '<span class="badge bg-info">TRF</span>';
                trfCount++;
            } else {
                sourceBadge = '<span class="badge bg-success">PROC</span>';
                procCount++;
            }
            
            const grnId = allocation.grn_id || 'N/A';
            const sourceNote = sourceType === 'transfer' ? 
                '<br><small class="text-muted">From previous transfer</small>' : 
                '<br><small class="text-muted">Original procurement</small>';
            
            const row = `
                <tr>
                    <td>${grnId}${sourceNote}</td>
                    <td>${sourceBadge}</td>
                    <td class="text-end">${unitPrice.toFixed(2)}</td>
                    <td class="text-end">${quantity.toFixed(2)}</td>
                    <td class="text-end">${value.toFixed(2)}</td>
                </tr>
            `;
            
            $('#allocationBody').append(row);
        });
        
        $('#totalAllocatedQty').text(totalQty.toFixed(2));
        $('#totalAllocatedValue').text(totalValue.toFixed(2));
        
        let footNote = '';
        if (isTransferFromHQ) {
            footNote = `FIFO: System automatically selected ${allocations.length} oldest batch(es) during approval.`;
        } else {
            if (procCount > 0 && trfCount > 0) {
                footNote = `Mixed sources: ${procCount} from procurement, ${trfCount} from transfers.`;
            } else if (procCount > 0) {
                footNote = `All ${procCount} batch(es) from original procurement.`;
            } else if (trfCount > 0) {
                footNote = `All ${trfCount} batch(es) from previous transfers.`;
            }
        }
        $('#allocationFootNote').text(footNote);
        
        if (allocationModal) {
            console.log('Showing modal...');
            allocationModal.show();
        } else {
            console.error('Modal instance not initialized');
            const modalElement = document.getElementById('allocationModal');
            if (modalElement) {
                allocationModal = new bootstrap.Modal(modalElement);
                allocationModal.show();
            } else {
                alert('Error: Modal element not found');
            }
        }
    }

    function cancelReceipt() {
        if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
            selectedTransferId = null;
            transferItems = [];
            mainStore = null;
            isTransferFromHQ = false;
            $('#receiptFormContainer').hide();
            $('#allocationInfo').hide();
            $('#storeInfo').text('');
            $('#itemsBody').empty();
        }
    }

    function updateDiscrepancy(index) {
        const $row = $(`input[data-index="${index}"]`).first().closest('tr');
        const receivedQty = parseFloat($row.find('.received-qty').val()) || 0;
        const dispatchedQty = parseFloat(transferItems[index]?.DispatchedQty) || 0;
        
        const discrepancy = receivedQty - dispatchedQty;
        
        const $discrepancyField = $row.find('.discrepancy-qty');
        $discrepancyField.val(discrepancy.toFixed(2));
        
        if (discrepancy > 0) {
            $discrepancyField.css('color', '#28a745'); 
        } else if (discrepancy < 0) {
            $discrepancyField.css('color', '#dc3545'); 
        } else {
            $discrepancyField.css('color', '#6c757d'); 
        }
    }

    $(document).on('input', '.received-qty, .damaged-qty', function() {
        const index = $(this).data('index');
        const $row = $(this).closest('tr');
        const receivedQty = parseFloat($row.find('.received-qty').val()) || 0;
        const damagedQty = parseFloat($row.find('.damaged-qty').val()) || 0;
        
        if ($(this).hasClass('received-qty')) {
            updateDiscrepancy(index);
        }
        
        if (damagedQty > receivedQty) {
            alert(`Damaged quantity (${damagedQty}) cannot exceed received quantity (${receivedQty})`);
            $row.find('.damaged-qty').val(0);
        }
    });

    $('#receiptForm').on('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        let hasDiscrepancies = false;
        let discrepancyDetails = [];
        
        $('.received-qty').each(function() {
            const index = $(this).data('index');
            const $row = $(this).closest('tr');
            const receivedQty = parseFloat($(this).val()) || 0;
            const damagedQty = parseFloat($row.find('.damaged-qty').val()) || 0;
            const discrepancyQty = parseFloat($row.find('.discrepancy-qty').val()) || 0;
            const dispatchedQty = parseFloat(transferItems[index]?.DispatchedQty) || 0;
            const itemName = transferItems[index]?.item?.ItemName || '';
            
            if (damagedQty > receivedQty) {
                alert(`Damaged quantity cannot exceed received quantity for item: ${itemName}`);
                isValid = false;
                return false;
            }
            
            if (discrepancyQty !== 0) {
                hasDiscrepancies = true;
                const type = discrepancyQty > 0 ? 'Surplus' : 'Shortage';
                discrepancyDetails.push(`${itemName}: ${type} of ${Math.abs(discrepancyQty)}`);
            }
        });
        
        if (!isValid) return;
        
        if (hasDiscrepancies) {
            const message = 'The following items have discrepancies:\n\n' + 
                          discrepancyDetails.join('\n') + 
                          '\n\nDo you want to proceed?';
            
            if (!confirm(message)) {
                return;
            }
        }
        
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...').prop('disabled', true);
        
        this.submit();
    });
</script>
@endpush

@push('styles')
<style>
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.7em;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .received-qty:focus, .damaged-qty:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    .discrepancy-qty {
        font-weight: 600;
    }
    #allocationModal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    .batch-badge {
        font-size: 0.7em;
        padding: 0.2em 0.4em;
        margin-left: 0.3em;
    }
    .view-allocation-btn {
        cursor: pointer;
    }
    .view-allocation-btn:hover {
        transform: scale(1.05);
        transition: transform 0.2s;
    }
</style>
@endpush