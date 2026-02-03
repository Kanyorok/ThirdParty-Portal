@extends('layouts.app')

@section('title','Mailbox')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/summernote/summernote-bs5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
@endsection
@section('content')

    <div class="row">
        <div class="col-12" id="MailListMainContent">
            <div class="card">
                <div class="card-body py-0">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-inbox" role="tab"
                               aria-selected="false" tabindex="-1" onclick="fetchConversations(true)">
                                <span><i class="ti ti-inbox"></i> Inbox </span>
                                {{--<span class="avtar avtar-xs">4</span> todo add unread--}}</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link " href="#tab-drafts" data-bs-toggle="tab" role="tab"
                               aria-selected="false" onclick="fetchDrafts(true);">
                                <i class="ti ti-file-text"></i> Drafts</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="profile-tab-3" href="#tab-sent" data-bs-toggle="tab" role="tab"
                               aria-selected="false" onclick="fetchSent(true)">
                                <i class="ti ti-send"></i> Sent</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content">
                <div class="tab-pane active" id="tab-inbox" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-sm-6 col-12">
                                    <select name="searchByType" id="searchByType" class=" form-control-sm">
                                        <option value="UNREAD" selected>UNREAD</option>
                                        <option value="INBOX">INBOX</option>
                                    </select></div>
                                <div class="col-sm-6 col-12"> <span class="float-end"><button
                                            class="btn btn-link p-1 pt-0" type="button"
                                            onclick="fetchConversations()"><i
                                                class="fas fa-refresh"></i></button></span></div>
                            </div>
                        </div>
                        <div class="card-body">
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
                    </div>
                </div>
                <div class="tab-pane" id="tab-drafts" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="tab-title border-bottom border-1">Drafts <span class="float-end"><button
                                        class="btn btn-link p-1 pt-0" type="button" onclick="fetchDrafts()"><i
                                            class="fas fa-refresh"></i></button></span></h4>
                        </div>
                        <div class="card-body">
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
                    </div>
                </div>
                <div class="tab-pane" id="tab-sent" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="tab-title border-bottom border-1">Sent Emails <span class="float-end"><button
                                        class="btn btn-link p-1 pt-0" type="button" onclick="fetchSent()"><i
                                            class="fas fa-refresh"></i></button></span></h4>
                        </div>
                        <div class="card-body">
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
                        "sEmptyTable": '<div class="row g-0 justify-content-center align-items-center h-100"><div class="col-md-8 col-sm-10 text-center"><img src="{{ asset('assets/img/img-empty-mail.png')}}" alt="img" class="img-fluid mb-4"><h2><b>There is No Mail</b></h2><p class="mb-0 text-muted">When You have message that will Display here</p></div></div>'
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading emails.");
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
                    "sEmptyTable": '<div class="row g-0 justify-content-center align-items-center h-100"><div class="col-md-8 col-sm-10 text-center"><img src="{{ asset('assets/img/img-empty-mail.png')}}" alt="img" class="img-fluid mb-4"><h2><b>There is No Mail</b></h2><p class="mb-0 text-muted">When You have message that will Display here</p></div></div>'
                }
            });

            table.on('error', function () {
                nWarning("an issue occurred while loading the emails.");
            });
            return table;
        }


    </script>
@endsection
