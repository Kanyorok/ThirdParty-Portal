@php use App\Enums\Core\VisibilityEnum;use App\Models\BR\Client;use App\Models\CRM\Lead;use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    {{ Str::limit($list->Label,50) }} List
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
    <li class="breadcrumb-item"><a href="{{ route('marketing-list.index') }}">Marketing Lists</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="float-start">{!! $list->Visibility->icon() !!} <a
                            href="{{ route('marketing-list.show',$list->slug) }}"
                            class="text-black text-decoration-underline">{{ $list->Label }}</a></h2>

                    <button type="button" class="btn btn-danger modal-trash-list float-end mx-2">
                        <i class="fas fa-trash-alt"></i> trash
                    </button>
                    <button type="button" class="btn btn-primary modal-update-list float-end mx-2">
                        <i class="fas fa-edit"></i> update
                    </button>
                    <div class="clearfix"></div>
                    <p><b class="me-2">{{ $list->Type->name }} List </b> | {{ $list->Notes }}</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="row">
                        <div class="col-6"><h3 class="card-title">Filters</h3></div>
                        <div class="col-6">
                            <select name="filtersSelect" id="filtersSelect" class="form-control-lg w-100">
                                <option value="0" selected>Select a filter to add</option>
                                @foreach($filters as $filter)
                                    <option value="{{ $filter->Id }}">{{ $filter->Name }}
                                        - {{ $filter->Operator->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive m-1">
                    <table id="filtersTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Value(s)</th>
                            <th>Value(s)</th>
                            <th>After</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="card-title float-start">Contacts -
                        @if($list->Source === Client::getPrimaryKey())
                            Clients
                        @elseif($list->Source === Lead::getPrimaryKey())
                            Leads
                        @else
                            Unkown Contacts
                        @endif
                    </h3>
                    <button class="btn btn-link float-end py-0" type="button" onclick="fetchContacts()"><i
                            class="fas fa-refresh"></i></button>
                </div>
                <div class="card-body pt-1">
                    <div class="table-responsive m-1">
                    @switch($list->Source)
                        @case(Client::getPrimaryKey())
                            <table id="clientsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100">
                                <thead>
                                <tr>
                                    <th>MemberNo</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            @break
                        @case(Lead::getPrimaryKey())
                            <table id="leadsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Last Contact</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            @break
                        @default
                            <h3 class="my-5 fw-bold">Unknown Contacts Source</h3>
                            @break
                    @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ListActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateListModal">
                        <form action="{{ route('marketing-list.update',[$list->slug]) }}" method="post"
                              id="updateListForm"> @csrf
                            <div class="mb-3">@method('put')
                                <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Label" name="Label" required
                                       placeholder="Label" value="{{ $list->Label }}">
                                <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Visibility">Visibility <span class="text-danger">*</span></label>
                                <select class="form-control" name="Visibility" id="Visibility" required>
                                    @foreach(VisibilityEnum::cases() as $Visibility)
                                        <option
                                            value="{{ $Visibility->value }}" {{ ($Visibility->value===$list->Visibility->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                                    @endforeach
                                </select>
                                <p id="Visibility_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control"
                                          maxlength="1000">{{ $list->Notes }}</textarea>
                                <p id="Notes_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateListBtn" type="submit"><i
                                        class="fas fa-save"></i>
                                    update {{ Str::limit($list->Label ,20) }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center" id="trashListModal">
                        <h4 class="text-danger">
                            Trash Marketing List <b>{{ $list->Label }}</b> ?
                        </h4>
                        <form id="trashListForm" method="post"
                              action="{{ route('marketing-list.destroy',[$list->slug]) }}"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashListBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="trashFilterListModal">
                        <h4 class="text-danger">
                            Trash List Filter <b id="trashFilterList"></b> ?
                        </h4>
                        <form id="trashFilterListForm" method="post"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashFilterListBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
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
    <script>let filtersTable = null;
        const $Modal = $('#ListActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchContacts();
            fetchFiltersTable();

            $('#filtersSelect').on('change', async function () {
                if (this.value === '0') {
                    return;
                }
                showOffCanvasMain("Add a new Filter.", '{{ route('marketing-list-filters.create',[$list->slug]) }}?filter=' + this.value);
                $('#filtersSelect').val('0').change();
            }).select2()

            $(document).on('click', '.trash-filter-modal', function () {
                const stuff = $(this).data('info').split('~');
                $('.modal-title').html('<b class="text-danger">Trash</b> Filter ' + stuff[1]);
                $("#trashFilterList").html(stuff[1]);
                $('#trashFilterListForm').attr('action', stuff[0]);
                $(".modal-item").addClass('d-none');
                $('#trashFilterListModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashFilterListForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashFilterListBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchFiltersTable();
                    fetchContacts();
                }
            });


            $(document).on('click', '.modal-trash-list', function () {
                $(".modal-title").html('Trash List : {{ $list->Label }}');
                $(".modal-item").addClass('d-none');
                $('#trashListModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashListForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashListBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-update-list', function () {
                $(".modal-title").html('Update List : {{ $list->Label }}');
                $(".modal-item").addClass('d-none');
                $('#updateListModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#updateListForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateListBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
        });

        function fetchFiltersTable() {
            if (filtersTable === null) {
                filtersTable = $('#filtersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: 'tir',
                    //"order": [[6, 'desc']],
                    columnDefs: [
                        /*  {"className": "text-center", "targets": [2]},*/
                        {
                            "render": function (data, type, row) {
                                return (data === null) ? row.FilterValues : data;
                            },
                            "targets": 2 // the place of col2
                        },
                        {"visible": false, "targets": [3]}
                    ],
                    ajax: {
                        url: '{{ route('marketing-list-filters.index', $list->slug) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'DisplayOrder', name: 'DisplayOrder'},
                        {data: 'filter.Name', name: 'filter.Name'},
                        {data: 'FilterValue', name: 'FilterValue'},
                        {data: 'FilterValues', name: 'FilterValues'},
                        {data: 'After', name: 'After'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no clients found under this filters"
                    }
                });

                filtersTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the list.");
                });
            } else {
                filtersTable.ajax.reload();
            }
        }

    </script>
    @switch($list->Source)
        @case(Client::getPrimaryKey())
            <script>let clientsTable = null;

                function fetchContacts() {
                    if (clientsTable === null) {
                        clientsTable = $('#clientsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            responsive: true,
                            ajax: {
                                url: '{{ route('marketing-list.clients', $list->slug) }}',
                                error: function (jqXHR) {
                                    codeNotify(jqXHR.status);
                                }
                            },
                            columns: [
                                {data: 'ClientID', name: 'ClientID'},
                                {data: 'Name', name: 'Name'},
                                {data: 'type.Description', name: 'type.Description'},
                                {data: 'Mobile', name: 'Mobile'},
                                {data: 'Email', name: 'Email'},
                            ], "oLanguage": {
                                "sEmptyTable": "no clients found under this filters"
                            }
                        });

                        clientsTable.on('error', function (er) {
                            nWarning("an issue occurred while loading the list.");
                        });
                    } else {
                        clientsTable.ajax.reload();
                    }
                }
            </script>
            @break
        @case(Lead::getPrimaryKey())
            <script>let leadsTable = null;

                function fetchContacts() {
                    if (leadsTable === null) {
                        leadsTable = $('#leadsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            responsive: true,
                            ajax: {
                                url: '{{ route('marketing-list.leads', $list->slug) }}',
                                error: function (jqXHR) {
                                    codeNotify(jqXHR.status);
                                }
                            },
                            columns: [
                                {data: 'LeadID', name: 'LeadID'},
                                {data: 'Name', name: 'Name'},
                                {data: 'Type', name: 'Type'},
                                {data: 'Email', name: 'Email'},
                                {data: 'Phone', name: 'Phone'},
                                {data: 'LastContacted', name: 'LastContacted'},
                            ], "oLanguage": {
                                "sEmptyTable": "no leads found under this filters"
                            }
                        });

                        leadsTable.on('error', function (er) {
                            nWarning("an issue occurred while loading leads.");
                        });
                    } else {
                        leadsTable.ajax.reload();
                    }
                }
            </script>
            @break
    @endswitch
@endsection
