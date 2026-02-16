@php use App\Models\Communication\BulkNotification; @endphp
@extends('layouts.app')

@section('title','Debt Collection Bulk Notification')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="float-end">
                        @can('create',BulkNotification::class)
                            <a href="{{ route('debt-notification.create') }}" class="btn btn-primary"><i
                                    class="fas fa-plane-departure"></i> send notification</a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <table id="notificationTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Label</th>
                            <th>Recipients</th>
                            <th>Complete On</th>
                            <th>Sent By</th>
                            <th>Dated</th>
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

    <script>
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchNotificationsTable();
        });

        function fetchNotificationsTable() {
            if (!$.fn.DataTable.isDataTable('#notificationTable')) {
                $('#notificationTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[5, 'desc']],
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Label', name: 'Label'},
                        {data: 'Total', name: 'Total'},
                        {data: 'CompleteOn', name: 'CompleteOn'},
                        {data: 'creator', name: 'creator', orderable: false, searchable: false},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no notification under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading notification.");
                });
            } else {
                $('#notificationTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
