@extends('layouts.app')

@section('title', 'Create Transfer Receipt')

@section('content')
<div class="container bg-white shadow rounded p-4">
    <h4 class="mb-4"> Transfer Receipt</h4>
    <div class="p-3 mb-4 rounded" style="background: linear-gradient(90deg,#e8f6ff,#f0f9ff); border: 1px solid #d0eaf8;">
        <div class="d-flex align-items-start">
            <i class="fas fa-info-circle me-2 fs-4 text-primary"></i>
            <div>
                <div><i>Ensure you have an active main store set for your branch, All items will be received into your branch's main store.</i></div>
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
                                    $hasAllocations = false;
                                    foreach ($transfer->items as $item) {
                                        if (!empty($item->BatchAllocation)) {
                                            $hasAllocations = true;
                                            break;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $transfer->TransferID }}</td>
                                    <td>{{ $transfer->TransferDate}}</td>
                                    <td>{{ $transfer->fromBranch->Name ?? 'N/A' }}</td>
                                    <td>{{ $transfer->items->count() }}</td>
                                    <td>
                                        @if($hasAllocations)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Specific GRN Allocations
                                            </span>
                                        @else
                                            <span class="badge bg-info">
                                                <i class="fas fa-sort-amount-down"></i> FIFO Allocation
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

    {{-- Receipt Form (hidden by default) --}}
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

                    {{-- Store Information --}}
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-store me-2"></i>
                        <strong>Store Information:</strong> All items will be received into your branch's main store: 
                        <span id="storeInfo" class="fw-bold"></span>
                    </div>

                    {{-- Items Section --}}
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

