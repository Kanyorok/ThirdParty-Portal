@extends('layouts.app')

@section('title','Teams')

@section('content')
    <div class="mb-3">
        <h1 class="h3 d-inline align-middle">@yield('title')</h1>
        <button class="btn btn-primary float-end ms-2 modal-create-team" type="button"><i
                class="fas fa-plus-circle"></i> Add a Team
        </button>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="teamsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>members</th>
                            <th>Notes</th>
                            <th>actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="NewTeamModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script> const $Modal = $('#NewTeamModal');
        let teamsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchTeamsTable();

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

        });


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
                        url: document.url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'users_count', name: 'users_count'},
                        {data: 'Notes', name: 'Notes'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no teams found here"
                    }
                });

                teamsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading teams.");
                    console.log(er);
                });
            } else {
                teamsTable.ajax.reload();
            }
        }
    </script>
@endsection
