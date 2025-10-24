@extends('dms.layout')

@section('title','Document Validation')
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
                <div class="card-body">
                    <table id="documentValidationTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Stage</th>
                            <th>Actions</th>
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
        let documentValidationTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchDocumentValidationTable();
        });

        function fetchDocumentValidationTable() {
            if (documentValidationTable === null) {
                documentValidationTable = $('#documentValidationTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[5, 'desc']],
                    /*"columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],*/
                    ajax: {
                        url: document.url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                        {data: 'Type', name: 'Type'},
                        {data: 'Name', name: 'Name'},
                        {data: 'Stage', name: 'Stage', orderable: false, searchable: false},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no documents pending approval found."
                    }
                });

                documentValidationTable.on('error', function (er) {
                    nWarning("an issue occurred while loading document validation.");
                    console.log(er);
                });
            } else {
                documentValidationTable.ajax.reload();
            }
        }
    </script>
@endsection
