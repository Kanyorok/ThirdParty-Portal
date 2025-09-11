@extends('dms.layout')

@section('title','Legal Hold')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="card-actions float-end">
                        <button class="btn btn-primary ms-2 click-summary-data" type="button"
                                data-click_url="{{ route('legal-hold.create') }}"
                                data-summary_title='<i class="fas fa-tag"></i> create a legal hold'>
                            <i class="fas fa-tag"></i> &nbsp; Tag Based Hold
                        </button>

                    </div>
                    <h5 class="card-title mb-0">@yield('title')</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="LegalHoldsTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 ">
                            <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Name</th>
                                <th>Documents</th>
                                <th>Start</th>
                                <th>Status</th>
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
    <script>
        let LegalHoldsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchLegalHoldsTable();
        });


        function fetchLegalHoldsTable() {
            if (LegalHoldsTable === null) {
                LegalHoldsTable = $('#LegalHoldsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    //"order": [[6, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (request) {
                            if (request.status === 400 && request.responseJSON.message) {
                                nWarning(request.responseJSON.message);
                            } else {
                                codeNotify(request.status);
                            }
                        }
                    },
                    columns: [
                        {data: "Ref", name: 'Ref'},
                        {data: 'Name', name: 'Name'},
                        {data: 'documents_count', name: 'documents_count', searchable: false, orderable: false},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'Status', name: 'Status'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no Legal Holds found here"
                    }
                });

                LegalHoldsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading Legal Holds.");
                    console.log(er);
                });
            } else {
                LegalHoldsTable.ajax.reload();
            }
        }
    </script>
@endsection
