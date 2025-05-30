@extends('layouts.app')

@section('title')
    Bulk Notification
@endsection

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
        <div class="col-md-5 col-xl-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="text-center my-2">{{ $bulkNotification->Label }}</h3>
                </div>
                <div class="card-body my-2">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3"><span>Module : </span><b class="float-end">{{ $bulkNotification->Module }}</b>
                        </li>
                        <li class="mb-3"><span>Notification  Type : </span><b
                                class="float-end">SMS</b></li>
                        <li class="mb-3"><span>Completed On : </span><b
                                class="float-end">{{ $bulkNotification->CompleteOn?->format('M d, Y H:i') }}</b></li>
                        <li class="mb-3"><span>Number of Recipients : </span><b
                                class="float-end">{{ number_format($bulkNotification->Total) }}</b></li>
                        @foreach($bulkNotification->Extra as $key=>$value)
                            <li class="mb-3"><span>{{ $key }}: </span><b
                                    class="float-end">{{ $value }}</b></li>
                        @endforeach
                        <li class="mb-3"><span>Content: </span><br>
                            <p>{{ $bulkNotification->Content }}</p>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$bulkNotification])
                </div>
            </div>
        </div>
        <div class="col-md-7 col-xl-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="card-actions float-end">
                        <button class="btn btn-sm btn-secondary mx-2" onclick="fetchSMSTable()"
                        ><i class="fas fa-refresh"></i></button>

                    </div>
                    Message Sent
                </div>
                <div class="card-body">
                    @if($hasProgress)
                        <div class="progress mb-3" style="height: 20px;">
                            <div id="notificationsProgress"
                                 class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                 style="width: 0" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    @endif
                    <div class="tab-pane m-2" id="tab-0" role="tabpanel">
                        <table id="MessagesTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>SMS ID</th>
                                <th>Loan</th>
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

@endsection
@section('scripts')

    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> let MessagesTable = null, progressInterval = null;
        $(function () {
            fetchSMSTable();
            @if($hasProgress)
            fetchProgress();
            @endif
        });

        function fetchSMSTable() {
            if (MessagesTable === null) {
                MessagesTable = $('#MessagesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    ajax: {
                        url: '{{ route('debt-notification.messages', [$bulkNotification->BulkNotificationID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "SMSId", name: 'SMSId'},
                        {data: 'source', name: 'source', orderable: false, searchable: false},
                        {data: 'Dated', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<span class='text-center'>No records found</span>"
                    }
                });

                MessagesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the messages.");
                });
            } else {
                MessagesTable.ajax.reload();
            }

        }

        @if($hasProgress)
        function fetchProgress() {
            if (progressInterval !== null) {
                clearInterval(progressInterval);
            }
            $.get("{{ route('debt-notification.edit', [$bulkNotification->BulkNotificationID]) }}", function (data) {
                $("#notificationsProgress").width(data.progress + '%').html('<small id="progress-status">' + data.description + '</small>');
                if (data.progress > 99) {
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 3000)
                } else {
                    progressInterval = setInterval(function () {
                        fetchProgress();
                    }, 5000);
                }
            });
        }
        @endif

    </script>
@endsection
