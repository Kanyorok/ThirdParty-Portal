@extends('layouts.app')

@section('title','Contacts')
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
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-1 pb-0">
                    <h3 class="card-title">Unattached Contacts <small class="text-muted">An unattached contacts are not currently tied to any lead or member</small></h3>
                </div>
                <div class="card-body ">
                    <table id="contactsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Label</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>action</th>
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
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchContactsTable();
        });

        function fetchContactsTable(){
            if (!$.fn.DataTable.isDataTable('#contactsTable')) {
                $('#contactsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // "order": [[3, 'asc']],
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Label', name: 'Label'},
                        {data: 'Phone', name: 'Phone'},
                        {data: 'Email', name: 'Email'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no contactsTable under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading contacts.");
                });
            } else {
                $('#contactsTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
