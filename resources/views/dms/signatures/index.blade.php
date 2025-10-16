@extends('dms.layout')

@section('title','Signatures')
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
                                data-click_url="{{ route('document-signature.create') }}"
                                data-summary_title='<i class="fas fa-signature"></i> create a signature'>
                            <i class="fas fa-signature"></i> Add a signature
                        </button>

                    </div>
                    {{--     <h5 class="card-title mb-0">@yield('title')</h5>--}}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="signaturesTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 ">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Files</th>
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
        let signaturesTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchSignaturesTable();
        });


        function fetchSignaturesTable() {
            if (signaturesTable === null) {
                signaturesTable = $('#signaturesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    //"order": [[6, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [3]}
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
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'Description', name: 'Description'},
                        {data: 'documents_count', name: 'documents_count', searchable: false, orderable: false},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no signatures found here"
                    }
                });

                signaturesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading signatures.");
                    console.log(er);
                });
            } else {
                signaturesTable.ajax.reload();
            }
        }
    </script>
@endsection
