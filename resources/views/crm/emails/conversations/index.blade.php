@extends('layouts.app')

@section('title','Mail')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css"
          integrity="sha512-ngQ4IGzHQ3s/Hh8kMyG4FC74wzitukRMIcTOoKT3EyzFZCILOPF0twiXOQn75eDINUfKBYmzYn2AA8DkAk8veQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection
@section('content')
    <div class="row">
        <div class="col-12" id="MailListMainContent">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-inbox" data-bs-toggle="tab" role="tab"
                                            aria-selected="true" onclick="fetchConversations(true)">INBOX</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#tab-drafts" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchDrafts(true);">DRAFTS</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-sent" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchSent(true)">SENT</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-inbox" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-4 col-md-3 col-6">
                                <select name="searchByType" id="searchByType" class="form-control">
                                    <option value="UNREAD" selected>UNREAD</option>
                                    <option value="INBOX">INBOX</option>
                                </select>
                            </div>
                            <div class="col-sm-8 col-md-9 col-6">
                                   <span class="float-end"><button class="btn btn-link p-1 pt-0" type="button"
                                                                   onclick="fetchConversations()"><i
                                               class="fas fa-refresh"></i></button></span>
                            </div>
                        </div>
                        <table id="conversationsTable"
                               class="table dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead class="d-none">
                            <tr>
                                <th>Party</th>
                                <th>Subject</th>
                                <th>Dated</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="tab-drafts" role="tabpanel">
                        <h4 class="tab-title border-bottom border-1">Drafts <span class="float-end"><button
                                    class="btn btn-link p-1 pt-0" type="button" onclick="fetchDrafts()"><i
                                        class="fas fa-refresh"></i></button></span></h4>
                        <div class="clearfix"></div>
                        <table id="DRAFTSTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead class="d-none">
                            <tr>
                                <th>Party</th>
                                <th>Subject</th>
                                <th>Dated</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane" id="tab-sent" role="tabpanel">
                        <h4 class="tab-title border-bottom border-1">Sent Emails <span class="float-end"><button
                                    class="btn btn-link p-1 pt-0" type="button" onclick="fetchSent()"><i
                                        class="fas fa-refresh"></i></button></span></h4>
                        <div class="clearfix"></div>
                        <table id="SENTTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead class="d-none">
                            <tr>
                                <th>Party</th>
                                <th>Subject</th>
                                <th>Dated</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6" id="MailMainContent">

        </div>
    </div>

@endsection
@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"
            integrity="sha512-6F1RVfnxCprKJmfulcxxym1Dar5FsT/V2jiEUvABiaEiFWoQ8yHvqRM/Slf0qJKiwin6IDQucjXuolCfCKnaJQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const Listing = $("#MailListMainContent"), Content = $("#MailMainContent");
        let DRAFTSTable = null, SENTTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchConversations();

            $(document).on('dblclick', '.click-email-details', function () {
                getConversationDetails($(this).data('click_url'));
            });

            $('#searchByType').change(function () {
                fetchConversations();
                //$('#UnreadConversationsTable').DataTable().draw();
            });
        });

        function getConversationDetails(url) {
            Listing.addClass('col-sm-6');
            Content.removeClass('d-none')
                .html('<div class="card"><div class="card-body text-center my-4"><div class="spinner-grow text-secondary me-2" role="status"><span class="visually-hidden">Loading...</span></div></div></div>');
            $.get(url, function (data) {
                Content.html(data);
            }).fail(function (jqXHR) {
                nError(jqXHR.responseJSON.message);
                closeEmailDetails();
            });
        }

        $(document).on('click', '.close-email-details', function () {
            closeEmailDetails();
        });

        function closeEmailDetails() {
            Listing.removeClass('col-sm-6');
            Content.addClass('d-none')
        }


        function fetchConversations(close = false) {
            if (close) {
                closeEmailDetails();
            }
            if (!$.fn.DataTable.isDataTable('#conversationsTable')) {
                $('#conversationsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    "pageLength": 100,
                    dom: '<"row"<"col-12 mb-2"tr><"col-5 text-center"i><"col-7"p>>',
                    columnDefs: [
                        {
                            "render": function (data, type, row) {
                                //console.log(row.emails_count);
                                let Subject = row.email.Subject;
                                if (row.emails_count > 1) {
                                    Subject = " (" + row.emails_count + ")&nbsp;" + Subject;
                                }
                                return '<div class="row"><div class="col-11"><p class="m-0 p-0">' + data + '<span class="float-end">' + row.ModifiedOn + '</span></p>' +
                                    '<p class="m-0 p-0">' + Subject + '</p></div><div class="col-1 text-center">' + row.action + '</div></div>';
                                /* return "<p class='m-0 p-0'>" + data + "<span class='float-end'>" + row.ModifiedOn + " " +
                                "<button class='btn btn-sm btn-link p-1 text-danger mx-1'><i class='fas fa-trash'></i></button>" +
                                "<button class='btn btn-sm btn-link p-1 text-danger mx-1'><i class='fas fa-trash'></i></button>" +
                                "</span></p><p class='m-0 p-0'>" + Subject + "</p>"*/
                            },
                            "targets": 0
                        },
                        {"visible": false, "targets": [1, 2]}
                    ],
                    ajax: {
                        url: getDocumentUrl(),
                        'data': function (data) {
                            data.searchByType = $('#searchByType').val();
                        },
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "party", name: 'party', searchable: false, orderable: false},
                        {data: 'email.Subject', name: 'email.Subject'},
                        {data: 'ModifiedOn', name: 'ModifiedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "<span class='text-center'>There are no Emails Found</span>"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading emails.");
                    // console.log(er);
                });
            } else {
                $('#conversationsTable').DataTable().ajax.reload();
            }
        }


        function fetchDrafts(close = false) {
            if (close) {
                closeEmailDetails();
            }
            if (DRAFTSTable === null) {
                DRAFTSTable = _fetchEmails('DRAFTS');
            } else {
                DRAFTSTable.ajax.reload();
            }
        }

        function fetchSent(close = false) {
            if (close) {
                closeEmailDetails();
            }
            if (SENTTable === null) {
                SENTTable = _fetchEmails('SENT');
            } else {
                SENTTable.ajax.reload();
            }
        }

        function _fetchEmails(type = '') {
            let table = $('#' + type + 'Table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                "order": [[2, 'desc']],
                "pageLength": 100,
                dom: '<"row"<"col-12 mb-2"tr><"col-5 text-center"i><"col-7"p>>',
                columnDefs: [
                    // {"className": "text-center", "targets": [0]},
                    {
                        "render": function (data, type, row) {
                            return "<p class='m-0 p-0'>" + data + "&nbsp;" + row.Status + "<span class='float-end'>" + row.ModifiedOn + "</span><br>" + row.Subject + "</p>"
                        },
                        "targets": 0 // the place of col2
                    },
                    {"visible": false, "targets": [1, 2]}
                ],
                ajax: {
                    url: '{{ route('emails.index') }}?_filter=' + type,
                    error: function (jqXHR) {
                        codeNotify(jqXHR.status);
                    }
                },
                columns: [
                    {data: "party", name: 'party', searchable: false, orderable: false},
                    {data: 'Subject', name: 'Subject'},
                    {data: 'Dated', name: 'CreatedOn'},
                ], "oLanguage": {
                    "sEmptyTable": "<span class='text-center'>No emails found here</span>"
                }
            });

            table.on('error', function () {
                nWarning("an issue occurred while loading the emails.");
            });
            return table;
        }


    </script>
@endsection
