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
            <a href="{{ route('requisition.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <button class="btn btn-primary modal-create-item ms-2" type="button">
                <i class="fas fa-plus-circle"></i> Add Items
            </button>
            
            {{-- Submit Button Logic --}}
            @if(isset($requisitionInfo))
                @php
                    $currentStatus = strtolower(trim($requisitionInfo->Status ?? 'draft'));
                    $hasItems = $details->count() > 0;
                    $canSubmit = in_array($currentStatus, ['draft']) && $hasItems;
                    $isAlreadyPending = $currentStatus === 'pending';
                    $isApproved = $currentStatus === 'approved';
                @endphp
                
                @if($canSubmit && !$isAlreadyPending && !$isApproved)
                    <button class="btn btn-success ms-2" type="button" id="submitForApproval">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                @elseif($isAlreadyPending)
                    <span class="badge bg-warning text-dark ms-2 p-2">
                        <i class="fas fa-clock"></i> Pending Approval
                    </span>
                @elseif($isApproved)
                    <span class="badge bg-success ms-2 p-2">
                        <i class="fas fa-check-circle"></i> Approved
                    </span>
                @elseif(!$hasItems)
                    <button class="btn btn-success ms-2" type="button" disabled title="Add items first">
                        <i class="fas fa-paper-plane"></i> Submit for Approval
                    </button>
                @endif
            @endif
        </div>
    </div>

    <!-- Alert for no items -->
    @if($details->isEmpty())
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle"></i> 
            No items added yet. Please add items before submitting for approval.
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
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
                                <th>Need ID</th>
                                <th>Est. Unit Cost</th>
                                <th>Estimated Cost</th>
                                <th>Urgency</th>
                                <th>Created By</th>
                                <th>Created On</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($details as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->Type }}</td>
                                    <td>{{ $item->ItemName }}</td>
                                    <td>{{ $item->Description }}</td>
                                    <td>{{ $item->UOM}}</td>
                                    <td>{{ $item->Quantity }}</td>
                                    <td>{{ $item->NeedRef ?? 'N/A' }}</td>
                                    <td>{{ isset($item->UnitPrice) && is_numeric($item->UnitPrice) ? number_format($item->UnitPrice, 2) : 'N/A' }}</td>
                                    <td>{{ isset($item->ExpectedPrice) && is_numeric($item->ExpectedPrice) ? number_format($item->ExpectedPrice, 2) : '0.00' }}</td>
                                    <td>{{ $item->Urgency }}</td>
                                    <td>{{ $item->UserName }}</td>
                                    <td>{{ $item->CreatedOn }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No requisition items found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <input type="hidden" name="RequisitionID" id="RequisitionID" value="{{ $id ?? '' }}">

                        <div class="mb-3">
                            <label class="form-label" for="Type">Item Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="Type" id="Type" required>
                                <option selected disabled value="">Select type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->Id }}">{{ $type->TypeName }}</option>
                                @endforeach
                            </select>
                            <p id="Type_error" class="invalid-feedback d-none error col-12" role="alert"></p>
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
                            </select>
                            <p id="UOM_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                        </div>

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
                            <label for="submitRemarks" class="form-label">Remarks (Optional)</label>
                            <textarea class="form-control" id="submitRemarks" name="remarks" rows="3" 
                                      placeholder="Add any additional comments..."></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Once submitted, this requisition will be sent to the approval workflow.
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
<script src="{{ asset('assets/libs/select2/css/select2.min.css') }}"></script>
<script src="{{ asset('assets/js/datatables.js') }}"></script>
<script>
    const $Modal = $('#RequisitionItemModal');
    const $SubmitModal = $('#submitConfirmationModal');
    const requisitionId = "{{ $id ?? '' }}";

    function getRequisitionIdFromUrl() {
        return requisitionId || window.location.pathname.split('/').pop();
    }

    $(function () {
        // Initialize DataTable if there are items
        @if($details->count() > 0)
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
        $('#Type, #Item, #UOM, #Urgency').select2({
            dropdownParent: $Modal,
            width: '100%'
        });

        // Show modal for adding item
        $(document).on('click', '.modal-create-item', function () {
            $('#RequisitionID').val(getRequisitionIdFromUrl());
            $Modal.modal('show');
        });

        // Submit for approval button
        $('#submitForApproval').on('click', function() {
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
        $('form#createRequisitionItemForm').submit(async function (e) {
            e.preventDefault();
            
            const submitBtn = $('#createRequisitionItemBtn');
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
            
            if (await saveForm($(this), submitBtn, true, true, true)) {
                $Modal.modal('hide');
                location.reload();
            } else {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Add Item');
            }
        });

        // On type change -> fetch items
        $('#Type').on('change', function () {
            let type = $(this).val();
            let requisitionId = getRequisitionIdFromUrl();

            if (type !== '' && type !== null) {
                $.ajax({
                    url: `/procurement/requisitionItem/getItem/${type}?requisition_id=${requisitionId}`,
                    type: 'GET',
                    success: function (response) {
                        $('#Item').empty().append('<option value="">Select Item</option>');
                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function (key, item) {
                                $('#Item').append(
                                    `<option value="${item.Id}">${item.ItemName}</option>`
                                );
                            });
                        }
                    },
                    error: function () {
                        alert('Failed to load items');
                    }
                });
            } else {
                $('#Item').empty().append('<option value="">Select Item</option>');
            }
        });

        // On item change -> fetch item details
        $('#Item').on('change', function () {
            let itemId = $(this).val();
            let requisitionId = getRequisitionIdFromUrl();

            if (itemId !== '' && itemId !== null) {
                $.ajax({
                    url: `/procurement/requisitionItem/getItemDetails/${itemId}?requisition_id=${requisitionId}`,
                    type: 'GET',
                    success: function (response) {
                        if (response.data && response.data.length > 0) {
                            let itemData = response.data[0];

                            // UOM
                            $('#UOM').empty().append(
                                `<option value="${itemData.UOMID}">${itemData.UOM}</option>`
                            );

                            // Est. Unit Cost
                            const unit = parseFloat(itemData.UnitPrice || 0) || 0;
                            $('#EstimatedPrice').val(unit);
                            $('#EstUnitCostDisplay').val(unit.toFixed(2));

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
                    error: function () {
                        alert('Failed to load item details');
                        resetItemFields();
                    }
                });
            } else {
                resetItemFields();
            }
        });

        function resetItemFields() {
            $('#UOM').empty().append('<option value="">Select UOM</option>');
            $('#EstimatedPrice').val('');
            $('#EstUnitCostDisplay').val('');
            $('#QtyAvailable').html('');
            $('#LineItemID').val('');
        }
    });
</script>
@endsection