@extends('layouts.app')

@section('title','Mail')
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-md-3 col-xl-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ config('from.address') }}</h5>
                </div>
                <div class="list-group list-group-flush" role="tablist">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="list"
                       href="#mail-inbox-tab"
                       onclick="fetchInbox();" role="tab">
                        <i class="fas fa-inbox"></i> Inbox
                    </a>
                    <a class="list-group-item list-group-item-action " data-bs-toggle="list" href="#mail-draft-tab"
                       onclick="fetchDrafts();" role="tab">
                        <i class="fas fa-edit"></i> Drafts
                    </a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#mail-sent-tab"
                       onclick="fetchSent();" role="tab">
                        <i class="fas fa-send"></i> Sent
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-9 col-xl-10">
            <div id="MainEmailContent"></div>
            <div class="tab-content" id="MainParentContent">
                <div class="tab-pane fade show active" id="mail-inbox-tab" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <div class="dropdown position-relative">
                                    <a href="#" data-bs-toggle="dropdown" data-bs-display="static">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round"
                                             class="feather feather-more-horizontal align-middle">
                                            <circle cx="12" cy="12" r="1"></circle>
                                            <circle cx="19" cy="12" r="1"></circle>
                                            <circle cx="5" cy="12" r="1"></circle>
                                        </svg>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#"> <i
                                                class="align-middle fas fa-address-book"></i> New to Lead </a>
                                        <a class="dropdown-item" href="#"> <i class="align-middle"
                                                                              data-feather="users"></i> New to Member
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <h5 class="card-title mb-0">Inbox</h5>
                        </div>
                        <div class="card-body">
                            <table id="INBOXTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>Party</th>
                                    <th>Subject</th>
                                    <th>Dated</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="mail-draft-tab" role="tabpanel">
                    <div class="card">
                        <div class="card-header border border-bottom">
                            <h5 class="card-title mb-0">Drafts</h5>
                        </div>
                        <div class="card-body">
                            <table id="DRAFTSTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>Party</th>
                                    <th>Subject</th>
                                    <th>Dated</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="mail-sent-tab" role="tabpanel">
                    <div class="card">
                        <div class="card-header border border-bottom">
                            <h5 class="card-title mb-0">Sent Emails</h5>
                        </div>
                        <div class="card-body">
                            <table id="SENTTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>Party</th>
                                    <th>Subject</th>
                                    <th>Dated</th>
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

@endsection
@section('scripts')
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        let INBOXTable = null, DRAFTSTable = null, SENTTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchInbox();
        });

        $(document).on('click', '.click-email-details', function () {
            const url = $(this).data('click_url');
            $("#MainEmailContent").html('<div class="card"><div class="card-body text-center my-4"><div class="spinner-grow text-secondary me-2" role="status"><span class="visually-hidden">Loading...</span></div></div></div>');
            $('#MainParentContent').addClass('d-none');
            $.get(url, function (data) {
                $("#MainEmailContent").html(data);
            }).fail(function (jqXHR) {
                nError(jqXHR.responseJSON.message);
                closeEmailDetails();
            });
        });

        function closeEmailDetails() {
            $("#MainEmailContent").html('');
            $('#MainParentContent').removeClass('d-none');

        }

        function fetchInbox() {
            closeEmailDetails();
            if (INBOXTable === null) {
                INBOXTable = fetchEmails('INBOX');
            } else {
                INBOXTable.ajax.reload();
            }
        }

        function fetchDrafts() {
            closeEmailDetails();
            if (DRAFTSTable === null) {
                DRAFTSTable = fetchEmails('DRAFTS');
            } else {
                DRAFTSTable.ajax.reload();
            }
        }

        function fetchSent() {
            closeEmailDetails();
            if (SENTTable === null) {
                SENTTable = fetchEmails('SENT');
            } else {
                SENTTable.ajax.reload();
            }
        }

        function fetchEmails(type) {
            let URL = document.URL;
            URL = URL.replace('#', '') + '?_filter=' + type;

            let table = $('#' + type + 'Table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                "order": [[2, 'desc']],
                columnDefs: [
                    {"className": "text-center", "targets": [3]},
                    {
                        "render": function (data, type, row) {
                            if (row.OtherNames === null) {
                                return data
                            }
                            return data + " " + row.OtherNames;
                        },
                        "targets": 1 // the place of col2
                    },
                    {"visible": false, "targets": [2]}
                ],
                ajax: {
                    url: URL,
                    error: function (jqXHR) {
                        codeNotify(jqXHR.status);
                    }
                },
                columns: [
                    {data: "party", name: 'party', searchable: false, orderable: false},
                    {data: 'Subject', name: 'Subject'},
                    {data: 'Dated', name: 'CreatedOn'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ], "oLanguage": {
                    "sEmptyTable": "<span class='text-center'>No records found</span>"
                }
            });

            table.on('error', function () {
                nWarning("an issue occurred while loading the emails.");
            });
            return table;
        }
    </script>
@endsection
