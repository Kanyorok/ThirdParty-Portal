@php use App\Enums\Core\VisibilityEnum; @endphp
@php use App\Enums\Core\RoleEnum; @endphp
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
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
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
                        <li class="list-group-item">Visibility : <span class="float-end"> <b>{!! $file->Visibility->icon() !!}
                                &nbsp; {{$file->Visibility->name}}</b> <a href="javascript:void(0)"
                                                                          class="float-end edit-permission-visibility"><i
                                        class="material-icons-two-tone"> edit</i></a></span></li>
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
                        </ul>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="tab-content p-0">
                        <div class="tab-pane m-2" id="tab-activities" role="tabpanel">
                            <table id="fileActivitiesTable"
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
                            <h4 class=" mb-3">Permissions
                                <button class="btn btn-primary btn-sm float-end share-file-btn" type="button"><i
                                        class="fas fa-share"></i> share
                                </button>
                            </h4>
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
    <div class="modal fade" id="fileActionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateFileVisibilityModal">
                        <form action="{{ route('file.visibility',[$file->DocumentId]) }}" method="post"
                              id="updateFileVisibilityForm"> @csrf
                            @method('put')
                            <div class="mb-3">
                                <label class="form-label" for="visibility">Visibility <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="visibility" id="visibility" required>
                                    @foreach(VisibilityEnum::cases() as $Visibility)
                                        <option
                                            value="{{ $Visibility->value }}" {{ ($Visibility->value===$file->Visibility->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                                    @endforeach
                                </select>
                                <p id="visibility_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateFileVisibilityBtn" type="submit">
                                    <i
                                        class="fas fa-save"></i> update Visibility
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="addFilePermissionModal">
                        <form action="{{ route('file-permissions.store',[$file->DocumentId]) }}" method="post"
                              id="addFilePermissionForm">
                            @csrf
                            <div class="mb-3">
                                <label for="share_role" class="form-label">Role <span
                                        class="text-danger">*</span></label>
                                <select class="form-control " name="share_role" id="share_role" required>
                                    <option selected disabled>select a role.</option>
                                    @foreach(RoleEnum::getAll() as $role)
                                        <option value="{{ $role->value }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <p id="share_role_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="share_party" class="form-label">User/Team </label>
                                <select class="form-control" name="share_party" id="share_party" required>
                                </select>
                                <p id="share_party_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-info float-end" id="addFilePermissionBtn" type="submit"><i
                                        class="fas fa-share-alt"></i> share
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content text-center with-gradient d-none modal-item"
                         id="trashFilePermissionModal">
                        <h3 class="h3 text-danger">Remove Permission for <b id="trashFilePermission"></b>
                            from {{ $file->Name }} File.
                        </h3>
                        <div class="mt-2 mb-2">
                            Are you sure you want to remove this share ?
                        </div>
                        <hr>
                        <form id="trashFilePermissionForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="trashFilePermissionBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, remove
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
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const $Modal = $('#fileActionModal');
        $(function () {
            fetchFilePermissionsTableTable()
            fetchFilePreview();


            $('#share_party').select2({
                placeholder: "Search a user or team (t:)", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{!! route('users.select2',['with_teams'=>'rzr.co.ke']) !!}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name, id: item.UserID}
                            })
                        };
                    },
                    cache: true
                }
            });

            $(document).on('click', '.share-permission-trash', function () {
                const name = $(this).data('info');
                $(".modal-item").addClass('d-none');
                $("#trashFilePermissionForm").attr('action', $(this).data('click_url'));
                $('#trashFilePermission').html(name);
                $('#trashFilePermissionModal').removeClass('d-none');
                $('.modal-title').html('<b>Remove</b> share : ' + name);
                $Modal.modal('show');
            });
            $(document).on('click', '.share-file-btn', function () {
                $('#share_party').val(null).change();
                $(".modal-item").addClass('d-none');
                $('#addFilePermissionModal').removeClass('d-none');
                $('.modal-title').html('SHARE: {{ $file->Name }}.');
                $Modal.modal('show');
            });
            $(document).on('click', '.edit-permission-visibility', function () {
                $(".modal-item").addClass('d-none');
                $('#updateFileVisibilityModal').removeClass('d-none');
                $('.modal-title').html('change {{ $file->Name }} visibility.');
                $Modal.modal('show');
            });

            $('form#addFilePermissionForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addFilePermissionBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchFilePermissionsTableTable();
                }
            });

            $('form#trashFilePermissionForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashFilePermissionBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchFilePermissionsTableTable();
                }
            });

            $('form#updateFileVisibilityForm').submit(async function (e) {
                e.preventDefault();
                const response = await saveForm($(this), $('#updateFileVisibilityBtn'), false, true, true, true);
                if (response) {
                    $Modal.modal('hide');
                    window.bsOffcanvas.hide();
                    $('#' + response.data.id).remove();
                    if (typeof appendFiles === "function") {
                        appendFiles(response.data);
                    }
                }
            });

        });

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


        function fetchActivitiesTable() {
            if (!$.fn.DataTable.isDataTable('#fileActivitiesTable')) {
                $('#fileActivitiesTable').DataTable({
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
                        url: '{{ route('file.activities',[$file->DocumentId]) }}',
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
                    // console.log(er);
                });
            } else {
                $('#fileActivitiesTable').DataTable().ajax.reload();
            }
        }

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
