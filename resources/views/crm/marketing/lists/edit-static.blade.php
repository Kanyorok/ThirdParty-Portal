@php use App\Enums\Core\ExtensionsEnum;use App\Enums\Core\VisibilityEnum;use App\Models\BR\Client;use App\Models\CRM\Lead;use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    {{ Str::limit($list->Label,50) }} List
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
    <li class="breadcrumb-item"><a href="{{ route('marketing-list.index') }}">Marketing Lists</a></li>
@endsection
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.5/css/select.dataTables.css">
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body border-bottom border-1">
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <h2 class="float-start">{!! $list->Visibility->icon() !!} <a
                                    href="{{ route('marketing-list.show',$list->slug) }}"
                                    class="text-black text-decoration-underline">{{ $list->Label }}</a></h2>
                        </div>
                        <div class="col-md-6 col-12">
                            <button type="button" class="btn btn-danger modal-trash-list float-end mx-2">
                                <i class="fas fa-trash-alt"></i> trash
                            </button>
                            <button type="button" class="btn btn-primary modal-update-list float-end mx-2">
                                <i class="fas fa-edit"></i> update
                            </button>
                            <button type="button" class="btn btn-info modal-upload-file float-end mx-2 inactive-upload">
                                <i class="fas fa-upload"></i> upload list
                            </button>
                            <button type="button"
                                    class="btn btn-secondary cancel-upload float-end mx-2  d-none active-upload">
                                <i class="fas fa-times"></i> cancel upload
                            </button>
                        </div>
                        <div class="col-12">
                            <p class="mb-0"><b class="me-2">{{ $list->Type->name }} List </b> | {{ $list->Notes }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6 col-12 text-muted">
                            Creation <span class="ms-2">{{ $list->CreatedOn?->format('d M, Y H:i') }} : {{ $list->creator?->UserID }} - {{ $list->creator?->Name }}</span>
                        </div>
                        <div class="col-md-6 col-12 text-muted">
                            Modified <span class="ms-2">{{ $list->ModifiedOn?->format('d M, Y H:i') }} : {{ $list->modified?->UserID }} - {{ $list->modified?->Name }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <form action="{{ route('marketing-list-upload.store', [$list->slug]) }}" method="post" id="uploadListForm"
                  enctype="multipart/form-data" class="card d-none active-upload"> @csrf
                <div class="card-header">
                    Upload File
                    <div class="progress mb-3 file-change d-none">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                             aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                Complete</small></div>
                    </div>
                </div>
                <div class="card-body ">
                    <input type="hidden" name="Type" value="{{ Client::getPrimaryKey() }}">
                    <div class="alert alert-info " role="alert">
                        <div class="alert-icon">
                            <i class="far fa-fw fa-bell"></i>
                        </div>
                        <div class="alert-message">
                            <strong>Note!</strong> this file must have a row with header of <code>MemberID</code> which
                            has all the memberId. <a target="_blank" download=""
                                                     href="{{ asset('assets/samples/MarketingListuploadSample.csv') }}">download
                                sample</a>
                        </div>
                    </div>

                    <div class="mb-3 ">
                        <label for="file">CSV File</label>
                        <input type="file" name="file" class="form-control"
                               accept="{{ ExtensionsEnum::Csv->getMimeType() }}" id="file">
                        <p id="file_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <button class="btn btn-secondary float-start cancel-upload" type="button"><i
                                    class="fas fa-times"></i> cancel
                            </button>
                        </div>
                        <div class="col-md-6 col-12">
                            <button class="btn btn-primary float-end file-change d-none" type="submit"
                                    id="uploadListBtn"><i class="fas fa-upload"> </i> upload and process
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    @if(is_null($list->Source) )
                        <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchLeadsTable()">Leads</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-0" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchMembersTable()">Members </a></li>
                    @elseif($list->Source === Lead::getPrimaryKey())
                        <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchLeadsTable()">Leads</a></li>
                    @elseif($list->Source === Client::getPrimaryKey())
                        <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchMembersTable()">Members </a></li>
                    @endif
                </ul>
                <div class="tab-content">
                    <div
                        class="tab-pane  {{ ($list->Source === Client::getPrimaryKey())?'active':'' }} m-2"
                        id="tab-0" role="tabpanel">
                        <div class="row mb-0">
                            <div class="col-8 mb-0">
                                <h3 class="mb-1 mt-2">
                                    Select Member to Add
                                </h3>
                            </div>
                            <div class="col-4 mb-0">
                                <form action="{{ route('marketing-list.clients', $list->slug) }}" method="post"
                                      id="saveMembersToListForm">@csrf
                                    <button class="float-end btn btn-primary disabled" type="submit"
                                            id="saveMembersToList">
                                        <i class="fas fa-save"></i> add clients
                                    </button>
                                    <input type="hidden" name="clients" id="MembersToList" class="d-none">
                                </form>
                            </div>
                        </div>
                        <hr class="mt-0 mb-2">
                        <div class="table-responsive m-1">
                            <table id="clientsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 ">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>MemberNo</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                    </div>
                    <div
                        class="tab-pane  {{ ($list->Source === Client::getPrimaryKey())?'':'active' }}   m-2"
                        id="tab-1" role="tabpanel">
                        <div class="row mb-0">
                            <div class="col-8 mb-0">
                                <h3 class="mb-1 mt-2">
                                    Select Leads to Add
                                </h3>
                            </div>
                            <div class="col-4 mb-0">
                                <form action="{{ route('marketing-list.leads', $list->slug) }}" method="post"
                                      id="saveLeadsToListForm">@csrf
                                    <button class="float-end btn btn-primary disabled" type="submit"
                                            id="saveLeadsToList">
                                        <i class="fas fa-save"></i> add leads
                                    </button>
                                    <input type="hidden" name="leads" id="LeadsToList" class="d-none">
                                </form>
                            </div>
                        </div>
                        <hr class="mt-0 mb-2">
                        <div class="table-responsive m-1">
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
                        </div>
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
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    {{--<script src="{{ asset('assets/libs/dataTables.checkboxes/dataTables.checkboxes.min.js') }}"></script>--}}
    {{----}}
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.5/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.5/js/select.dataTables.js"></script>
    <script>let clientsTable = null, leadsTable = null;
        const leadsBtn = $("#saveLeadsToList"), clientsBtn = $("#saveMembersToList"), $Modal = $('#ListActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            @if($list->Source === Client::getPrimaryKey())
            fetchMembersTable();
            @else
            fetchLeadsTable();
            @endif


            $(document).on('click', '.modal-upload-file', function () {
                $('.active-upload').removeClass('d-none');
                $('.inactive-upload').addClass('d-none');
            });
            $(document).on('click', '.cancel-upload', function () {
                cancelUpload();
            });

            $("#file").change(function () {
                $('.file-change').removeClass('d-none');
                $('.file-changed').addClass('d-none');
            });

            $('form#uploadListForm').submit(function (e) {
                e.preventDefault();
                const saveBtn = $('#uploadListBtn');
                const btnContent = saveBtn.html();
                $(".form-control").removeClass('is-invalid');
                $('.error').addClass('d-none');
                saveBtn.prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
                $(this).ajaxSubmit({
                    dataType: 'json', beforeSubmit: function () {
                        $("#progress-bar").width('0%');
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                    },
                    success: function (data) {
                        nSuccess(data.message);
                        cancelUpload();
                        $("#progress-bar").width('0%').html('0');
                        saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                        setTimeout(() => {
                            window.location.replace(data.route);
                        }, 2000);
                    },
                    error: function (request) {
                        formRequest(request, true);
                        saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                    }, resetForm: true
                });
                return false;
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

            $('form#saveLeadsToListForm').submit(async function (e) {
                e.preventDefault();
                let list = $("#LeadsToList");
                if (leadsTable !== null) {
                    list.val($.map(leadsTable.rows({selected: true}).data(), function (item) {
                        return item.LeadID;
                    }).join(","));
                    if (await saveForm($(this), leadsBtn, false, true, true)) {
                        fetchLeadsTable();
                    }
                }
            });
            $('form#saveMembersToListForm').submit(async function (e) {
                e.preventDefault();
                let list = $("#MembersToList");
                if (clientsTable !== null) {
                    list.val($.map(clientsTable.rows({selected: true}).data(), function (item) {
                        return item.ClientId;
                    }).join(","));
                    if (await saveForm($(this), clientsBtn, false, true, true)) {
                        fetchMembersTable();
                    }
                }
            });
        });

        function cancelUpload() {
            $('.active-upload').addClass('d-none');
            $('.inactive-upload').removeClass('d-none');
            $('.file-change').addClass('d-none');
            $('.file-changed').removeClass('d-none');
            $('form#uploadListForm').resetForm();
        }

        function fetchMembersTable() {
            if (clientsTable === null) {
                clientsTable = $('#clientsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    select: {
                        style: 'multi',
                        selector: 'td:first-child',
                        headerCheckbox: 'select-page'
                    },
                    ajax: {
                        url: '{{ route('marketing-list.clients', $list->slug) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: null, orderable: false, searchable: false, render: DataTable.render.select()},
                        {data: 'ClientID', name: 'ClientID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'type.Description', name: 'type.Description'},
                        {data: 'Mobile', name: 'Mobile'},
                        {data: 'Email', name: 'Email'},
                    ]
                }).on('select', function () {
                    if (clientsTable.rows({selected: true}).count() === 0) {
                        clientsBtn.addClass('disabled');
                    } else {
                        clientsBtn.removeClass('disabled');
                    }
                }).on('deselect', function () {
                    if (clientsTable.rows({selected: true}).count() === 0) {
                        clientsBtn.addClass('disabled');
                    } else {
                        clientsBtn.removeClass('disabled');
                    }
                });

                clientsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the list.");
                });
            } else {
                clientsTable.ajax.reload();
            }
        }

        function fetchLeadsTable() {
            if (leadsTable === null) {
                leadsTable = $('#leadsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    /* "order": [[5, 'desc']],
                     "columnDefs": [
                         {"className": "text-center", "targets": [2]}
                     ],*/
                    /* 'columnDefs': [
                         {
                             'targets': 0,
                             'checkboxes': {
                                 'selectRow': true
                             }
                         }
                     ], */
                    select: {
                        style: 'multi',
                        selector: 'td:first-child',
                        headerCheckbox: 'select-page'
                    },
                    ajax: {
                        url: '{{ route('marketing-list.leads', $list->slug) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },

                    columns: [
                        {data: null, orderable: false, searchable: false, render: DataTable.render.select()},
                        {data: 'Name', name: 'Name'},
                        {data: 'Type', name: 'Type'},
                        {data: 'Email', name: 'Email'},
                        {data: 'Phone', name: 'Phone'},
                        {data: 'LastContacted', name: 'LastContacted'},
                    ], "oLanguage": {
                        "sEmptyTable": "no leads found here"
                    }
                }).on('select', function () {
                    if (leadsTable.rows({selected: true}).count() === 0) {
                        leadsBtn.addClass('disabled');
                    } else {
                        leadsBtn.removeClass('disabled');
                    }
                }).on('deselect', function () {
                    if (leadsTable.rows({selected: true}).count() === 0) {
                        leadsBtn.addClass('disabled');
                    } else {
                        leadsBtn.removeClass('disabled');
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
@endsection
