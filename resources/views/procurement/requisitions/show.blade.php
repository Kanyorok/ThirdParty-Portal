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
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">@yield('title')</h1>
        <button class="btn btn-primary float-end ms-2 modal-create-item" type="button"><i class="fas fa-plus-circle"></i> Add
            Items
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
                            <th>ID</th>
                            <th>Type</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>UOM</th>
                            <th>Quantity</th>
                            <th>Estimated Cost</th>
                            <th>Urgency</th>
                            {{--                            <th>Status</th>--}}
                            <th>Created By</th>
                            <th>Created On</th>
                        </tr>

                        </thead>
                        <tbody>
                        @forelse($details as $item)
                            <tr>
                                <td>{{  $loop->iteration }}</td>
                                <td>{{ $item->Type }}</td>
{{--                                <td>{{ $item->Category }}</td>--}}
                                <td>{{ $item->ItemName }}</td>
                                <td>{{ $item->Description }}</td>
                                <td>{{ $item->UOM}}</td>
                                <td>{{ $item->Quantity }}</td>
                                <td>{{ number_format($item->ExpectedPrice, 2) }}</td>
{{--                                <td>{{ $item->NeededBy }}</td>--}}
                                <td>{{ $item->Urgency }}</td>
                                {{--                                <td>{{ $item->Status }}</td>--}}
                                <td>{{ $item->UserName }}</td>
                                <td>{{ $item->CreatedOn }}</td>
                                {{--                                <td>{{ $item->ModifiedBy }}</td> --}}
                                {{--                                <td>{{ $item->ModifiedOn }}</td> --}}
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center">No requisition items found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="RequisitionItemModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createRequisitionItem">
                        <form action="{{ route('requisitionItem.store') }}" method="post" id="createRequisitionItemForm">
                            @csrf
                            <input type="hidden" name="RequisitionID" id="RequisitionID" value="">

{{--                            <input type="hidden" name="CategoryId" id="CategoryId">--}}
{{--                            <div class="mb-3">--}}
{{--                                <label class="form-label" for="RequisitionNo">Requisition No </label>--}}

{{--                                <input type="text" class="form-control" id="RequisitionNo" name="RequisitionNo" required--}}
{{--                                       placeholder="Requisition No" Readonly>--}}

{{--                                <p id="RequisitionNo_error" class="invalid-feedback d-none error col-12" role="alert"></p>--}}
{{--                            </div>--}}

                            <div class="mb-3">
                                <label class="form-labe1l" for="Type">Item Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="Type" id="Type" required>
                                    <option selected disabled>Select type</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->Id }}">{{ $type->TypeName }}</option>
                                    @endforeach
                                </select>

                                <p id="Type_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="Item" class="form-label">Item </label>
                                <select class="form-control" name="Item" id="Item" required>
                                    <option selected disabled>Select item</option>

                                    {{-- @foreach ($MarketingLists as $MarketingList)
                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>
                                    @endforeach --}}
                                </select>
                                <p id="Item_error" class="invalid-feedback d-none error col-12" role="alert"></p>

                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="Quantity">Quantity <span
                                class="text-danger" id="QtyAvailable"></span></label>

                                <input type="number" class="form-control" id="Quantity" name="Quantity" required step="any"
                                       placeholder="Quantity">
                                {{-- <textarea name="Quantity" id="Quantity" rows="3" class="form-control"
                                          maxlength="1000"></textarea> --}}
                                <p id="Quantity_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="UOM">UOM </label>

                                <select class="form-control" name="UOM" id="UOM" required>
                                    
                                </select>

                                <p id="UOM_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                         
                            <input type="hidden" name="EstimatedPrice" id="EstimatedPrice">

{{--                            <div class="mb-3">--}}
{{--                                <label class="form-label" for="LineItemID">LineItemID </label>--}}

                                <input type="hidden" class="form-control" id="LineItemID" name="LineItemID"
                                       readonly>