{{-- GRN Allocation Details Modal --}}
<div class="modal fade" id="allocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">GRN Batch Allocations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Item:</strong> <span id="modalItemName"></span><br>
                    <strong>Dispatched Quantity:</strong> <span id="modalDispatchedQty"></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>GRN ID</th>
                                <th>Unit Price</th>
                                <th>Allocated Quantity</th>
                                <th>Total Value</th>
                                <th>Age (Days)</th>
                            </tr>
                        </thead>
                        <tbody id="allocationBody"></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                <td><strong id="totalAllocatedQty">0</strong></td>
                                <td><strong id="totalAllocatedValue">0.00</strong></td>
                                <td></td>
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

    function selectTransfer(transferId) {
        selectedTransferId = transferId;
        
        // Show loading
        $('#itemsBody').html('<tr><td colspan="8" class="text-center"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Loading transfer details...</td></tr>');
        
        // Fetch transfer items
        fetch("{{ route('transactionsreceipts.get-transfer-items', '') }}/" + transferId)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                transferItems = data.items;
                mainStore = data.main_store;
                
                $('#transferId').val(transferId);
                
                // Update store info
                if (mainStore) {
                    $('#storeInfo').text(mainStore.StoreName);
                }
                
                // Populate items table
                $('#itemsBody').empty();
                
                let hasSpecificAllocations = false;
                let fifoAllocations = false;
                
                data.items.forEach((item, index) => {
                    const hasAllocations = item.batch_allocation && item.batch_allocation.length > 0;
                    if (hasAllocations) {
                        hasSpecificAllocations = true;
                    } else if (item.allocation_type === 'fifo') {
                        fifoAllocations = true;
                    }
                    
                    // Get store name
                    const storeName = item.main_store ? item.main_store.StoreName : 'Main Store';
                    const storeId = item.main_store ? item.main_store.Id : '';
                    
                    $('#itemsBody').append(`
                        <tr>
                            <td>
                                ${item.item.ItemName}
                                <input type="hidden" name="items[${index}][item]" value="${item.Item}">
                                <input type="hidden" name="items[${index}][uom]" value="${item.UOM}">
                                <input type="hidden" name="items[${index}][unit_cost]" value="${item.UnitCost}">
                                <input type="hidden" name="items[${index}][dispatched_qty]" value="${item.DispatchedQty}">
                                <input type="hidden" name="items[${index}][store_id]" value="${storeId}">
                            </td>
                            <td>${item.DispatchedQty}</td>
                            <td>
                                <input type="text" 
                                       class="form-control" 
                                       value="${storeName}" 
                                       readonly>
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control received-qty" 
                                       name="items[${index}][received_qty]"
                                       value="${item.DispatchedQty}"
                                       min="0"
                                       step="0.01"
                                       required
                                       data-index="${index}">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control damaged-qty" 
                                       name="items[${index}][damaged_qty]"
                                       value="0"
                                       min="0"
                                       step="0.01"
                                       data-index="${index}">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control discrepancy-qty" 
                                       name="items[${index}][discrepancy_qty]"
                                       value="0"
                                       readonly
                                       style="background-color: #f8f9fa;"
                                       data-index="${index}">
                            </td>
                            <td>
                                ${hasAllocations ? 
                                    `<button type="button" class="btn btn-sm btn-info" onclick="showAllocations(${index})">
                                        <i class="fas fa-layer-group"></i> View Allocations
                                    </button>` :
                                    `<span class="text-muted">FIFO Allocation</span>`
                                }
                            </td>
                            <td>
                                <input type="text" 
                                       class="form-control" 
                                       name="items[${index}][remarks]"
                                       placeholder="Item remarks">
                            </td>
                        </tr>
                    `);
                });
                
                // Update allocation info
                let infoText = '';
                if (hasSpecificAllocations) {
                    infoText = 'This transfer has specific GRN batch allocations. Costs will be tracked per batch.';
                } else if (fifoAllocations) {
                    infoText = 'This transfer will use FIFO (First-In-First-Out) allocation from source.';
                } else {
                    infoText = 'This transfer has no specific allocations and will use default costing.';
                }
                
                $('#allocationInfoText').text(infoText);
                $('#allocationInfo').show();
                
                // Show receipt form
                $('#receiptFormContainer').show();
                $('html, body').animate({
                    scrollTop: $('#receiptFormContainer').offset().top
                }, 500);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load transfer details.');
            });
    }

    function showAllocations(index) {
        const item = transferItems[index];
        const allocations = item.batch_allocation || [];
        
        $('#modalItemName').text(item.item.ItemName);
        $('#modalDispatchedQty').text(item.DispatchedQty);
        
        $('#allocationBody').empty();
        
        let totalQty = 0;
        let totalValue = 0;
        
        allocations.forEach(allocation => {
            const value = allocation.unit_price * allocation.quantity;
            totalQty += allocation.quantity;
            totalValue += value;
            
            const ageDays = allocation.age_days || 'N/A';
            
            $('#allocationBody').append(`
                <tr>
                    <td>${allocation.grn_id}</td>
                    <td>${allocation.unit_price.toFixed(2)}</td>
                    <td>${allocation.quantity}</td>
                    <td>${value.toFixed(2)}</td>
                    <td>${ageDays}</td>
                </tr>
            `);
        });
        
        $('#totalAllocatedQty').text(totalQty);
        $('#totalAllocatedValue').text(totalValue.toFixed(2));
        
        new bootstrap.Modal('#allocationModal').show();
    }

    function cancelReceipt() {
        selectedTransferId = null;
        transferItems = [];
        mainStore = null;
        $('#receiptFormContainer').hide();
        $('#allocationInfo').hide();
        $('#storeInfo').text('');
    }

    // Calculate and update discrepancy
    function updateDiscrepancy(index) {
        const $row = $(`input[data-index="${index}"]`).first().closest('tr');
        const receivedQty = parseFloat($row.find('.received-qty').val()) || 0;
        const dispatchedQty = parseFloat(transferItems[index]?.DispatchedQty) || 0;
        
        const discrepancy = receivedQty - dispatchedQty;
        
        // Update discrepancy field
        const $discrepancyField = $row.find('.discrepancy-qty');
        $discrepancyField.val(discrepancy.toFixed(2));
        
        // Color code the discrepancy field
        if (discrepancy > 0) {
            $discrepancyField.css('color', '#28a745'); // Green for surplus
        } else if (discrepancy < 0) {
            $discrepancyField.css('color', '#dc3545'); // Red for shortage
        } else {
            $discrepancyField.css('color', '#6c757d'); // Gray for exact match
        }
    }

    // Validate received and damaged quantities
    $(document).on('input', '.received-qty, .damaged-qty', function() {
        const index = $(this).data('index');
        const $row = $(this).closest('tr');
        const receivedQty = parseFloat($row.find('.received-qty').val()) || 0;
        const damagedQty = parseFloat($row.find('.damaged-qty').val()) || 0;
        
        // Update discrepancy when received qty changes
        if ($(this).hasClass('received-qty')) {
            updateDiscrepancy(index);
        }
        
        // Validate damaged quantity doesn't exceed received quantity
        if (damagedQty > receivedQty) {
            alert(`Damaged quantity (${damagedQty}) cannot exceed received quantity (${receivedQty})`);
            $row.find('.damaged-qty').val(0);
        }
    });

    // Form submission
    $('#receiptForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate all items
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
            
            // Validate damaged quantity
            if (damagedQty > receivedQty) {
                alert(`Damaged quantity cannot exceed received quantity for item: ${itemName}`);
                isValid = false;
                return false;
            }
            
            // Track discrepancies
            if (discrepancyQty !== 0) {
                hasDiscrepancies = true;
                const type = discrepancyQty > 0 ? 'Surplus' : 'Shortage';
                discrepancyDetails.push(`${itemName}: ${type} of ${Math.abs(discrepancyQty)}`);
            }
        });
        
        if (!isValid) return;
        
        // Warn about discrepancies
        if (hasDiscrepancies) {
            const message = 'The following items have discrepancies:\n\n' + 
                          discrepancyDetails.join('\n') + 
                          '\n\nDo you want to proceed?';
            
            if (!confirm(message)) {
                return;
            }
        }
        
        // Show loading
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...').prop('disabled', true);
        
        // Submit form
        this.submit();
    });
</script>
@endpush