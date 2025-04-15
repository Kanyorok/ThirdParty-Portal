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
                    <table id="campaignTable"
                        class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>UOM</th>
                                <th>Expected Price</th>
                                <th>Actual Price</th>
                                <th>Urgency</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="RequisitionItemModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createRequistionItem">
                        <form action="{{ route('requisitionItem.store') }}" method="post" id="createRequistionItemForm">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="Module">Module <span class="text-danger">*</span></label>
                                <select class="form-control" name="Module" id="Module" required>
                                    <option selected disabled>Select a List</option>
                                    <option>Purchase Requisition</option>
                                    <option>Tender</option>

                                    {{-- @foreach ($MarketingLists as $MarketingList)
                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>
                                    @endforeach --}}
                                </select>

                                <p id="Module_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="ItemType">Item Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="ItemType" id="ItemType" required>
                                    <option selected disabled>Select a List</option>
                                    <option>Select a List</option>
                                    {{-- @foreach ($MarketingLists as $MarketingList)
                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>
                                    @endforeach --}}
                                </select>

                                <p id="ItemType_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="Item" class="form-label">Item </label>
                                <select class="form-control" name="Item" id="Item" required>
                                    <option selected disabled>Select a List</option>
                                    <option>Select a List</option>
                                    {{-- @foreach ($MarketingLists as $MarketingList)
                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>
                                    @endforeach --}}
                                </select>
                                <p id="Item_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Description">Description </label>
                                <textarea name="Description" id="Description" rows="3" class="form-control" maxlength="1000"></textarea>
                                <p id="Description_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            {{-- <div class="mb-3">
                                <label for="Description" class="form-label">Description <span class="text-danger">*</span></label>
                                <select class="form-control" name="Description" id="Description" required>
                                    <option selected disabled>select a Type</option>
                                    <option  >Select a List</option>

                                </select>
                                <p id="Description_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div> --}}
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
                                    <option selected disabled>Select a List</option>
                                    <option>Select a List</option>
                                    {{-- @foreach ($MarketingLists as $MarketingList)
                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>
                                    @endforeach --}}
                                </select>

                                <p id="UOM_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="ExpectedPrice">Expected Price </label>

                                <input type="number" class="form-control" id="ExpectedPrice" name="ExpectedPrice" required
                                    placeholder="Expected Price">

                                <p id="ExpectedPrice_error" class="invalid-feedback d-none error col-12" role="alert">
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="ActualPrice">Actual Price </label>

                                <input type="number" class="form-control" id="ActualPrice" name="ActualPrice" required
                                    placeholder="Actual Price">

                                <p id="ActualPrice_error" class="invalid-feedback d-none error col-12" role="alert">
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Urgency">Urgency </label>

                                <input type="number" class="form-control" id="Urgency" min="1" max="4"
                                    name="Urgency" required placeholder="Urgency">

                                <p id="Urgency" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start" data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createRequistionItemBtn" type="submit"><i
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
        $(function() {
            // $.fn.dataTable.ext.errMode = 'none';
            // fetchCampaignsTable();

            $(document).on('click', '.modal-create-item', function() {
                $(".modal-title").html('Add Item');
                $(".modal-item").addClass('d-none');
                $('#createRequistionItem').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createRequistionItemForm').submit(async function(e) {
                // alert('hello');
                e.preventDefault();
                if (await saveForm($(this), $('#createRequistionItemBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }

            });

            // $("#createRequistionItemBtn").click(function (e) {
            //     e.preventDefault();

            //     let form = $('#createRequistionItemForm')[0];
            //     let data = new FormData(form);

            //     $.ajax({
            //         url: "{{ route('requisitionItem.store') }}",
            //         type: "POST",
            //         data: data,
            //         dataType: "json",
            //         processData: false,
            //         contentType: false,
            //         success: function (response) {
            //             console.log(response);
            //             // Show success message or close modal here
            //         },
            //         error: function (xhr) {
            //             console.error(xhr.responseText);
            //             // Optional: Handle validation or other error display
            //         }
            //     });
            // });

            $("#MarketingList").select2({
                dropdownParent: $Modal,
            });

        });

        // function fetchCampaignsTable() {
        //     if (!$.fn.DataTable.isDataTable('#campaignTable')) {
        //         $('#campaignTable').DataTable({
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
        //         $('#campaignTable').DataTable().ajax.reload();
        //     }
        // }
    </script>
@endsection
