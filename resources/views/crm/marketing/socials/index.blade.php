@extends('layouts.app')

@section('title','Socials')
@section('styles')
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-0 row">
                    <div class="col-10">
                        <ul class="nav nav-tabs profile-tabs" id="employeeTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab"
                                                    role="tab"
                                                    aria-selected="false" onclick="fetchScheduledPostsTableTable()">Scheduled
                                    Posts</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchPublishedPostsTableTable()">Published
                                    Posts</a></li>
                        </ul>
                    </div>
                    <div class="col-2">
                        <div class="float-end mt-3">
                            <a class="btn btn-primary float-end ms-2 btn-sm modal-create-competitor"
                               href="{{ route('socials.create') }}"><i
                                    class="fas fa-plus-circle"></i> Schedule a Post
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-content">
                <div class="tab-pane active" id="tab-0" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <table id="ScheduledPostsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th class="py-1">Image</th>
                                    <th class="py-1">Type</th>
                                    <th class="py-1 w-50">Content</th>
                                    <th class="py-1">By</th>
                                    <th class="py-1">Dated</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab-1" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <table id="PublishedPostsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th class="py-1">Image</th>
                                    <th class="py-1">Type</th>
                                    <th class="py-1">Content</th>
                                    <th class="py-1">Likes</th>
                                    <th class="py-1">Views</th>
                                    <th class="py-1">By</th>
                                    <th class="py-1">Dated</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')

    <script>
        let PublishedPostsTable = null, ScheduledPostsTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchScheduledPostsTableTable();

        });


        function fetchPublishedPostsTableTable() {
            if (PublishedPostsTable === null) {
                PublishedPostsTable = $('#PublishedPostsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: '<"row"<"col-12 mb-2"tr><"col-4"l><"col-4 text-center"i><"col-4"p>>',
                    "order": [[6, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [3, 4]}
                    ],
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'image', name: 'image', orderable: false, searchable: false},
                        {data: 'Type', name: 'Type'},
                        {data: 'Content', name: 'Content'},
                        {data: 'LikesCount', name: 'LikesCount'},
                        {data: 'ViewCount', name: 'ViewCount'},
                        {data: 'creator.Name', name: 'creator.Name'},
                        {data: 'Published_at', name: 'Published_at'},
                    ], "oLanguage": {
                        "sEmptyTable": "<p class='my-2'>There are no published posts, <a href='{{ route('socials.create') }}'>create some</a>.</p>"
                    }
                });

                PublishedPostsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading published posts.");
                    console.log(er);
                });
            } else {
                PublishedPostsTable.ajax.reload();
            }
        }

        function fetchScheduledPostsTableTable() {
            if (ScheduledPostsTable === null) {
                ScheduledPostsTable = $('#ScheduledPostsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: '<"row"<"col-12 mb-2"tr><"col-4"l><"col-4 text-center"i><"col-4"p>>',
                    "order": [[4, 'desc']],
                    /* "columnDefs": [
                         {"className": "text-center", "targets": [2]}
                     ],*/
                    ajax: {
                        url: getDocumentUrl() + '?filter=scheduled',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "image",
                                sort: "SocialID",
                            }, name: 'SocialID', searchable: false
                        },
                        {data: 'Type', name: 'Type'},
                        {data: 'Content', name: 'Content'},
                        {data: 'creator.Name', name: 'creator.Name'},
                        {data: 'Scheduled_at', name: 'Scheduled_at'},
                    ], "oLanguage": {
                        "sEmptyTable": "<p class='my-2'>There are no scheduled posts, <a href='{{ route('socials.create') }}'>Schedule some</a></p>"
                    }
                });

                ScheduledPostsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading Scheduled posts.");
                    console.log(er);
                });
            } else {
                ScheduledPostsTable.ajax.reload();
            }
        }

    </script>
@endsection
