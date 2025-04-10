@extends('layouts.app')

@section('title','Users')
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
            <div class="card mb-3">
                <div class="card-body">
                    <table id="usersTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Gender</th>
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
    <div class="modal fade" id="NewUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createUserModal">
                        <form action="{{ route('users.store') }}" method="post" id="createUserForm"> @csrf
                            <div class="row">
                                <div class="col-sm-6 col-12 mb-3">
                                    <label class="form-label" for="UserID">UserID <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="UserID" name="UserID" required
                                           placeholder="UserID">
                                    <p id="UserID_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <div class="form-check form-switch mt-3">
                                        <input class="form-check-input" type="checkbox" id="SyncAccount"
                                               name="SyncAccount">
                                        <label class="form-check-label" for="SyncAccount">Sync Account with CBS</label>
                                    </div>
                                    <p id="SyncAccount_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label class="form-label" for="Name">Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="Name" name="Name" required
                                           placeholder="Name">
                                    <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label for="Gender" class="form-label">Gender <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="Gender" id="Gender" required>
                                        @foreach(App\Enums\GenderEnum::getAll() as $gender)
                                            <option value="{{ $gender->value }}">{{ $gender->name }}</option>
                                        @endforeach
                                    </select>
                                    <p id="Gender_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label class="form-label" for="Phone">Phone Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="Phone" name="Phone"
                                           placeholder="Phone Number" required>
                                    <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="col-sm-6 col-12 mb-3">
                                    <label class="form-label" for="Email">Email <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="Email" name="Email"
                                           placeholder="Email">
                                    <p id="Email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label" for="Notes">Notes </label>
                                    <textarea name="Notes" id="Notes" rows="3" class="form-control"></textarea>
                                    <p id="Notes_end_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createUserBtn" type="submit"><i
                                        class="fas fa-save"></i> add User
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
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script> const $Modal = $('#NewUserModal');
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
                        {data: 'Gender', name: 'Gender'},
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