{{--                                <p id="LineItemID_error" class="invalid-feedback d-none error col-12" role="alert">--}}
{{--                                </p>--}}
{{--                            </div>--}}

                            <div class="mb-3">
                                <label class="form-label" for="Urgency">Urgency </label>

                                <select class="form-control" name="Urgency" id="Urgency" required>
                                    <option selected disabled>Select urgency</option>
                                    <option value="1">Very High</option>
                                    <option value="2">High</option>
                                    <option value="3">Medium</option>
                                    <option value="4">Low</option>

                                </select>


                                <p id="Urgency" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createRequisitionItemBtn" type="submit"><i
                                        class="fas fa-save"></i> add item
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

        // Inject Requisition ID from URL or backend
        const requisitionId = "{{ $id ?? '' }}"; // fallback for safety

        function getRequisitionIdFromUrl(){
            // use global if available
            return requisitionId || window.location.pathname.split('/').pop();
        }

        $(function () {
            $(document).on('click', '.modal-create-item', function () {
                $(".modal-title").html('Add Item');
                $('#RequisitionID').val(getRequisitionIdFromUrl());
                $(".modal-item").addClass('d-none');
                $('#createRequisitionItem').removeClass('d-none');
                $Modal.modal('show');
            });
            
            $('form#createRequisitionItemForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createRequisitionItemBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $('#Type').on('change', function () {
                let type = $(this).val();
                let requisitionId = getRequisitionIdFromUrl();

                if (type !== '') {
                    $.ajax({
                        url: `/procurement/requisitionItem/getItem/${type}?requisition_id=${requisitionId}`,
                        type: 'GET',
                        success: function (response) {
                            $('#Item').empty().append('<option value="">Select Item</option>');
                            $.each(response.data, function (key, item) {
                                $('#Item').append(
                                    `<option value="${item.Id}">${item.ItemName}</option>`
                                );
                            });
                        },
                        error: function (response) {
                            alert('Failed to load items');
                            console.log(response);
                        }
                    });
                } else {
                    $('#Item').empty().append('<option value="">Select Item</option>');
                }
            });

            $('#Item').on('change', function () {
                let itemId = $(this).val();
                let requisitionId = getRequisitionIdFromUrl();

                if (itemId !== '') {
                    $.ajax({
                        url: `/procurement/requisitionItem/getItemDetails/${itemId}?requisition_id=${requisitionId}`,
                        type: 'GET',
                        success: function (response) {
                            if (response.data && response.data.length > 0) {
                                let itemData = response.data[0];

                                // Set UOM
                                $('#UOM').empty().append(
                                    `<option value="${itemData.UOMID}">${itemData.UOM}</option>`
                                );

                                // Set estimated price
                                $('#EstimatedPrice').val(itemData.UnitPrice || 0);
                                $('#LineItemID').val(item.LineItemID || '');
                                // Display available quantity
                                if (itemData.OriginalQty && itemData.OriginalQty > 0) {
                                    $('#QtyAvailable').text(`Available Qty: ${itemData.OriginalQty}`);
                                } else {
                                    $('#QtyAvailable').text('');
                                }
                            } else {
                                $('#UOM').empty().append('<option value="">Select UOM</option>');
                                $('#EstimatedPrice').val('');
                                $('#QtyAvailable').text('');
                            }
                        },
                        error: function (response) {
                            alert('Failed to load item details');
                            console.log(response);
                            $('#UOM').empty().append('<option value="">Select UOM</option>');
                            $('#EstimatedPrice').val('');
                            $('#QtyAvailable').text('');
                        }
                    });
                } else {
                    $('#UOM').empty().append('<option value="">Select UOM</option>');
                    // $('#Description').val('');
                    $('#EstimatedPrice').val(0);
                    $('#LineItemID').val('');
                    $('#EstimatedPrice').val('');
                    $('#QtyAvailable').text('');
                }
            });

            $("#MarketingList").select2({
                dropdownParent: $Modal,
            });
        });
</script>

@endsection
