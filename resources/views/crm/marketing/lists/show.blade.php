@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    {{ Str::limit($list->Label,50) }} List
@endsection
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.5/css/select.dataTables.css">
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 ">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center">{!! $list->Visibility->icon() !!} {{ $list->Label }} </h2>
                    <p class="text-center">{{ $list->Type->name }}</p>
                    <p class="text-center">Contacts: <b>{{ number_format($contacts_count) }}</b></p>
                    <p class="text-center">{{ $list->Notes }}</p>
                    <hr>
                    @include('snippets.behind_scenes',['model'=>$list])

                    @if(!$isProcessing)
                        @can('update',$list)
                            <hr>
                            <a href="{{ route('marketing-list.edit',[$list->slug]) }}" class="btn btn-info w-100"><i
                                    class="fas fa-edit"></i> update list</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            @if($isProcessing)
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title" id="processingTotal">Processing Data </h3>
                    </div>
                    <div class="card-body pt-0">
                        <div class="progress mb-3" style="height: 20px;">
                            <div id="processingProgress"
                                 class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                 style="width: 0" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    @if(is_null($list->Source) && $list->Type->value === \App\Enums\MarketingListEnum::Static->value)
                        <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchLeadsTable()">Leads</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-0" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchMembersTable()">Members </a></li>
                    @elseif($list->Source === \App\Models\CRM\Lead::getPrimaryKey())
                        <li class="nav-item"><a class="nav-link active" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchLeadsTable()">Leads</a></li>
                    @elseif($list->Source === \App\Models\BR\Client::getPrimaryKey())
                        <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchMembersTable()">Members </a></li>
                    @endif
                </ul>
                <div class="tab-content">
                    <div
                        class="tab-pane m-2 {{ ($list->Source === \App\Models\BR\Client::getPrimaryKey())?'active':'' }} "
                        id="tab-0" role="tabpanel">
                        @if( $list->Type->value === \App\Enums\MarketingListEnum::Static->value && !$isProcessing)
                            @can('update',$list)
                                <div class="row mb-0">
                                    <div class="col-8 mb-0">
                                        <h3 class="mb-1 mt-2">
                                            Select Member(s) to Remove
                                        </h3>
                                    </div>
                                    <div class="col-4 mb-0">
                                        <form
                                            action="{{ route('marketing-list.clients', [$list->slug,'q'=> 'current']) }}"
                                            method="post"
                                            id="removeMembersListForm">@csrf
                                            <button class="float-end btn btn-danger disabled" type="submit"
                                                    id="removeMembersList">
                                                <i class="fas fa-minus-circle"></i> remove members
                                            </button>
                                            <input type="hidden" name="members" id="MembersList" class="d-none">
                                        </form>
                                    </div>
                                </div>
                                <hr class="mt-0 mb-2">
                            @endcan
                        @endif
                        <table id="membersTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Member No</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Phone</th>
                                <th>Email</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div
                        class="tab-pane {{ ($list->Source === \App\Models\BR\Client::getPrimaryKey())?'':'active' }} m-2"
                        id="tab-1" role="tabpanel">
                        @if($list->Type->value === \App\Enums\MarketingListEnum::Static->value  && !$isProcessing)
                            @can('update',$list)
                                <div class="row mb-0">
                                    <div class="col-8 mb-0">
                                        <h3 class="mb-1 mt-2">
                                            Select Lead(s) to Remove
                                        </h3>
                                    </div>
                                    <div class="col-4 mb-0">
                                        <form
                                            action="{{ route('marketing-list.leads',[$list->slug, 'q'=> 'current']) }}"
                                            method="post"
                                            id="saveLeadsToListForm">@csrf
                                            <button class="float-end btn btn-primary disabled" type="submit"
                                                    id="saveLeadsToList">
                                                <i class="fas fa-minus-circle"></i> remove leads
                                            </button>
                                            <input type="hidden" name="leads" id="LeadsToList" class="d-none">
                                        </form>
                                    </div>
                                </div>
                                <hr class="mt-0 mb-2">
                            @endcan
                        @endif
                        <table id="leadsTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
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

@endsection
@section('scripts')
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.5/js/dataTables.select.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.5/js/select.dataTables.js"></script>
    <script>let membersTable = null, leadsTable = null, progressInterval = null;
        const leadsBtn = $("#saveLeadsToList"), membersBtn = $("#removeMembersList"), $Modal = $('#ListActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            @if($list->Source === \App\Models\BR\Client::getPrimaryKey())
            fetchMembersTable();
            @else
            fetchLeadsTable();
            @endif

            @if($isProcessing)
            fetchProgress();
            @endif

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
            $('form#removeMembersListForm').submit(async function (e) {
                e.preventDefault();
                let list = $("#MembersList");
                if (membersTable !== null) {
                    list.val($.map(membersTable.rows({selected: true}).data(), function (item) {
                        return item.ClientId;
                    }).join(","));
                    if (await saveForm($(this), membersBtn, false, true, true)) {
                        fetchMembersTable();
                    }
                }
            });
        });

        @if($isProcessing)
        function fetchProgress() {
            if (progressInterval !== null) {
                clearInterval(progressInterval);
            }
            $.get("{{ route('marketing-list-upload.index', [$list->slug]) }}", function (data) {
                $("#processingProgress").width(data.progress + '%').html('<small id="progress-status">' + data.description + '</small>');
                $("#processingTotal").html(data.description);
                if (data.progress > 99) {
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 3000)
                } else {
                    progressInterval = setInterval(function () {
                        fetchProgress();
                    }, 5000);
                }
            });
        }
        @endif

        function fetchMembersTable() {
            if (membersTable === null) {
                membersTable = $('#membersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    select: {
                        style: 'multi',
                        selector: 'td:first-child',
                        headerCheckbox: 'select-page'
                    },
                    ajax: {
                        url: '{{ route('marketing-list.clients', [$list->slug, 'q'=> 'current']) }}',
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
                    if (membersTable.rows({selected: true}).count() === 0) {
                        membersBtn.addClass('disabled');
                    } else {
                        membersBtn.removeClass('disabled');
                    }
                }).on('deselect', function () {
                    if (membersTable.rows({selected: true}).count() === 0) {
                        membersBtn.addClass('disabled');
                    } else {
                        membersBtn.removeClass('disabled');
                    }
                });

                membersTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the list.");
                    console.log(er);
                });
            } else {
                membersTable.ajax.reload();
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
                        url: '{{ route('marketing-list.leads', [$list->slug, 'q'=> 'current']) }}',
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
                    console.log(er);
                });
            } else {
                leadsTable.ajax.reload();
            }
        }
    </script>
@endsection
