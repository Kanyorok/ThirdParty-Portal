@extends('layouts.app')
@section('title', 'Add Requisition')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
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
                            <th>Category</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>UOM</th>
                            <th>Quantity</th>
                            <th>Estimated Cost</th>
                            {{--                                <th>Actual Price</th>--}}
                            <th>Needed By</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created On</th>
                            {{--                                <th>Modified By</th> --}}
                            {{--                                <th>Modified On</th> --}}
                        </tr>

                        </thead>
                        <tbody>
                        @forelse($details as $item)
                            <tr>
                                <td>{{ $item->Id }}</td>
                                {{--                                <td>{{ $item->RequisitionID }}</td> --}}
                                {{--                                    <td>{{ $item->Module }}</td>--}}
                                <td>{{ $item->Type }}</td>
                                <td>{{ $item->Category }}</td>
                                <td>{{ $item->ItemName }}</td>
                                <td>{{ $item->Description }}</td>
                                <td>{{ $item->UOMx }}</td>
                                <td>{{ $item->Quantity }}</td>
                                <td>{{ number_format($item->ExpectedPrice, 2) }}</td>
                                <td>{{ $item->NeededBy }}</td>
                                <td>{{ $item->Urgency }}</td>
                                <td>{{ $item->Status }}</td>
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

                            <input type="hidden" name="CategoryId" id="CategoryId">
                            <div class="mb-3">
                                <label class="form-label" for="RequisitionNo">Requisition No </label>

                                <input type="text" class="form-control" id="RequisitionNo" name="RequisitionNo" required
                                       placeholder="Requisition No" Readonly>

                                <p id="RequisitionNo_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-labe1l" for="Type">Item Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="Type" id="Type" required>
                                    <option selected disabled>Select type</option>
                                    <option value="good">Good</option>
                                    <option value="service">Service</option>
                                    {{-- @foreach ($MarketingLists as $MarketingList)
                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>
                                    @endforeach --}}
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
                                <label class="form-label" for="Quantity">Quantity </label>

                                <input type="number" class="form-control" id="Quantity" name="Quantity" required
                                       placeholder="Quantity">
                                {{-- <textarea name="Quantity" id="Quantity" rows="3" class="form-control"
                                          maxlength="1000"></textarea> --}}
                                <p id="Quantity_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="UOM">UOM </label>

                                <select class="form-control" name="UOM" id="UOM" required>
                                    {{--                                    <option selected disabled>Select UOM</option> --}}

                                    {{-- @foreach ($MarketingLists as $MarketingList)
                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>
                                    @endforeach --}}
                                </select>

                                <p id="UOM_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="EstimatedPrice">Estimated Price </label>

                                <input type="number" class="form-control" id="EstimatedPrice" name="EstimatedPrice"
                                       readonly required>

                                <p id="EstimatedPrice_error" class="invalid-feedback d-none error col-12" role="alert">
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="NeededBy">Needed By </label>

                                <input type="date" class="form-control" id="NeededBy" name="NeededBy"
                                       required>

                                <p id="NeededBy_error" class="invalid-feedback d-none error col-12" role="alert">
                                </p>
                            </div>

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
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        const $Modal = $('#RequisitionItemModal');

        function getRequisitionIdFromUrl(){
            const pathSegments = window.location.pathname.split('/');
            // Example URL: /requisition/1 → '1' is the last segment
            return pathSegments[pathSegments.length - 1];
        }

        $(function() {
            // $.fn.dataTable.ext.errMode = 'none';
            // fetchCampaignsTable();

            $(document).on('click', '.modal-create-item', function() {

                $(".modal-title").html('Add Item');
                $('#RequisitionID').val(getRequisitionIdFromUrl());
                $(".modal-item").addClass('d-none');
                $('#createRequisitionItem').removeClass('d-none');
                $Modal.modal('show');

            });

            $('form#createRequisitionItemForm').submit(async function(e) {
                // alert($('#RequisitionID').val());
                e.preventDefault();
                if (await saveForm($(this), $('#createRequisitionItemBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }

            });


            $('#Type').on('change', function() {
                // alert('hello');
                let type = $(this).val();

                if (type !== '') {
                    // alert(type + 'eric');
                    $.ajax({
                        url: `/requisitionItem/getItem/${type}`,
                        type: 'GET',
                        success: function(response) {
                            // console.log('AJAX Response:', response);

                            $('#Item').empty().append('<option value="">Select Item</option>');
                            $.each(response.data, function(key, item) {
                                $('#Item').append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );

                            });
                        },
                        error: function(response) {
                            // alert('Failed to load items');
                            alert(response)
                            console.log(response)
                        }
                    });
                } else {

                    $('#Item').empty().append('<option value="">Select Item</option>')
                }
            })


            $('#Item').on('change', function() {
                // alert('hello');
                let item = $(this).val();

                if (item !== '') {
                    $.ajax({
                        url: `/requisitionItem/getItemDetails/${item}`,
                        type: 'GET',
                        success: function(response) {
                            if (response.data && response.data.length > 0) {
                                $.each(response.data, function(key, item) {
                                    $('#UOM').empty().append(
                                        `<option value="${item.UOM}">${item.UOM}</option>`
                                    );

                                    // $('#Description').val(item.Description || '');
                                    $('#EstimatedPrice').val(item.UnitPrice || '');
                                    $('#CategoryId').val(item.CategoryId ||
                                        ''); // Populate the hidden CategoryId field
                                    console.log('CategoryId:', item);
                                });
                            }
                        },
                        error: function(response) {
                            alert('Failed to load item details');
                            console.log(response);
                        }
                    });
                } else {
                    $('#UOM').empty().append('<option value="">Select UOM</option>');
                    // $('#Description').val('');
                    $('#ActualPrice').val('');
                    $('#CategoryId').val(''); // Clear the hidden CategoryId field
                }
            })

            $("#MarketingList").select2({
                dropdownParent: $Modal,
            });

        });

        // function fetchCampaignsTable() {
        //     if (!$.fn.DataTable.isDataTable('#requsitionItemsTable')) {
        //         $('#requsitionItemsTable').DataTable({
        //             processing: true,
        //             serverSide: true,
        //             responsive: true,
        //             // "order": [[3, 'asc']],
        //             "columnDefs": [
        //                 {"className": "text-center", "targets": [2]}
        //             ],
        //             ajax: {
        //                 url: getDocumentUrl(),
        //                 error: function (request) {
        //                     if (request.status === 400 && request.responseJSON.message) {
        //                         nWarning(request.responseJSON.message);
        //                     } else {
        //                         codeNotify(request.status);
        //                     }
        //                 }
        //             },
        //             columns: [
        //                 {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
        //                 {data: 'Label', name: 'Label'},
        //                 {data: 'Status', name: 'Status'},
        //                 {data: 'contacts_count', name: 'contacts_count'},
        //                 {data: 'CreatedOn', name: 'CreatedOn'},
        //                 {data: 'action', name: 'action', orderable: false, searchable: false},
        //             ], "oLanguage": {
        //                 "sEmptyTable": "no campaigns under this filter"
        //             }
        //         }).on('error', function () {
        //             nWarning("an issue occurred while loading campaigns.");
        //         });
        //     } else {
        //         $('#requsitionItemsTable').DataTable().ajax.reload();
        //     }
        // }
    </script>
@endsection
