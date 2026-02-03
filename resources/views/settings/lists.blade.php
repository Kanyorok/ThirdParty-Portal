@extends('layouts.app')

@section('title','Static Lists')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.5.0/css/rowReorder.dataTables.css">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-3 col-xl-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Codes</h5>
                </div>
                <div class="list-group list-group-flush" role="tablist">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#IndustriesTab"
                       onclick="fetchIndustries();" role="tab">
                        Industries
                    </a>
                    <a class="list-group-item list-group-item-action " data-bs-toggle="list" href="#TicketCategoriesTab"
                       onclick="fetchTicketCategories();" role="tab">
                        Ticket Categories
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#CustomerTypesTab"
                       onclick="fetchCustomerTypesTab();" role="tab">
                        Customer Types
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#MarketingModesTab"
                       onclick="fetchMarketingModes();" role="tab">
                        Marketing Modes
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#CustomerResponsesTab"
                       onclick="fetchCustomerResponses();" role="tab">
                        Customer Responses
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#LeadLossReasonTab"
                       onclick="fetchLeadLossReasonTab();" role="tab">
                        Lead Loss Reason
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list"
                       href="#ProductDevelopmentStagesTab"
                       onclick="fetchProductDevelopmentStagesTab();" role="tab">
                        Product Dev. Stages
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list"
                       href="#MeetingRoomsTab"
                       onclick="fetchMeetingRoomsTable();" role="tab">
                        Meeting Rooms
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list"
                       href="#CurrenciesTab"
                       onclick="fetchCurrenciesTable();" role="tab">
                        Currencies
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list"
                       href="#{{ \App\Enums\LocalityTypeEnum::County->value }}Tab"
                       onclick="fetchCountiesTab();" role="tab">
                        Counties
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list"
                       href="#{{ \App\Enums\LocalityTypeEnum::City->value }}Tab"
                       onclick="fetchCitiesTab();" role="tab">
                        Cities
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-9 col-xl-10">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="IndustriesTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary list-action-add"
                                        data-type="Industries"><i class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Industries</h5>
                        </div>
                        <div class="card-body">
                            <table id="IndustriesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Order</th>
                                    <th>Description</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="MarketingModesTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary list-action-add"
                                        data-type="MarketingModes"><i class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Marketing Modes</h5>
                        </div>
                        <div class="card-body">
                            <table id="MarketingModesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Order</th>
                                    <th>Description</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="TicketCategoriesTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary list-action-add"
                                        data-type="TicketCategories"><i class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Ticket Categories</h5>
                        </div>
                        <div class="card-body">
                            <table id="TicketCategoriesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Order</th>
                                    <th>Description</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="CustomerResponsesTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary list-action-add"
                                        data-type="CustomerResponses"><i class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Customer Responses</h5>
                        </div>
                        <div class="card-body">
                            <table id="CustomerResponsesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Order</th>
                                    <th>Description</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="LeadLossReasonTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary list-action-add"
                                        data-type="LeadLossReason"><i class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Lead Loss Reasons</h5>
                        </div>
                        <div class="card-body">
                            <table id="LeadLossReasonTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Order</th>
                                    <th>Description</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="CustomerTypesTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary list-action-add"
                                        data-type="CustomerTypes"><i class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Customer Types</h5>
                        </div>
                        <div class="card-body">
                            <table id="CustomerTypesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Order</th>
                                    <th>Description</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="ProductDevelopmentStagesTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary list-action-add"
                                        data-type="ProductDevelopmentStages"><i class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Product Development Stages</h5>
                        </div>
                        <div class="card-body">
                            <table id="ProductDevelopmentStagesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Order</th>
                                    <th>Description</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="{{ \App\Enums\LocalityTypeEnum::County->value }}Tab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary location-action-add"
                                        data-in_source=""
                                        data-type="{{ \App\Enums\LocalityTypeEnum::County->value }}"
                                        data-name="{{ \App\Enums\LocalityTypeEnum::County->name }}"><i
                                        class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Counties</h5>
                        </div>
                        <div class="card-body">
                            <table id="CountyTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="{{ \App\Enums\LocalityTypeEnum::City->value }}Tab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button type="button" class="btn btn-sm btn-primary location-action-add"
                                        data-in_source="{{ route('locality.select2') }}?type={{  \App\Enums\LocalityTypeEnum::County->value }}"
                                        data-type="{{ \App\Enums\LocalityTypeEnum::City->value }}"
                                        data-name="{{ \App\Enums\LocalityTypeEnum::City->name }}"><i
                                        class="fas fa-plus-circle"></i> add
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Cities</h5>
                        </div>
                        <div class="card-body">
                            <table id="CityTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Located In</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="MeetingRoomsTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button class="btn btn-sm btn-primary click-summary-data" type="button"
                                        data-click_url="{{ route('meeting-room.create') }}"
                                        data-summary_title="Add a meeting room">
                                    <i class="fas fa-plus-circle"></i> Add a Room
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Meeting Rooms</h5>
                        </div>
                        <div class="card-body">
                            <table id="meetingRoomsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Capacity</th>
                                    <th>Branch</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="CurrenciesTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Currencies</h5>
                        </div>
                        <div class="card-body">
                            <table id="currenciesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Symbol</th>
                                    <th>Name</th>
                                    <th>ISOnum</th>
                                    <th>Decimal</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="listActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="addItemModal">
                        <form method="post" id="addItemForm">
                            @csrf
                            <input type="hidden" name="_type" id="listType" class="d-none">
                            <div class="mb-3">
                                <label class="form-label" for="Description">Description </label>
                                <textarea name="Description" id="Description" rows="2" class="form-control"></textarea>
                                <p id="Description_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addItemBtn" type="submit"><i
                                        class="fas fa-save"></i> add
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateItemModal">
                        <form action="{{ route('code-lists.store') }}" method="post"
                              id="updateItemForm">
                            @csrf
                            <div class="mb-3">@method('put')
                                <label class="form-label" for=e_"Description">Description </label>
                                <textarea name="Description" id="e_Description" rows="2"
                                          class="form-control"></textarea>
                                <p id="e_Description_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateItemBtn" type="submit"><i
                                        class="fas fa-save"></i> save update
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content d-none modal-item text-center" id="deleteItemModal">
                        <h4 class="text-danger">
                            Delete Item <b id="deleteItem"></b> ?
                        </h4>
                        <div class="mt-2 mb-2">
                            You are about to delete this item, confirm below ?
                        </div>
                        <hr>
                        <form id="deleteItemForm" method="post"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="deleteItemBtn" type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="onboarding-content with-gradient d-none modal-item" id="addLocationModal">
                        <form method="post" id="addLocationForm">
                            @csrf
                            <div class="mb-3"><input type="hidden" name="_type" id="locationType" class="d-none">
                                <label class="form-label" for="place_name">Place Name </label>
                                <input type="text" name="place_name" id="place_name" class="form-control" required>
                                <p id="place_name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="place_in" class="form-label">Place In</label>
                                <select class="form-control" name="place_in" id="place_in"></select>
                                <p id="place_in_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addLocationBtn" type="submit"><i
                                        class="fas fa-save"></i> add
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateLocationModal">
                        <form method="post" id="updateLocationForm">@method('put')
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="e_place_name">Place Name </label>
                                <input type="text" name="place_name" id="e_place_name" class="form-control" required>
                                <p id="e_place_name_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="e_place_in" class="form-label">Place In</label>
                                <select class="form-control" name="place_in" id="e_place_in"></select>
                                <p id="e_place_in_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateLocationBtn" type="submit"><i
                                        class="fas fa-save"></i> save update
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
    <script src="https://cdn.datatables.net/rowreorder/1.5.0/js/rowReorder.dataTables.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.5.0/js/dataTables.rowReorder.js"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>let IndustriesTable = null, MarketingModes = null, CustomerResponses = null, LeadLossReason = null, CustomerTypes = null, CountyTable = null, TicketCategoriesTable = null, CityTable = null, LocationIn = null, ELocationIn = null, ProductDevelopmentStages = null, isBusy = false, testWindow = null;
        const $Modal = $('#listActionsModal'), Type = $('#listType'), ListUrl = "{{ route('code-lists.index') }}",
            LocationUrl = "{{ route('localities.index') }}";
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchIndustries();

            $(document).on('dblclick', '.reorder', function (e) {
                alert('Double click does not do anything here');
            });
            $(document).on('click', '.list-action-add', function () {
                $(".modal-item").addClass('d-none');
                $('#addItemModal').removeClass('d-none');
                const code = $(this).data('type');
                Type.val(code);
                $('.modal-title').html('Add  ' + code);
                $('#addItemForm').attr('action', ListUrl);
                $Modal.modal('show');
            });
            $(document).on('click', '.location-action-add', function () {
                $(".modal-item").addClass('d-none');
                $('#addLocationModal').removeClass('d-none');
                $('.modal-title').html('Add  ' + $(this).data('name'));
                $('#locationType').val($(this).data('type'));
                const source = $(this).data('in_source');
                if (LocationIn !== null) {
                    LocationIn.select2('destroy');
                    LocationIn = null;
                }
                let place = $("#place_in");
                if (source.trim() !== '') {
                    place.parent().removeClass('d-none');
                    LocationIn = place.val([]).html('').change().select2({
                        placeholder: "Place location ...", minimumInputLength: 2,
                        dropdownParent: $Modal,
                        ajax: {
                            url: source,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {q: $.trim(params.term)};
                            },
                            processResults: function (data) {
                                return {
                                    results: $.map(data, function (item) {
                                        return {text: item.Name, id: item.ID}
                                    })
                                };
                            },
                            cache: true
                        }
                    });
                } else {
                    place.parent().addClass('d-none');
                    place.html("<option value='0' selected>None</option>");
                }

                $('#addLocationForm').attr('action', LocationUrl);
                $Modal.modal('show');
            });

            $(document).on('click', '.list-action-update', function () {
                $(".modal-item").addClass('d-none');
                $('#updateItemModal').removeClass('d-none');
                const stuff = $(this).data('info').split('~');
                $('.modal-title').html('Update  ' + stuff[1]);
                $("#e_Description").html(stuff[1]);
                $('#updateItemForm').attr('action', ListUrl + '/' + stuff[0]);
                $Modal.modal('show');
            });
            $(document).on('click', '.location-action-update', function () {
                $(".modal-item").addClass('d-none');
                $('#updateLocationModal').removeClass('d-none');
                const stuff = $(this).data('info').split('~');
                const source = $(this).data('in_source');
                $('.modal-title').html('Update  ' + stuff[1]);
                $("#e_place_name").val(stuff[1]);
                if (LocationIn !== null) {
                    LocationIn.select2('destroy');
                    LocationIn = null;
                }
                let place = $("#e_place_in");
                if (source.trim() !== '') {
                    place.parent().removeClass('d-none');
                    LocationIn = place.html("<option value='" + stuff[2] + "' selected>" + stuff[3] + "</option>").val(stuff[2]).change().select2({
                        placeholder: "Place location ...", minimumInputLength: 2,
                        dropdownParent: $Modal,
                        ajax: {
                            url: source,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {q: $.trim(params.term)};
                            },
                            processResults: function (data) {
                                return {
                                    results: $.map(data, function (item) {
                                        return {text: item.Name, id: item.ID}
                                    })
                                };
                            },
                            cache: true
                        }
                    });
                } else {
                    place.parent().addClass('d-none');
                    place.html("<option value='0' selected>None</option>");
                }

                $('#updateLocationForm').attr('action', LocationUrl + '/' + stuff[0]);
                $Modal.modal('show');
            });
            $(document).on('click', '.list-action-trash', function () {
                $(".modal-item").addClass('d-none');
                $('#deleteItemModal').removeClass('d-none');
                const stuff = $(this).data('info').split('~');
                $('.modal-title').html('Trash  ' + stuff[1]);
                $("#deleteItem").html(stuff[1]);
                $('#deleteItemForm').attr('action', ListUrl + '/' + stuff[0]);
                $Modal.modal('show');
            });
            $(document).on('click', '.location-action-trash', function () {
                $(".modal-item").addClass('d-none');
                $('#deleteItemModal').removeClass('d-none');
                const stuff = $(this).data('info').split('~');
                $('.modal-title').html('Trash  ' + stuff[1]);
                $("#deleteItem").html(stuff[1]);
                $('#deleteItemForm').attr('action', LocationUrl + '/' + stuff[0]);
                $Modal.modal('show');
            });

            $('form#addItemForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#addItemBtn'), false, true, true);
                if (response) {
                    $Modal.modal('hide');
                    $("a[href='#" + response.list + "Tab']").trigger("click");
                }
            });
            $('form#addLocationForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#addLocationBtn'), false, true, true);
                if (response) {
                    $Modal.modal('hide');
                    $("a[href='#" + response.list + "Tab']").trigger("click");
                }
            });

            $('form#updateItemForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#updateItemBtn'), false, true, true, true);
                if (response) {
                    $Modal.modal('hide');
                    $("a[href='#" + response.list + "Tab']").trigger("click");
                }
            });
            $('form#updateLocationForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#updateLocationBtn'), false, true, true, true);
                if (response) {
                    $Modal.modal('hide');
                    $("a[href='#" + response.list + "Tab']").trigger("click");
                }
            });

            $('form#deleteItemForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#deleteItemBtn'), false, true, true, true);
                if (response) {
                    $Modal.modal('hide');
                    $("a[href='#" + response.list + "Tab']").trigger("click");
                }
            });

        });

        function fetchIndustries() {
            if (IndustriesTable === null) {
                IndustriesTable = fetchLists('Industries');
            } else {
                IndustriesTable.ajax.reload();
            }
        }

        function fetchTicketCategories() {
            if (TicketCategoriesTable === null) {
                TicketCategoriesTable = fetchLists('TicketCategories');
            } else {
                TicketCategoriesTable.ajax.reload();
            }
        }

        function fetchMarketingModes() {
            if (MarketingModes === null) {
                MarketingModes = fetchLists('MarketingModes');
            } else {
                MarketingModes.ajax.reload();
            }
        }

        function fetchCustomerResponses() {
            if (CustomerResponses === null) {
                CustomerResponses = fetchLists('CustomerResponses');
            } else {
                CustomerResponses.ajax.reload();
            }
        }

        function fetchLeadLossReasonTab() {
            if (LeadLossReason === null) {
                LeadLossReason = fetchLists('LeadLossReason');
            } else {
                LeadLossReason.ajax.reload();
            }
        }

        function fetchCustomerTypesTab() {
            if (CustomerTypes === null) {
                CustomerTypes = fetchLists('CustomerTypes');
            } else {
                CustomerTypes.ajax.reload();
            }
        }

        function fetchProductDevelopmentStagesTab() {
            if (ProductDevelopmentStages === null) {
                ProductDevelopmentStages = fetchLists('ProductDevelopmentStages');
            } else {
                ProductDevelopmentStages.ajax.reload();
            }
        }

        function fetchCountiesTab() {
            if (CountyTable === null) {
                CountyTable = $('#CountyTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('localities.index') }}?_code={{ \App\Enums\LocalityTypeEnum::County->value }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>There no counties found here</p>"
                    }
                });

                CountyTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the notes.");
                });
            } else {
                CountyTable.ajax.reload();
            }
        }

        function fetchCitiesTab() {
            if (CityTable === null) {
                CityTable = $('#CityTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('localities.index') }}?_code={{ \App\Enums\LocalityTypeEnum::City->value }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'in.Name', name: 'in.Name'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>There no counties found here</p>"
                    }
                });

                CityTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the notes.");
                });
            } else {
                CityTable.ajax.reload();
            }
        }

        function fetchLists(code) {
            let table = $('#' + code + 'Table').on('dt-error.dt', function (e, settings, techNote, message) {
                nWarning("an issue occurred while loading the list.");
            }).DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                columnDefs: [
                    {"className": "text-center", "targets": [2]},
                    {"visible": false, "targets": [1]},
                    {
                        className: 'reorder',
                        render: () => '≡',
                        targets: 0
                    },
                ],
                order: [[2, 'asc']],
                rowReorder: {
                    update: false
                },
                /* rowReorder: true{
                     dataSrc: 'ID',selector: 'tr'
                 }*/
                ajax: {
                    url: ListUrl + '?_code=' + code,
                    error: function (jqXHR) {
                        codeNotify(jqXHR.status);
                    }
                },
                columns: [
                    {data: "DT_RowIndex", name: '', searchable: false, orderable: false},
                    {data: 'ID', name: 'ID'},
                    {data: 'DisplayOrder', name: 'DisplayOrder'},
                    {data: 'Description', name: 'Description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ], "oLanguage": {
                    "sEmptyTable": "<p class='text-center'>No records found</p>"
                },
            });
            /* table.on('error', function (er) {
                 nWarning("an issue occurred while loading the list.");
             });*/
            $('#' + code + 'Table tbody').on('dblclick', 'tr', function (e) {
                alert('Double click does not do anything here');
            });

            table.on('row-reorder', function (e, diff, edit) {
                e.preventDefault();
                if (isBusy) {
                } else if (!Array.isArray(diff) && diff.length === 0) {
                    isBusy = true;
                    setTimeout(function () {
                        isBusy = false;
                    }, 1000);
                } else {
                    if ($(diff[0].node).data('info') == edit.originalEvent.target.parentNode.dataset.info) {
                        let position = parseInt(diff[0].newPosition);
                        isBusy = true;
                        if (Number.isInteger(position)) {
                            $.ajax({
                                url: "{{ route('code-lists.order') }}",
                                type: 'post',
                                dataType: 'json',
                                data: [{name: '_token', value: window.csrf_token}, {
                                    name: 'position',
                                    value: position + 1
                                },
                                    {name: 'CodeId', value: $(diff[0].node).data('info')}],
                                success: function (data) {
                                    nSuccess(data.message);
                                    table.ajax.reload();
                                }, error: function (request) {
                                    formRequest(request, true)
                                }
                            }).always(function () {
                                setTimeout(function () {
                                    isBusy = false;
                                }, 3000);
                            });
                        }
                    } else if ($(diff[diff.length - 1].node).data('info') == edit.originalEvent.target.parentNode.dataset.info) {
                        let position = parseInt(diff[diff.length - 1].newPosition);
                        isBusy = true;
                        if (Number.isInteger(position)) {
                            $.ajax({
                                url: "{{ route('code-lists.order') }}",
                                type: 'post',
                                dataType: 'json',
                                data: [{name: '_token', value: window.csrf_token}, {
                                    name: 'position',
                                    value: position + 1
                                },
                                    {name: 'CodeId', value: $(diff[diff.length - 1].node).data('info')}],
                                success: function (data) {
                                    nSuccess(data.message);
                                    table.ajax.reload();
                                }, error: function (request) {
                                    formRequest(request, true)
                                }
                            }).always(function () {
                                setTimeout(function () {
                                    isBusy = false;
                                }, 3000);
                            });
                        }
                    }
                }
            });

            return table;
        }

        function isObject(obj) {
            return obj != null && obj.constructor.name === "Object"
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        function fetchMeetingRoomsTable() {
            if (!$.fn.DataTable.isDataTable('#meetingRoomsTable')) {
                $('#meetingRoomsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // "order": [[3, 'asc']],
                    ajax: {
                        url: "{{ route('meeting-room.index') }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "RoomID", name: 'RoomID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'Capacity', name: 'Capacity'},
                        {data: 'BranchId', name: 'BranchId'},
                    ], "oLanguage": {
                        "sEmptyTable": "no meeting rooms under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading meeting rooms.");
                });
            } else {
                $('#meetingRoomsTable').DataTable().ajax.reload();
            }
        }
        function fetchCurrenciesTable() {
            if (!$.fn.DataTable.isDataTable('#currenciesTable')) {
                $('#currenciesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // "order": [[3, 'asc']],
                    ajax: {
                        url: "{{ route('currencies.index') }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "Code", name: 'Code'},
                        {data: 'Symbol', name: 'Symbol'},
                        {data: 'Name', name: 'Name'},
                        {data: 'ISOnum', name: 'ISOnum'},
                        {data: 'DecimalDigits', name: 'DecimalDigits'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no currencies under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading currenciess.");
                });
            } else {
                $('#meetingRoomsTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
