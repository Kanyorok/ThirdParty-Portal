@extends('layouts.app')

@section('title','Users & Roles')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
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
                    <h5 class="card-title mb-0">@yield('title')</h5>
                </div>
                <div class="list-group list-group-flush" role="tablist">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#users"
                       onclick="fetchUsersTable();" role="tab">
                        Users
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#teams"
                       onclick="fetchTeamsTable();" role="tab">
                        Teams
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#branches"
                       onclick="fetchBranchesTable();" role="tab">
                        Branches
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#roles"
                       onclick="fetchRolesTable();" role="tab">
                        Roles
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-9 col-xl-10">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="users" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button class="btn btn-info ms-2 " type="button" id="triggerSendBulkNotification">
                                    <i class="fas fa-plane-departure"></i> Send a message
                                </button>
                                <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                        data-click_url="{{ route('users.create') }}"
                                        data-summary_title="Add a User">
                                    <i class="fas fa-plus-circle"></i> Add a User
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Users</h5>
                        </div>
                        <div class="card-body">
                            <table id="usersTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="roles" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                        data-click_url="{{ route('roles.create') }}"
                                        data-summary_title="Create a New Role">
                                    <i class="fas fa-plus"> </i> Add a Role
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Roles</h5>
                        </div>
                        <div class="card-body">
                            <table id="rolesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Users</th>
                                    <th>Dated</th>
                                    <th>Added By</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="teams" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <button class="btn btn-primary ms-2  modal-create-team" type="button">
                                    <i class="fas fa-plus"></i> Add a Team
                                </button>
                            </div>
                            <h5 class="card-title mb-0">Teams</h5>
                        </div>
                        <div class="card-body">
                            <table id="teamsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>users</th>
                                    <th>Notes</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="branches" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Branches</h5>
                        </div>
                        <div class="card-body pt-0">
                            <table id="branchesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Address</th>
                                    <th>Manager</th>
                                    <th>Operation</th>
                                    <th>actions</th>
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
    <div class="modal fade" id="userRolesActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashRoleModal">
                        <h3 class="h3 text-center" id="trashRoleContent"></h3>
                        <div class="mt-2 mb-2">
                            Are you sure you want to trash this role ?
                        </div>
                        <hr>
                        <form id="trashRoleForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="trashRoleBtn" type="submit"><i
                                        class="fas fa-trash"></i> yes, delete
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="createTeamModal">
                        <form action="{{ route('teams.store') }}" method="post" id="createTeamForm"> @csrf
                            <div class="mb-3">
                                <label class="form-label" for="TeamName">Team Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="TeamName" name="TeamName" required
                                       placeholder="Team Name">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="TeamEmail">Team Email <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="TeamEmail" name="TeamEmail" required
                                       placeholder="TeamEmail">
                                <p id="TeamEmail_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="TeamLead" class="form-label">Team Lead</label>
                                <select class="form-control team-members" name="TeamLead" id="TeamLead"></select>
                                <p id="TeamLead_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="TeamMembers" class="form-label">Team Members(s)</label>
                                <select class="form-control team-members" name="TeamMembers[]" id="TeamMembers" multiple
                                        required>
                                </select>
                                <p id="TeamMembers_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="TeamNotes">Team Notes </label>
                                <textarea name="TeamNotes" id="TeamNotes" rows="3" class="form-control"></textarea>
                                <p id="TeamNotes_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createTeamBtn" type="submit"><i
                                        class="fas fa-save"></i> add Team
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="createUserMessagingModal">
                        <div class="accordion accordion-flush mb-3" id="accordionHelp">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne">
                                    <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"
                                            aria-expanded="false" aria-controls="flush-collapseOne">
                                        Help Notes
                                    </button>
                                </h2>
                                <div id="flush-collapseOne" class="accordion-collapse collapse"
                                     aria-labelledby="flush-headingOne" data-bs-parent="#accordionHelp">
                                    <div class="accordion-body">
                                        <ul class="users-group users-group-flush">
                                            <li class="users-group-item">You can use <code> #name</code> to be
                                                replaced by their name while sending.
                                            </li>
                                            <li class="users-group-item">You can use <code> #date</code> to be
                                                replaced by {{ now()->format('M d, Y') }}while sending.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form method="post" id="usersBulkNotificationForm" action="{{ route('bulk-notification.users') }}">
                            <div class="col-12 mb-3">@csrf
                                <label class="form-label" for="NotificationLabel">Label <span class="text-danger">*</span></label>
                                <input type="text" class="search-form-item form-control" required
                                       id="NotificationLabel" name="NotificationLabel"
                                       placeholder="Label">
                                <p id="NotificationLabel_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-12">
                                <label class="form-label" for="NotificationContent">Content <span class="text-danger">*</span></label>
                                <b class="float-end text-info" id="msgCounter"></b>
                                <textarea name="NotificationContent" id="NotificationContent" class="form-control" rows="4"
                                          maxlength="50000" minlength="2"></textarea>
                                <p id="NotificationContent_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <button type="submit" class="float-end btn btn-success w-50 "
                                    id="usersBulkNotificationBtn"><i class="fa fa-plane-departure"></i> send messages
                            </button>
                            <div class="clearfix"></div>
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
    <script>let usersTable = null, rolesTable = null, teamsTable = null, branchesTable = null;
        const $Modal = $('#userRolesActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchUsersTable();


            $('.team-members').select2({
                placeholder: "Choose users ...", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: "{{ route('users.select2') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name, id: item.UserID}
                            })
                        };
                    },
                    cache: true
                }
            });

            $(document).on('click', '.trash-role', function () {
                $(".modal-item").addClass('d-none');
                const stuff = $(this).data('info').split('~');
                $('.modal-title').html('<b class="text-danger">Delete</b>  ' + stuff[1]);
                $("#trashRoleContent").html(stuff[1]);
                $('#trashRoleForm').attr('action', stuff[0]);
                $('.modal-dialog').removeClass('modal-lg');
                $('#trashRoleModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashRoleForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashRoleBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchRolesTable();
                }
            });

            $(document).on('click', '.modal-create-team', function () {
                $(".modal-title").html('Create a new Team');
                $(".modal-item").addClass('d-none');
                $('#createTeamModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createTeamForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createTeamBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchTeamsTable();
                }
            });


            $(document).on('click', '#triggerSendBulkNotification', function () {
                $(".modal-title").html('Send a bulk notification to users');
                $(".modal-item").addClass('d-none');
                $('#createUserMessagingModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#usersBulkNotificationForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#usersBulkNotificationBtn'), false, true, true)) {
                    $Modal.modal('hide');
                }
            });
        });

        function fetchUsersTable() {
            if (usersTable === null) {
                usersTable = $('#usersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    //"order": [[6, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: "{{ route('users.index') }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "photo",
                                sort: "UserID",
                            }, name: 'UserID', searchable: false
                        },
                        {data: 'Name', name: 'Name'},
                        {data: 'branch.BranchName', name: 'branch.BranchName', orderable: false, searchable: false},
                        {data: 'Email', name: 'Email'},
                        {data: 'Phone', name: 'Phone'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no users found here"
                    }
                });

                usersTable.on('error', function (er) {
                    nWarning("an issue occurred while loading users.");
                });
            } else {
                usersTable.ajax.reload();
            }
        }

        function fetchBranchesTable() {
            if (branchesTable === null) {
                branchesTable = $('#branchesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    //"order": [[6, 'desc']],
                    columnDefs: [
                        /*  {"className": "text-center", "targets": [2]},*/
                        {
                            "render": function (data, type, row) {
                                return data + ", " + row.Address2;
                            },
                            "targets": 2 // the place of col2
                        },
                        {"visible": false, "targets": [3]}
                    ],
                    ajax: {
                        url: "{{ route('branches.index') }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "OurBranchID", name: 'OurBranchID'},
                        {data: 'BranchName', name: 'BranchName'},
                        {data: 'Address1', name: 'Address1'},
                        {data: 'Address2', name: 'Address2'},
                        {data: 'Manager', name: 'Manager', orderable: false, searchable: false},
                        {data: 'Operation', name: 'Operation', orderable: false, searchable: false},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no teams found here"
                    }
                });

                branchesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading teams.");
                });
            } else {
                branchesTable.ajax.reload();
            }
        }

        function fetchTeamsTable() {
            if (teamsTable === null) {
                teamsTable = $('#teamsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    //"order": [[6, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: "{{ route('teams.index') }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'users_count', name: 'users_count', orderable: false, searchable: false},
                        {data: 'Notes', name: 'Notes'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no teams found here"
                    }
                });

                teamsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading teams.");
                });
            } else {
                teamsTable.ajax.reload();
            }
        }

        function fetchRolesTable() {
            if (rolesTable === null) {
                rolesTable = $('#rolesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: "{{ route('roles.index') }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'name', name: 'name'},
                        {data: 'users_count', name: 'users_count', searchable: false, orderable: false},
                        {data: 'created_at', name: 'created_at'},
                        {data: 'creator', name: 'creator', orderable: false, searchable: false},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no roles under filter"
                    }
                });

                rolesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading roles.");
                });
            } else {
                rolesTable.ajax.reload();
            }
        }

    </script>
@endsection
