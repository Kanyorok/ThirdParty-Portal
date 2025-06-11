@extends('layouts.app')

@section('title','Reviews')
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="reviewsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Rate</th>
                            <th>Sentiment</th>
                            <th>From</th>
                            <th>Source</th>
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
        let reviewsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchReviewsTable();
        });

        function fetchReviewsTable() {
            if (reviewsTable === null) {
                reviewsTable = $('#reviewsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[5, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: document.URL,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'branch.BranchName', name: 'branch.BranchName'},
                        {data: 'Rating', name: 'Rating'},
                        {data: 'Tonality', name: 'Tonality'},
                        {data: 'Party', name: 'Party'},
                        {data: 'Source', name: 'Source'},
                        {data: 'CreatedOn', name: 'CreatedOn'}
                    ], "oLanguage": {
                        "sEmptyTable": "No reviews under this filter."
                    }
                });

                reviewsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the reviews.");
                    console.log(er);
                });
            } else {
                reviewsTable.ajax.reload();
            }
        }
    </script>
@endsection
