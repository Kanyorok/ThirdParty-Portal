@extends('layouts.app')

@section('title')
    {{ $file->Name }}
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    @if($repoService->isRoot())
        <li class="breadcrumb-item"><a href="{{ route('repo.index') }}">Root</a></li>
    @elseif($repoService->parentRoot())
        <li class="breadcrumb-item"><a href="javascript:void(0)">...</a></li>
    @endif
@endsection
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item ">Name: <b class="float-end">{{ $file->Name }}</b></li>
                        <li class="list-group-item">Type : <b class="float-end">{!! $file->ext()->getIcon() !!} &nbsp;
                                {{$file->ext()->name}}</b></li>
                        <li class="list-group-item">Visibility : <b class="float-end">{!! $file->Visibility->icon() !!}
                                &nbsp; {{$file->Visibility->name}}</b></li>
                        <li class="list-group-item">Versions : <b
                                class="float-end">{{ number_format($file->versions_count) }}</b></li>
                        <li class="list-group-item">Size : <b
                                class="float-end">{{  \Illuminate\Support\Number::fileSize( $file->current->Size, 2) }}</b>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="accordion accordion-flush" id="filePropertiesAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="filePropertiesHeader">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#flush-fileProperties" aria-expanded="false"
                                        aria-controls="flush-fileProperties">
                                    File Properties
                                </button>
                            </h2>
                            <div id="flush-fileProperties" class="accordion-collapse collapse"
                                 aria-labelledby="filePropertiesHeader" data-bs-parent="#filePropertiesAccordion">
                                <ul class="list-group list-group-flush">
                                    @foreach($file->properties as $property)
                                        <li class="list-group-item ">{{ $property->Name }} : <b
                                                class="float-end">{{ $property->formated_value }}</b></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$file])
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xxl-9">
            <div class="card ">
                <div class="card-body" style="min-height: 100px" id="FilePreviewPage">
                    <p class="text-center m-5"><i class="fas fa-spinner fa-spin fa-5x"></i><br>loading preview</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header p-0">
                    <div class="nav nav-pills card-header py-2">
                        <ul class="nav" role="tablist">
                            <li class="nav-item"><a class="nav-link active" href="#tab-usersAndTeams"
                                                    data-bs-toggle="tab"
                                                    role="tab" aria-selected="false"
                                                    onclick="fetchFilePermissionsTableTable()">
                                    Permissions
                                </a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-activities" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchActivitiesTable()"
                                >Activities</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-workflow" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchWorkflowTable()"
                                >workflows</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="tab-content p-0">
                        <div class="tab-pane m-2 " id="tab-workflow" role="tabpanel">
                            <table id="ticketWorkflowTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Stage</th>
                                    <th>Status</th>
                                    <th>Dated</th>
                                    <th>By</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="tab-pane m-2" id="tab-activities" role="tabpanel">
                            <table id="ticketActivitiesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Event</th>
                                    <th>Description</th>
                                    <th>By</th>
                                    <th>Dated</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="tab-pane m-2 active show" id="tab-usersAndTeams" role="tabpanel">
                            <table id="filePermissionsTable"
                                   class="table table-striped no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Party</th>
                                    <th>Role</th>
                                    <th>Dated</th>
                                    <th>action</th>
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
        function fetchFilePreview() {
            const previewContainer = document.getElementById('FilePreviewPage');

            fetch("{{ route('file.preview', [$file->DocumentId]) }}")
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    previewContainer.innerHTML = html;
                })
                .catch(error => {
                    previewContainer.innerHTML = `
                        <div class="text-center m-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                            <p class="mt-2">Error loading preview: ${error.message}</p>
                        </div>`;
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            fetchFilePermissionsTableTable()
            fetchFilePreview();
        });

        function fetchFilePermissionsTableTable() {
            if (!$.fn.DataTable.isDataTable('#filePermissionsTable')) {
                $('#filePermissionsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    dom: '<"row"<"col-12 mb-2"tr><"col-12"p>>',
                    "order": [[3, 'desc']],
                    ajax: {
                        url: '{{ route('file-permissions.index',[$file->DocumentId]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'party', name: 'party'},
                        {data: 'Role', name: 'Role'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no permissions under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading permissions.");
                    // console.log(er);
                });
            } else {
                $('#filePermissionsTable').DataTable().ajax.reload();
            }
        }

    </script>
@endsection
