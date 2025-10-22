@extends('layouts.app')

@section('title','Branches')
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
                    <h5 class="card-title mb-0">Branches</h5>
                    <div class="card-actions float-end">
                        <button class="btn btn-primary ms-2  modal-create-branch" type="button">
                            <i class="fas fa-plus"></i> Add a Branch
                        </button>
                    </div>
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
    <div class="modal fade" id="branchesActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createBranchModal">
                        <form action="{{ route('branches.store') }}" method="post" id="createBranchForm"
                              class="row"> @csrf
                            <div class="mb-3 col-6">
                                <label class="form-label" for="Name">Branch Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required
                                       placeholder="Branch Name">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3  col-6">
                                <label class="form-label" for="BranchID">Branch ID <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="BranchID" name="BranchID" required
                                       maxlength="10"
                                       placeholder="Branch ID">
                                <p id="BranchID_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3  col-6">
                                <label class="form-label" for="Address">Address</label>
                                <input type="text" class="form-control" id="Address" name="Address" maxlength="255"
                                       placeholder="Address Line 1">
                                <p id="Address_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3  col-6">
                                <label class="form-label" for="Address2">Address Line 2</label>
                                <input type="text" class="form-control" id="Address2" name="Address2" maxlength="255"
                                       placeholder="Address Line 2">
                                <p id="Address2_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3 col-6">
                                <label class="form-label" for="Phone">Phone</label>
                                <input type="tel" class="form-control" id="Phone" name="Phone" maxlength="20" pattern="^\+[1-9]\d{7,14}$" inputmode="tel"
                                       placeholder="e.g., +12025550123">
                                <p id="Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3  col-6">
                                <label class="form-label" for="Email">Email</label>
                                <input type="email" class="form-control" id="Email" name="Email" maxlength="255"
                                       placeholder="Email Address">
                                <p id="Email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3 col-6">
                                <label class="form-label" for="Manager">Branch Manager</label>
                                <select class="form-control select-users" id="Manager" name="Manager">
                                    <option value="">Select Branch Manager</option>
                                </select>
                                <p id="Manager_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3 col-6">
                                <label class="form-label" for="Operation">Operation Manager</label>
                                <select class="form-control select-users" id="Operation" name="Operation">
                                    <option value="">Select Operation Manager</option>
                                </select>
                                <p id="Operation_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createBranchBtn" type="submit"><i
                                        class="fas fa-save"></i> add Branch
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateBranchModal">
                        <form action="" method="post" id="updateBranchForm" class="row"> @csrf @method('PUT')
                            <div class="mb-3 col-6">
                                <label class="form-label" for="e_Name">Branch Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="e_Name" name="Name" required
                                       placeholder="Branch Name">
                                <p id="e_Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3  col-6">
                                <label class="form-label" for="e_BranchID">Branch ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control " readonly id="e_BranchID" name="BranchID"
                                       maxlength="10"
                                       placeholder="Branch ID">
                                <p id="e_BranchID_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3  col-6">
                                <label class="form-label" for="e_Address">Address</label>
                                <input type="text" class="form-control" id="e_Address" name="Address" maxlength="255"
                                       placeholder="Address Line 1">
                                <p id="e_Address_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3  col-6">
                                <label class="form-label" for="e_Address2">Address Line 2</label>
                                <input type="text" class="form-control" id="e_Address2" name="Address2" maxlength="255"
                                       placeholder="Address Line 2">
                                <p id="e_Address2_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3 col-6">
                                <label class="form-label" for="e_Phone">Phone</label>
                                <input type="tel" class="form-control" id="e_Phone" name="Phone" maxlength="20" pattern="^\+[1-9]\d{7,14}$" inputmode="tel"
                                       placeholder="e.g., +12025550123">
                                <p id="e_Phone_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3  col-6">
                                <label class="form-label" for="e_Email">Email</label>
                                <input type="email" class="form-control" id="e_Email" name="Email" maxlength="255"
                                       placeholder="Email Address">
                                <p id="e_Email_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3 col-6">
                                <label class="form-label" for="e_Manager">Branch Manager</label>
                                <select class="form-control select-users" id="e_Manager" name="Manager">
                                    <option value="">Select Branch Manager</option>
                                </select>
                                <p id="e_Manager_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <div class="mb-3 col-6">
                                <label class="form-label" for="e_Operation">Operation Manager</label>
                                <select class="form-control select-users" id="e_Operation" name="Operation">
                                    <option value="">Select Operation Manager</option>
                                </select>
                                <p id="e_Operation_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>

                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateBranchBtn" type="submit"><i
                                        class="fas fa-save"></i> update Branch
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content text-center with-gradient d-none modal-item" id="trashBranchModal">
                        <h3 class="h3 text-center" id="trashBranchContent"></h3>
                        <div class="mt-2 mb-2">
                            Are you sure you want to trash this branch ?
                        </div>
                        <hr>
                        <form id="trashBranchForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="trashBranchBtn" type="submit"><i
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
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const $Modal = $('#branchesActionsModal');
        let branchesTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchBranchesTable();

            $(document).on('click', '.branch-action-update', function () {
                const data = $(this).data('info').split('~');
                const [managerId, managerName] = $(this).data('manager').split('~');
                const [operationId, operationName] = $(this).data('operation').split('~');
                // Show the modal and set title
                $(".modal-title").html('Update Branch <b>' + data[0] + '</b>');
                $(".modal-item").addClass('d-none');

                // Set form action and method
                $('#updateBranchForm').attr('action', $(this).data('route'));
                // Fill in all form fields
                $('#e_BranchID').val(data[0]);
                $('#e_Name').val(data[1]);
                $('#e_Address').val(data[2]);
                $('#e_Address2').val(data[3]);
                $('#e_Phone').val(data[4]);
                $('#e_Email').val(data[5]);

                // Handle select2 fields with both ID and text
                if (managerId && managerName) {
                    $('#e_Manager').empty().append(new Option(managerName, managerId, true, true)).trigger('change');
                } else {
                    $('#e_Manager').empty().trigger('change');
                }

                if (operationId && operationName) {
                    $('#e_Operation').empty().append(new Option(operationName, operationId, true, true)).trigger('change');
                } else {
                    $('#e_Operation').empty().trigger('change');
                }

                $('#updateBranchModal').removeClass('d-none');
                $Modal.children().first().addClass('modal-lg');
                $Modal.modal('show');
            });

            $('form#updateBranchForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateBranchBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchBranchesTable();
                }
            });

            $(document).on('click', '.branch-action-trash', function () {
                $(".modal-item").addClass('d-none');
                const stuff = $(this).data('info').split('~');
                $('.modal-title').html('<b class="text-danger">Delete</b>  ' + stuff[1]);
                $("#trashBranchContent").html(stuff[1]);
                $('#trashBranchForm').attr('action', $(this).data('route'));
                $('.modal-dialog').removeClass('modal-lg');
                $('#trashBranchModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashBranchForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashBranchBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchBranchesTable();
                }
            });

            $(document).on('click', '.modal-create-branch', function () {
                $(".modal-title").html('Create a new Branch');
                $(".modal-item").addClass('d-none');
                $('#createBranchModal').removeClass('d-none');
                $Modal.children().first().addClass('modal-lg');
                $Modal.modal('show');
            });

            $('form#createBranchForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createBranchBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchBranchesTable();
                }
            });

            $('.select-users').select2({
                placeholder: "Choose users ...", minimumInputLength: 2,
                dropdownParent: $Modal,
                allowClear: true,
                ajax: {
                    url: '{{route('users.select2')}}',
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
        });

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
                        url: '{{ route('branches.index') }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "BranchID", name: 'BranchID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'Address', name: 'Address'},
                        {data: 'Address2', name: 'Address2'},
                        {data: 'Manager', name: 'Manager', orderable: false, searchable: false},
                        {data: 'Operation', name: 'Operation', orderable: false, searchable: false},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no branches found here"
                    }
                });

                branchesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading branchs.");
                    console.log(er);
                });
            } else {
                branchesTable.ajax.reload();
            }
        }
    </script>
@endsection
