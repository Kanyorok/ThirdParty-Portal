@extends('layouts.app')
@section('title', $module->description().' Reports')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="reportsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>actions</th>
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
            fetchReportsTable();
        });

        function fetchReportsTable() {
            if (!$.fn.DataTable.isDataTable('#reportsTable')) {
                $('#reportsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: '<"row"<"col-12"r><"col-6"l><"col-6"f><"col-12 w-100 my-3"t><"col-6"i><"col-6"p>>',
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
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no reports found, under current filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading reports.");
                    // console.log(er);
                });
            } else {
                $('#reportsTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
