@extends('layouts.app')
@section('title', 'Add Requisition Items')
@section('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
<style>
    .select2-container {
        width: 100% !important;
    }
</style>
@endsection

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h4 class="mb-0">Requisition Items</h4>
        @if(isset($requisitionInfo))
        <small class="text-muted">
            Requisition No: <strong>{{ $requisitionInfo->RequisitionNo ?? 'N/A' }}</strong> |
            Status: <strong>{{ $requisitionInfo->Status ?? 'Draft' }}</strong>
        </small>
        @endif
    </div>
    <div>
        <a href="{{ route('requisition.create') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>

        @if(isset($requisitionInfo))
        @php
        // Normalize status for comparison
        $currentStatus = strtolower(trim($requisitionInfo->Status ?? 'draft'));
        $hasItems = $details->count() > 0;

        // Determine if we can add items and submit
        $canAddItems = in_array($currentStatus, ['draft']);
        $canSubmit = in_array($currentStatus, ['draft']) && $hasItems;
        $isPending = $currentStatus === 'pending';
        $isApproved = $currentStatus === 'approved';
        $isRejected = $currentStatus === 'rejected';
        @endphp

        {{-- Add Items button - only for Draft --}}
        @if($canAddItems)
        <button class="btn btn-primary modal-create-item ms-2" type="button">
            <i class="fas fa-plus-circle"></i> Add Items
        </button>
        @endif

        {{-- Submit Button Logic --}}
        @if($canSubmit)
        <button class="btn btn-success ms-2" type="button" id="submitForApproval">
            <i class="fas fa-paper-plane"></i> Submit for Approval
        </button>
        @elseif(!$hasItems && $canAddItems)
        <button class="btn btn-success ms-2" type="button" disabled title="Add items first">
            <i class="fas fa-paper-plane"></i> Submit for Approval
        </button>
        @elseif($isPending)
        <span class="badge bg-warning text-dark ms-2 p-2">
            <i class="fas fa-clock"></i> Pending Approval
        </span>
        @elseif($isApproved)
        <span class="badge bg-success ms-2 p-2">
            <i class="fas fa-check-circle"></i> Approved
        </span>
        @elseif($isRejected)
        <span class="badge bg-danger ms-2 p-2">
            <i class="fas fa-times-circle"></i> Rejected
        </span>
        @endif
        @endif
    </div>
</div>

<!-- Alert for no items -->
@if($details->isEmpty() && isset($requisitionInfo) && strtolower($requisitionInfo->Status) === 'draft')
<div class="alert alert-warning" role="alert">
    <i class="fas fa-exclamation-triangle"></i>
    No items added yet. Please add items before submitting for approval.
</div>
@endif

<!-- Replace the table in show.blade.php with this enhanced version -->
<div class="table-responsive">
    <table id="requsitionItemsTable" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Item</th>
                <th>Description</th>
                <th>UOM</th>
                <th>Quantity</th>
                <th>Est. Unit Cost</th>
                <th>Estimated Cost</th>
                <th>Urgency</th>
                @if(isset($requisitionInfo) && strtolower($requisitionInfo->Status ?? '') === 'draft')
                <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($details as $item)
            <tr id="row-{{ $item->Id }}">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->Type }}</td>
                <td>
                    {{ $item->ItemName }}
                    Plan
                    </span>

                </td>
                <td>{{ $item->Description }}</td>
                <td>{{ $item->UOM }}</td>
                <td>
                    @if(isset($requisitionInfo) && strtolower($requisitionInfo->Status ?? '') === 'draft')
                    <!-- Editable quantity for draft status -->
                    <input type="number"
                        class="form-control form-control-sm plan-item-quantity"
                        data-line-id="{{ $item->Id }}"
                        data-unit-price="{{ $item->UnitPrice ?? 0 }}"
                        value="{{ $item->Quantity }}"
                        min="0.01"
                        step="any"
                        style="width: 100px;">
                    @else
                    {{ $item->Quantity }}
                    @endif
                </td>
                <td>{{ isset($item->UnitPrice) && is_numeric($item->UnitPrice) ? number_format($item->UnitPrice, 2) : 'N/A' }}</td>
                <td id="total-price-{{ $item->Id }}">
                    {{ isset($item->ExpectedPrice) && is_numeric($item->ExpectedPrice) ? number_format($item->ExpectedPrice, 2) : '0.00' }}
                </td>
                <td>
                    @php
                    $urgencyMap = [1 => 'Very High', 2 => 'High', 3 => 'Medium', 4 => 'Low'];
                    $urgencyClass = [1 => 'danger', 2 => 'warning', 3 => 'info', 4 => 'secondary'];
                    @endphp
                    <span class="badge bg-{{ $urgencyClass[$item->Urgency] ?? 'secondary' }}">
                        {{ $urgencyMap[$item->Urgency] ?? $item->Urgency }}
                    </span>
                </td>
                @if(isset($requisitionInfo) && strtolower($requisitionInfo->Status ?? '') === 'draft')
                <td>
                    <button type="button"
                        class="btn btn-sm btn-danger remove-plan-item"
                        data-line-id="{{ $item->Id }}"
                        title="Remove item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ isset($requisitionInfo) && strtolower($requisitionInfo->Status ?? '') === 'draft' ? '10' : '9' }}" class="text-center">
                    @if(isset($requisitionInfo->PlanRef) && $requisitionInfo->PlanRef)
                    No items available from the selected procurement plan.
                    @else
                    No requisition items found. Click "Add Items" to add manually.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($details->count() > 0)
        <tfoot>
            <tr class="table-active">
                <th colspan="7" class="text-end">Total Estimated Cost:</th>
                <th colspan="{{ isset($requisitionInfo) && strtolower($requisitionInfo->Status ?? '') === 'draft' ? '3' : '2' }}">
                    {{ number_format($details->sum('ExpectedPrice'), 2) }}
                </th>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<!-- Add alert if items were auto-populated -->
