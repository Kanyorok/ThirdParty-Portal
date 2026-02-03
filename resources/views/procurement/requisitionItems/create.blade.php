@extends('layouts.app')
@section('title', 'Add Requisition')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">@yield('title')</h1>
        <button class="btn btn-primary float-end ms-2 modal-create-item" type="button">
            <i class="fas fa-plus-circle"></i> Add Items
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="requsitionItemsTable"
                        class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                            <tr>
                                <th>IDs</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Estimated Cost</th>
                                <th>Urgency</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($details as $item)
                                <tr>
                                    <td>{{ $item->Id }}</td>
                                    <td>{{ $item->Type }}</td>
                                    <td>{{ $item->Category }}</td>
                                    <td>{{ $item->ItemName }}</td>
                                    <td>{{ $item->Description }}</td>
                                    <td>{{ $item->Quantity }}</td>
                                    <td>{{ number_format($item->ExpectedPrice, 2) }}</td>
                                    <td>{{ $item->Urgency }}</td>
                                    <td>{{ $item->Status }}</td>
                                    <td>{{ $item->UserName }}</td>
                                    <td>{{ $item->CreatedOn }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">No requisition items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="RequisitionItemModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Item from Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createRequisitionItem">
                        <form action="{{ route('requisitionItem.store') }}" method="post" id="createRequisitionItemForm">
                            @csrf
                            <input type="hidden" name="RequisitionID" id="RequisitionID" value="">
                            <input type="hidden" name="CategoryId" id="CategoryId">
                            <input type="hidden" name="LineItemID" id="LineItemID">

                            <div class="mb-3">
                                <label class="form-label" for="RequisitionNo">Requisition No</label>
                                <input type="text" class="form-control" id="RequisitionNo" name="RequisitionNo" 
                                    placeholder="Requisition No" readonly>
                                <p id="RequisitionNo_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            @if(!$hasPlan)
                            <div class="mb-3">
                                <label class="form-label" for="Type">Item Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="Type" id="Type" required>
                                    <option value="" selected disabled>Select item type</option>
                                    @if(isset($types) && $types->count() > 0)
                                        @foreach($types as $type)
                                            <option value="{{ $type->Id }}">{{ $type->TypeName }}</option>
                                        @endforeach
                                    @else
                                        <option value="">No item types available</option>
                                    @endif
                                </select>
                                <p id="Type_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label for="Item" class="form-label">
                                    @if($hasPlan)
                                        Select Item from Plan <span class="text-danger">*</span>
                                    @else
                                        Select Item <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <select class="form-control" name="Item" id="Item" required>
                                    @if($hasPlan)
                                        <option value="" selected disabled>Loading items from plan...</option>
                                    @else
                                        <option value="" selected disabled>Select item type first</option>
                                    @endif
                                </select>
                                <small class="form-text text-muted">
                                    @if($hasPlan)
                                        Items shown are from your procurement plan with available quantities
                                    @else
                                        Select an item type above to load available items
                                    @endif
                                </small>
                                <p id="Item_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="AvailableQty">Available Quantity</label>
                                        <input type="text" class="form-control" id="AvailableQty" readonly 
                                            placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="UOM">UOM</label>
                                        <input type="text" class="form-control" id="UOM_Display" name="UOM" readonly 
                                            placeholder="Unit">
                                        <input type="hidden" name="UOM" id="UOM">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="Quantity">Request Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="Quantity" name="Quantity" 
                                    step="any" min="0.01" required placeholder="Enter quantity to request">
                                <small class="form-text text-muted">Must not exceed available quantity</small>
                                <p id="Quantity_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="EstimatedPrice">Unit Price <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="EstimatedPrice" name="EstimatedPrice"
                                    step="0.01" min="0" readonly required>
                                <p id="EstimatedPrice_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="TotalCost">Total Cost</label>
                                <input type="text" class="form-control" id="TotalCost" readonly placeholder="0.00">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="NeededBy">Needed By</label>
                                <input type="date" class="form-control" id="NeededBy" name="NeededBy">
                                <p id="NeededBy_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="Urgency">Urgency <span class="text-danger">*</span></label>
                                <select class="form-control" name="Urgency" id="Urgency" required>
                                    <option value="" selected disabled>Select urgency</option>
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
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        const $Modal = $('#RequisitionItemModal');

        function getRequisitionIdFromUrl() {
            const pathSegments = window.location.pathname.split('/');
            return pathSegments[pathSegments.length - 1];
        }

        $(function() {
            // Open modal and load items
            $(document).on('click', '.modal-create-item', function() {
                @if($hasPlan)
                $(".modal-title").html('Add Item from Plan');
                @else
                $(".modal-title").html('Add Item');
                @endif
                
                const requisitionId = getRequisitionIdFromUrl();
                $('#RequisitionID').val(requisitionId);
                $('#RequisitionNo').val('REQ-' + requisitionId);
                
                // Reset form
                $('#createRequisitionItemForm')[0].reset();
                $('#RequisitionID').val(requisitionId);
                @if($hasPlan)
                $('#Item').empty().append('<option value="" selected disabled>Loading items from plan...</option>');
                @else
                $('#Item').empty().append('<option value="" selected disabled>Select item type first</option>');
                $('#Type').val('');
                @endif
                $('#AvailableQty').val('');
                $('#UOM_Display').val('');
                $('#CategoryId').val('');
                $('#LineItemID').val('');
                $('#TotalCost').val('0.00');
                
                $(".modal-item").addClass('d-none');
                $('#createRequisitionItem').removeClass('d-none');
                $Modal.modal('show');
                
                @if($hasPlan)
                // Load items from plan immediately
                loadItems(requisitionId);
                @endif
            });

            // Load items (from plan or by type)
            function loadItems(requisitionId, type = null) {
                
                $.ajax({
                    url: type ? `/requisitionItem/getItems/${type}` : `/requisitionItem/getItems`,
                    type: 'GET',
                    data: {
                        requisition_id: requisitionId
                    },
                    beforeSend: function() {
                        $('#Item').empty().append('<option value="">Loading items from plan...</option>');
                    },
                    success: function(response) {
                        
                        $('#Item').empty();
                        
                        if (response.success && response.data && response.data.length > 0) {
                            $('#Item').append('<option value="" selected disabled>Select an item</option>');
                            
                            $.each(response.data, function(index, item) {
                                
                                const itemId = item.Id || '';
                                const itemName = item.Name || 'Unknown Item';
                                const availableQty = item.AvailableQuantity || 0;
                                const category = item.Category || '';
                                
                                if (itemId) {
                                    let optionText = `${itemName} (${category}) - Available: ${availableQty}`;
                                    
                                    const $option = $('<option></option>')
                                        .val(itemId)
                                        .text(optionText)
                                        .attr('data-uom', item.UOM || '')
                                        .attr('data-price', item.UnitPrice || 0)
                                        .attr('data-category', item.CategoryId || '')
                                        .attr('data-lineitem', item.PlanLineRef || item.LineItemID || '')
                                        .attr('data-available', availableQty)
                                        .attr('data-name', itemName);
                                    
                                    $('#Item').append($option);
                                }
                            });
                            
                            // Initialize Select2
                            $('#Item').select2({
                                dropdownParent: $Modal,
                                placeholder: "Select an item",
                                width: '100%'
                            });
                            
                        } else {
                            $('#Item').append('<option value="">No items available</option>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to load items:', {
                            status: xhr.status,
                            response: xhr.responseJSON,
                            error: error
                        });
                        
                        $('#Item').empty().append('<option value="">No items available for this type</option>');
                        
                        // Don't show alert popup - just log and show user-friendly message
                    }
                });
            }

            @if(!$hasPlan)
            // Handle Type selection - load items
            $('#Type').on('change', function() {
                const typeId = $(this).val();
                
                
                if (typeId !== '' && typeId !== null) {
                    const requisitionId = $('#RequisitionID').val();
                    
                    
                    loadItems(requisitionId, typeId);
                } else {
                    // Clear items when no type selected
                    $('#Item').empty().append('<option value="" selected disabled>Select item type first</option>');
                }
            });
            @endif

            // Handle Item selection
            $('#Item').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                
                // Get data from attributes
                const uom = selectedOption.attr('data-uom');
                const price = parseFloat(selectedOption.attr('data-price')) || 0;
                const categoryId = selectedOption.attr('data-category');
                const lineItemId = selectedOption.attr('data-lineitem');
                const availableQty = parseFloat(selectedOption.attr('data-available')) || 0;
                const itemName = selectedOption.attr('data-name');
                
                    itemName: itemName,
                    uom: uom,
                    price: price,
                    categoryId: categoryId,
                    lineItemId: lineItemId,
                    availableQty: availableQty
                });
                
                // Set values
                $('#UOM').val(uom);
                $('#UOM_Display').val(uom);
                $('#EstimatedPrice').val(price.toFixed(2));
                $('#CategoryId').val(categoryId);
                $('#LineItemID').val(lineItemId);
                $('#AvailableQty').val(availableQty.toFixed(2));
                
                // Set max quantity
                $('#Quantity').attr('max', availableQty);
                $('#Quantity').attr('placeholder', `Max: ${availableQty}`);
                
                // Clear quantity and total
                $('#Quantity').val('');
                $('#TotalCost').val('0.00');
            });

            // Calculate total cost when quantity changes
            $('#Quantity').on('input', function() {
                const quantity = parseFloat($(this).val()) || 0;
                const unitPrice = parseFloat($('#EstimatedPrice').val()) || 0;
                const availableQty = parseFloat($('#AvailableQty').val()) || 0;
                const totalCost = quantity * unitPrice;
                
                $('#TotalCost').val(totalCost.toFixed(2));
                
                // Validate quantity
                if (quantity > availableQty) {
                    $(this).addClass('is-invalid');
                    $('#Quantity_error').removeClass('d-none').text(`Quantity cannot exceed available quantity (${availableQty.toFixed(2)})`);
                } else {
                    $(this).removeClass('is-invalid');
                    $('#Quantity_error').addClass('d-none');
                }
            });

            // Form submission
            $('form#createRequisitionItemForm').submit(async function(e) {
                e.preventDefault();
                
                // Validate quantity against available
                const quantity = parseFloat($('#Quantity').val()) || 0;
                const availableQty = parseFloat($('#AvailableQty').val()) || 0;
                
                if (quantity > availableQty) {
                    alert(`Quantity (${quantity}) cannot exceed available quantity (${availableQty})`);
                    return false;
                }
                
                if (quantity <= 0) {
                    alert('Quantity must be greater than 0');
                    return false;
                }
                
                // Validate form
                if (!$(this)[0].checkValidity()) {
                    $(this)[0].reportValidity();
                    return false;
                }
                
                if (await saveForm($(this), $('#createRequisitionItemBtn'), true, true, true)) {
                    $Modal.modal('hide');
                    // Reload page to show new item
                    location.reload();
                }
            });
        });
    </script>
@endsection