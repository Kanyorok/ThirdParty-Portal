@extends('layouts.app')

@section('title','Roles')
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
    </div>
    <div class="row">
        <div class="col-12">
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
    </div>
    <div class="modal fade" id="rolesActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
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

                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script> const $Modal = $('#rolesActionsModal');
        let rolesTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchRolesTable();

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
        });


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
                        url: getDocumentUrl(),
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
                    console.log(er);
                });
            } else {
                rolesTable.ajax.reload();
            }
        }
    </script>
@endsection
