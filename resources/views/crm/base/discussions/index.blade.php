@extends('layouts.app')

@section('title','Discussions')
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="discussionsTable" class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Party</th>
                            <th>From</th>
                            <th>Discussion</th>
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
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script>const searchBtn =  $('#searchFormBtn'), searchQuery =  $('#searchFormQ');
        let discussionsTable = null;
        $(function () {
           $.fn.dataTable.ext.errMode = 'none';
            fetchDiscussionsTable();
        });

        function fetchDiscussionsTable() {
            if (discussionsTable === null) {
                discussionsTable = $('#discussionsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: document.url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: {
                                _: "DT_RowIndex",
                                sort: "DiscussionID",
                            }, name: 'DiscussionID', searchable: false
                        },
                        {data: 'party', name: 'party'},
                        {data: 'SourceType', name: 'SourceType'},
                        {data: 'Discussion', name: 'Discussion'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>There are no discussions found here</p>"
                    }
                });

                discussionsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the client discussions.");
                    console.log(er);
                });
            } else {
                discussionsTable.ajax.reload();
            }
        }
    </script>
@endsection