@if(isset($requisitionInfo->PlanRef) && $requisitionInfo->PlanRef && $details->count() > 0)
<div class="alert alert-info mt-3">
    <i class="fas fa-info-circle"></i>
    <strong>Items Auto-populated from Plan:</strong>
    These items were automatically added from your selected procurement plan.
    You can adjust quantities or remove items before submitting.
</div>
@endif

<!-- Add Item Modal -->
<div class="modal fade" id="RequisitionItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('requisitionItem.store') }}" method="post" id="createRequisitionItemForm">
                    @csrf
                    <div class="mb-3">
                        <label for="Type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="Type" id="Type" required>
                            <option selected disabled value="">Select Type</option>
                            @foreach($types as $type)
                            <option value="{{ $type->Id }}">{{ $type->TypeName ?? $type->Name ?? $type->Description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="Item" class="form-label">Item <span class="text-danger">*</span></label>
                        <select class="form-control" name="Item" id="Item" required>
                            <option selected disabled value="">Select item</option>
                        </select>
                        <p id="Item_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="Quantity">
                            Quantity <span class="text-danger">*</span>
                            <span id="QtyAvailable" class="ms-2"></span>
                        </label>
                        <input type="number" class="form-control" id="Quantity" name="Quantity" required step="any" placeholder="Quantity">
                        <p id="Quantity_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="EstUnitCostDisplay">Est. Unit Cost</label>
                        <input type="text" class="form-control" id="EstUnitCostDisplay" value="" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="UOM">UOM <span class="text-danger">*</span></label>
                        <select class="form-control" name="UOM" id="UOM" required>
                            <option value="">Select UOM</option>
                            @foreach($uoms as $uom)
                            <option value="{{ $uom->Id }}">{{ $uom->Code }}</option>
                            @endforeach
                        </select>
                        <p id="UOM_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>

                    <input type="hidden" name="RequisitionID" id="RequisitionID">
                    <input type="hidden" name="EstimatedPrice" id="EstimatedPrice">
                    <input type="hidden" class="form-control" id="LineItemID" name="LineItemID">

                    <div class="mb-3">
                        <label class="form-label" for="Urgency">Urgency <span class="text-danger">*</span></label>
                        <select class="form-control" name="Urgency" id="Urgency" required>
                            <option selected disabled value="">Select urgency</option>
                            <option value="1">Very High</option>
                            <option value="2">High</option>
                            <option value="3">Medium</option>
                            <option value="4">Low</option>
                        </select>
                        <p id="Urgency_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>

                    <hr>
                    <div class="mt-4">
                        <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn btn-primary float-end" id="createRequisitionItemBtn" type="submit">
                            <i class="fas fa-save"></i> Add Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="modal fade" id="submitConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit for Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('requisition.submit', $id ?? '') }}" method="POST" id="submitForm">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to submit this requisition for approval?</p>
                    <div class="mb-3">
                        <label for="submitRemarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="submitRemarks" name="remarks" rows="3" required
                            placeholder="Add any additional comments..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Once submitted, this requisition will be sent to the approval workflow and you will not be able to add more items.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="confirmSubmitBtn">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/libs/dataTables/jquery.dataTables.min.js') }}"></script>
<script>
    const $Modal = $('#RequisitionItemModal');
    const $SubmitModal = $('#submitConfirmationModal');
    const requisitionId = "{{ $id ?? '' }}";
    // Check if plan exists based on requisition info
    const requisitionInfo = @json($requisitionInfo);
    const hasPlan = {
        {
            isset($requisitionInfo - > PlanRef) && $requisitionInfo - > PlanRef ? 'true' : 'false'
        }
    };

    function getRequisitionIdFromUrl() {
        return requisitionId || window.location.pathname.split('/').pop();
    }

    // Function to fetch items
    function fetchItems(type = null) {
        let requisitionId = getRequisitionIdFromUrl();
        let url = `/procurement/requisitionItem/getItems/${type}?requisition_id=${requisitionId}`;

        // If type is null (for plan), adjust URL
        if (!type) {
            url = `/procurement/requisitionItem/getItems?requisition_id=${requisitionId}`;
        }

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#Item').empty().append('<option value="">Select Item</option>');
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(key, item) {
                        $('#Item').append(
                            `<option value="${item.Id}">${item.Name}</option>`
                        );
                    });
                }
            },
            error: function() {
                alert('Failed to load items');
            }
        });
    }

    $(document).ready(function() {

        // Initialize DataTable if there are items
        @if(count($details) > 0)
        $('#requsitionItemsTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            language: {
                emptyTable: "No items added yet"
            }
        });
        @endif

        // Initialize Select2
        try {
            $('#Type, #Item, #UOM, #Urgency').select2({
                dropdownParent: $Modal,
                width: '100%'
            });
        } catch (error) {
            console.error('Select2 initialization error:', error);
        }

        // Show modal for adding item - FIXED
        $('.modal-create-item').on('click', function(e) {
            e.preventDefault();

            const reqId = getRequisitionIdFromUrl();

            $('#RequisitionID').val(reqId);

            // Reset form
            $('#createRequisitionItemForm')[0].reset();
            $('#Item').empty().append('<option value="">Select Item</option>');
            $('#UOM').val('').trigger('change'); // Reset UOM selection
            $('#EstUnitCostDisplay').prop('readonly', true); // Reset readonly

            // Handle Plan vs Manual logic
            if (hasPlan) {
                // Hide Type selection
                $('#Type').closest('.mb-3').hide();
                $('#Type').removeAttr('required');

                // Auto-load items from plan
                fetchItems(null);
            } else {
                // Show Type selection
                $('#Type').closest('.mb-3').show();
                $('#Type').attr('required', 'required');
                // Reset type selection
                $('#Type').val('').trigger('change');
            }

            // Show modal
            $Modal.modal('show');
        });

        // Submit for approval button
        $('#submitForApproval').on('click', function(e) {
            e.preventDefault();

            const itemCount = $('#requsitionItemsTable tbody tr').not(':has(td[colspan])').length;

            if (itemCount === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Items',
                        text: 'Please add at least one item before submitting for approval.',
                    });
                } else {
                    alert('Please add at least one item before submitting for approval.');
                }
                return;
            }

            $SubmitModal.modal('show');
        });

        // Handle submit form
        $('#submitForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $('#confirmSubmitBtn');
            submitBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...'
            );

            // Submit the form normally (not AJAX)
            this.submit();
        });

        // Handle form submission for adding items
        $('#createRequisitionItemForm').on('submit', async function(e) {
            e.preventDefault();

            const submitBtn = $('#createRequisitionItemBtn');
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

            try {
                // Check if saveForm function exists
                if (typeof saveForm === 'function') {
                    if (await saveForm($(this), submitBtn, true, true, true)) {
                        $Modal.modal('hide');
                        location.reload();
                    } else {
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Add Item');
                    }
                } else {
                    // Fallback: submit form directly
                    this.submit();
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Add Item');
                alert('Error adding item. Please try again.');
            }
        });

        // On type change -> fetch items
        $('#Type').on('change', function() {
            let type = $(this).val();

            if (type !== '' && type !== null) {
                fetchItems(type);
            } else {
                $('#Item').empty().append('<option value="">Select Item</option>');
            }
        });

        // On item change -> fetch item details
        $('#Item').on('change', function() {
            let itemId = $(this).val();
            let requisitionId = getRequisitionIdFromUrl();

            if (itemId !== '' && itemId !== null) {
                $.ajax({
                    url: `/procurement/requisitionItem/getItemDetails/${itemId}?requisition_id=${requisitionId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.data && response.data.length > 0) {
                            let itemData = response.data[0];

                            // UOM
                            if (itemData.UOMID) {
                                $('#UOM').val(itemData.UOMID).trigger('change');
                            } else {
                                $('#UOM').val('').trigger('change');
                            }

                            // Est. Unit Cost
                            const unit = parseFloat(itemData.UnitPrice || 0) || 0;
                            $('#EstimatedPrice').val(unit);
                            $('#EstUnitCostDisplay').val(unit.toFixed(2));

                            // Make price editable if 0
                            if (unit === 0) {
                                $('#EstUnitCostDisplay').prop('readonly', false);
                            } else {
                                $('#EstUnitCostDisplay').prop('readonly', true);
                            }

                            // LineItem ID
                            $('#LineItemID').val(itemData.LineItemID || '');

                            // Remaining Qty
                            let remainingQty = parseFloat(itemData.RemainingQty ?? 0);
                            if (!isNaN(remainingQty)) {
                                if (remainingQty > 0) {
                                    $('#QtyAvailable').html(
                                        `<span class="badge bg-info text-dark">Available: ${remainingQty}</span>`
                                    );
                                } else {
                                    $('#QtyAvailable').html(
                                        `<span class="badge bg-danger">Not Available</span>`
                                    );
                                }
                            } else {
                                $('#QtyAvailable').html('');
                            }
                        } else {
                            resetItemFields();
                        }
                    },
                    error: function() {
                        alert('Failed to load item details');
                        resetItemFields();
                    }
                });
            } else {
                resetItemFields();
            }
        });

        function resetItemFields() {
            $('#UOM').val('').trigger('change');
            $('#EstimatedPrice').val('');
            $('#EstUnitCostDisplay').val('').prop('readonly', true);
            $('#QtyAvailable').html('');
            $('#LineItemID').val('');
        }


        // Update EstimatedPrice when EstUnitCostDisplay changes (for manual entry)
        $('#EstUnitCostDisplay').on('input', function() {
            if (!$(this).prop('readonly')) {
                $('#EstimatedPrice').val($(this).val());
            }
        });
        // Delete item handler
        $(document).on('click', '.remove-plan-item', function(e) {
            e.preventDefault();
            const itemId = $(this).data('line-id');

            if (confirm('Are you sure you want to delete this item?')) {
                $.ajax({
                    url: '/procurement/requisitionLine/' + itemId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                         location.reload();
                    },
                    error: function(xhr) {
                        alert('Error deleting item');
                        console.error(xhr);
                    }
                });
            }
        });
    });
</script>
@endsection