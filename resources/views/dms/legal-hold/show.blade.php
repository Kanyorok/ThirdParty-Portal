@php use App\Enums\Core\VisibilityEnum;use App\Enums\DMS\LegalHoldStatusEnum; @endphp
@extends('dms.layout')

@section('title')
    {{ \Illuminate\Support\Str::limit($hold->Ref,50) }}
@endsection
@section('styles')

@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    <li class="breadcrumb-item"><a href="{{ route('legal-hold.index') }}">Legal Holds</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 ">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center"> {{ $hold->Ref }} </h2>
                    <p class="text-center h5">{{ $hold->Name  }}</p>
                    <p class="text-center">Documents : <b>{{ number_format($hold->documents_count) }}</b></p>
                    <p class="text-center">{{ $hold->Description }}</p>
                    <div class="row">
                        @switch($hold->Status->value)
                            @case(LegalHoldStatusEnum::Canceled->value)
                                <div class="col-12">
                                    <p class="h4 text-warning text-center"><b>CANCELED </b>
                                        on {{  $hold->ReleasedOn?->format('F d, Y h:i A') }}</p>
                                </div>
                                @break
                            @case(LegalHoldStatusEnum::Released->value)
                                <div class="col-12">
                                    <p class="h4 text-primary text-center"><b>RELEASED </b>
                                        on {{  $hold->ReleasedOn?->format('F d, Y h:i A') }}</p>
                                </div>
                                @break
                            @case(LegalHoldStatusEnum::Active->value)
                                <div class="col-sm-6 col-12">
                                    @can('update', $hold)
                                        <button class="btn btn-info btn-sm modal-update-legal-hold w-100 my-2"
                                                type="button"><i
                                                class="fas fa-edit"></i> update
                                        </button>
                                    @endcan
                                </div>
                                <div class="col-sm-6 col-12">
                                    @can('delete', $hold)
                                        <button class="btn btn-warning btn-sm modal-trash-legal-hold w-100 my-2"
                                                type="button">
                                            <i
                                                class="fas fa-times"></i> cancel
                                        </button>
                                    @endcan
                                </div>
                                <div class="col-sm-6 col-12">
                                    @can('delete', $hold)
                                        <button class="btn btn-primary btn-sm modal-release-legal-hold w-100 my-2"
                                                type="button">
                                            <i
                                                class="fas fa-check"></i> release
                                        </button>
                                    @endcan
                                </div>
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$hold])
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body py-0">
                    <ul class="nav nav-tabs profile-tabs" id="LegalHoldTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="document-legal-hold-tab-2" href="#tab-1" data-bs-toggle="tab"
                               role="tab"
                               aria-selected="false" onclick="fetchDocumentsTable()">
                                <i class="fas fa-file-lines me-2"></i> Documents</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link " id="document-legal-hold-tab-1" data-bs-toggle="tab" href="#tab-0"
                               role="tab"
                               aria-selected="false" tabindex="-1" onclick="fetchActivitiesTable()">
                                Activities</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div class="tab-pane" id="tab-0" role="tabpanel" aria-labelledby="document-legal-hold-tab-1">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Activities</h5></div>
                                <div class="card-body  table-responsive">
                                    <table id="holdActivitiesTable"
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
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane active show" id="tab-1" role="tabpanel"
                     aria-labelledby="document-legal-hold-tab-2">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5>Documents</h5></div>
                                <div class="card-body  table-responsive">
                                    <table id="DocumentsTable"
                                           class="table dataTable no-footer dtr-inline w-100">
                                        <thead>
                                        <tr class="d-none">
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
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

        </div>
    </div>

    <div class="modal fade" id="LegalHoldActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateLegalHoldModal">
                        <form action="{{ route('legal-hold.update',[$hold->Ref]) }}" method="post"
                              id="updateLegalHoldForm"> @csrf
                            <div class="mb-3">@method('put')
                                <label class="form-label" for="Ref">Ref </label>
                                <input type="text" class="form-control" id="Ref" name="Ref" required
                                       placeholder="Reference No." value="{{ $hold->Ref }}" readonly>
                                <p id="Ref_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required
                                       placeholder="Name" value="{{ $hold->Name }}">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Description">Description </label>
                                <textarea name="Description" id="Description" rows="3" class="form-control"
                                          maxlength="1000">{{ $hold->Description }}</textarea>
                                <p id="Description_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateLegalHoldBtn" type="submit"><i
                                        class="fas fa-save"></i>
                                    update details
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="cancelLegalHoldModal">
                        <h4 class="text-danger">
                            Cancel Legal Hold <b>{{ $hold->Ref }}</b> ?
                        </h4>
                        <form id="cancelLegalHoldForm" method="post"
                              action="{{ route('legal-hold.destroy',[$hold->Ref]) }}"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="cancelLegalHoldBtn"
                                        type="submit"><i
                                        class="fas fa-times"></i> yes, cancel Hold
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="releaseLegalHoldModal">
                        <h4 class="text-primary">
                            Release Legal Hold <b>{{ $hold->Ref }}</b> ?
                        </h4>
                        <form id="releaseLegalHoldForm" method="post"
                              action="{{ route('legal-hold.release',[$hold->Ref]) }}"> @csrf
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-primary float-end" id="releaseLegalHoldBtn"
                                        type="submit"><i
                                        class="fas fa-check"></i> yes, release Hold
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>let DocumentsTable = null;
        const $Modal = $('#LegalHoldActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchDocumentsTable();


            $(document).on('click', '.modal-release-legal-hold', function () {
                $(".modal-title").html('Release Legal Hold : {{ $hold->Name }}');
                $(".modal-item").addClass('d-none');
                $('#releaseLegalHoldModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#releaseLegalHoldForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#releaseLegalHoldBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            $(document).on('click', '.modal-trash-legal-hold', function () {
                $(".modal-title").html('Cancel Legal Hold : {{ $hold->Name }}');
                $(".modal-item").addClass('d-none');
                $('#cancelLegalHoldModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#cancelLegalHoldForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#cancelLegalHoldBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-update-legal-hold', function () {
                $(".modal-title").html('Update Legal Hold : {{ $hold->Name }}');
                $(".modal-item").addClass('d-none');
                $('#updateLegalHoldModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#updateLegalHoldForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateLegalHoldBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

        });

        function fetchDocumentsTable() {
            if (DocumentsTable === null) {
                DocumentsTable = $('#DocumentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('hold-files.index', [$hold->Ref]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    dom: '<"row"<"col-12"r><"col-12 w-100 my-3"t><"col-6"i><"col-6"p>>',
                    columnDefs: [
                        //{"className": "text-center", "targets": [3]},
                        {
                            "render": function (data, type, row) {
                                return '<div class="d-flex align-items-center"><img src="' + data + '" alt="file-icon" class="wid-35"><h6 class="mb-0 ms-2 text-truncate">' + row.Visibility + ' ' + row.Name + '</h6> </div></div>';
                            },
                            "targets": 0
                        },
                        /*{
                            "render": function (data, type, row) {
                                return '<div class="d-flex align-items-center"><img src="'+data+'" alt="file-icon" class="wid-35"></div>';
                            },
                            "targets": 1
                        },*/
                        {"visible": false, "targets": [1, 2]}
                    ],
                    columns: [
                        {data: 'Icon', name: 'Icon', searchable: false, orderable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'Visibility', name: 'Visibility'},
                        {data: 'current.Size', name: 'current.Size'},
                        {data: 'Repository', name: 'Repository'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ]
                });

                DocumentsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the list.");
                });
            } else {
                DocumentsTable.ajax.reload();
            }
        }

        function fetchActivitiesTable() {
            /*  if (!$.fn.DataTable.isDataTable('#holdActivitiesTable')) {
                  $('#holdActivitiesTable').DataTable({
                      processing: true,
                      serverSide: true,
                      responsive: true,
                      "order": [[4, 'desc']],
                      columnDefs: [
                          // {"className": "text-center", "targets": [3]},
                          {
                              "render": function (data, type, row) {
                                  return '<p><b>' + row.event + '</b><br/>' + data + '</p>';
                                  //return data + " " + row.OtherNames;
                              },
                              "targets": 2 // the place of col2
                          },
                          {"visible": false, "targets": [0, 1]}
                      ],
                      ajax: {
                          url: '{ { route('file.activities',[$file->DocumentId]) }}',
                          error: function (jqXHR) {
                              codeNotify(jqXHR.status);
                          }
                      },
                      columns: [
                          {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                          {data: 'event', name: 'event'},
                          {data: 'description', name: 'description'},
                          {data: 'causer.Name', name: 'causer.Name'},
                          {data: 'created_at', name: 'created_at'},
                      ], "oLanguage": {
                          "sEmptyTable": "no activities under this filter"
                      }
                  }).on('error', function () {
                      nWarning("an issue occurred while loading activities.");
                  });
              } else {
                  $('#holdActivitiesTable').DataTable().ajax.reload();
              }*/
        }
    </script>
@endsection
