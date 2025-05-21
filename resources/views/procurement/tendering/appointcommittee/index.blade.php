@extends('layouts.app')

@section('title','Users')
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
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-actions float-end">
                        <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                data-click_url="{{ route('appointcommittee.create') }}"
                                data-summary_title="Add a User">
                            <i class="fas fa-plus-circle"></i> Add a User
                        </button>
                    </div>
                    <h5 class="card-title mb-0">@yield('title')</h5>
                </div>
                <div class="card-body">
                    <table id="usersTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
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
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        let usersTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchUsersTable();

            $(document).on('click', '.modal-create-user', function () {
                $(".modal-title").html('Create a new User');
                $(".modal-item").addClass('d-none');
                $('#createUserModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createUserForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createUserBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchUsersTable();
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
                        url: document.url,
                        error: function (request) {
                            if (request.status === 400 && request.responseJSON.message) {
                                nWarning(request.responseJSON.message);
                            } else {
                                codeNotify(request.status);
                            }
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
                        {data: 'branch.Name', name: 'branch.Name', orderable: false},
                        {data: 'Email', name: 'Email'},
                        {data: 'Phone', name: 'Phone'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no users found here"
                    }
                });

                usersTable.on('error', function (er) {
                    nWarning("an issue occurred while loading users.");
                    console.log(er);
                });
            } else {
                usersTable.ajax.reload();
            }
        }
    </script>
@endsection
