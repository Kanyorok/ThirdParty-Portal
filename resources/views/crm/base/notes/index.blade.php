@extends('layouts.app')

@section('title','Notes')
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="notesTable" class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>Party</th>
                            <th>Note</th>
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
@endsection
@section('scripts')

    <script>
        let notesTable = null;
        $(function () {
           $.fn.dataTable.ext.errMode = 'none';
            fetchNotesTable();
        });

        function fetchNotesTable() {
            if (notesTable === null) {
                notesTable = $('#notesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('notes.index') }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'party', name: 'party'},
                        {data: 'Notes', name: 'Notes'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>There are no notes found here</p>"
                    }
                });

                notesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the notes.");
                    console.log(er);
                });
            } else {
                notesTable.ajax.reload();
            }
        }
    </script>
@endsection
