@extends('layouts.app')

@section('title','Roles')

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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-actions float-end">
                        <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                data-click_url="{{ route('roles.create') }}"
                                data-summary_title="Create a New Role">
                            <i class="fas fa-plus"></i> Add a Role
                        </button>
                    </div>
                    <h5 class="card-title mb-0">Roles List</h5>
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
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Trash Role Modal --}}
    <div class="modal fade" id="rolesActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashRoleModal">
                        <h3 class="h3 text-center" id="trashRoleContent"></h3>
                        <div class="mt-2 mb-2">Are you sure you want to trash this role?</div>
                        <hr>
                        <form id="trashRoleForm" method="post">@csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-success float-start" data-bs-dismiss="modal">no, keep</button>
                                <button class="btn btn-danger float-end" id="trashRoleBtn" type="submit">
                                    <i class="fas fa-trash"></i> yes, delete
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- View Role Modal --}}
    <div class="modal fade" id="viewRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Role Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="roleDetailsContent">
                    <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const $TrashModal = $('#rolesActionsModal');
        let rolesTable = null;

        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchRolesTable();

            // Trash Role
            $(document).on('click', '.trash-role', function () {
                $(".modal-item").addClass('d-none');
                const [url, name] = $(this).data('info').split('~');
                $('.modal-title').html('<b class="text-danger">Delete</b> ' + name);
                $("#trashRoleContent").html(name);
                $('#trashRoleForm').attr('action', url);
                $('.modal-dialog').removeClass('modal-lg');
                $('#trashRoleModal').removeClass('d-none');
                $TrashModal.modal('show');
            });

            $('#trashRoleForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashRoleBtn'), false, true, true)) {
                    $TrashModal.modal('hide');
                    fetchRolesTable();
                }
            });

            // View Role
            $(document).on('click', '.view-role', function () {
                const roleId = $(this).data('role_id');
                $('#viewRoleModal').modal('show');
                $('#roleDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');

                $.get(`{{ url('settings/roles') }}/${roleId}/ajax`, function (data) {
                    $('#roleDetailsContent').html(data);
                }).fail(function () {
                    $('#roleDetailsContent').html('<div class="text-danger text-center">Failed to load role details.</div>');
                });
            });
        });

        function fetchRolesTable() {
            if (rolesTable === null) {
                rolesTable = $('#rolesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    order: [[3, 'desc']],
                    columnDefs: [{"className": "text-center", "targets": [2]}],
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
                    ],
                    oLanguage: {
                        sEmptyTable: "No roles found under current filter"
                    }
                });

                rolesTable.on('error', function () {
                    nWarning("An issue occurred while loading roles.");
                });
            } else {
                rolesTable.ajax.reload();
            }
        }
    </script>
@endsection
