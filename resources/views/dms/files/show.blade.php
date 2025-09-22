@php use App\Enums\Core\VisibilityEnum; @endphp
@php use App\Enums\Core\RoleEnum; @endphp
@extends('dms.layout')

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
    <style>
        .select2-container {
            width: 100% !important;
        }

        .ribbon {
            width: 150px;
            height: 150px;
            overflow: hidden;
            position: absolute;
        }

        .ribbon::before,
        .ribbon::after {
            position: absolute;
            z-index: -1;
            content: '';
            display: block;
            border: 5px solid #2980b9;
        }

        .ribbon-info span {
            background-color: rgba(var(--bs-info-rgb));
            color: #fff;
        }

        .ribbon-danger span {
            background-color: rgba(var(--bs-danger-rgb));
            color: #fff;
        }


        .ribbon span {
            position: absolute;
            display: block;
            width: 225px;
            padding: 15px 0;
            box-shadow: 0 5px 10px rgba(0, 0, 0, .1);
            font: 700 18px/1 'Lato', sans-serif;
            text-shadow: 0 1px 1px rgba(0, 0, 0, .2);
            text-transform: uppercase;
            text-align: center;
        }

        /* top left*/
        .ribbon-top-left {
            top: -10px;
            left: -10px;
        }

        .ribbon-top-left::before,
        .ribbon-top-left::after {
            border-top-color: transparent;
            border-left-color: transparent;
        }

        .ribbon-top-left::before {
            top: 0;
            right: 0;
        }

        .ribbon-top-left::after {
            bottom: 0;
            left: 0;
        }

        .ribbon-top-left span {
            right: -25px;
            top: 30px;
            transform: rotate(-45deg);
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item ">Name: <span class="float-end"><b>{{ $file->Name }}</b>
                            <a href="javascript:void(0)" class="float-end edit-file-name">
                                    <i class="material-icons-two-tone"> edit</i></a>
                            </span></li>
                        <li class="list-group-item">Type : <b class="float-end">{!! $file->ext()->getIcon() !!} &nbsp;
                                {{$file->ext()->name}}</b></li>
                        <li class="list-group-item">Visibility : <span class="float-end"> <b>{!! $file->Visibility->icon() !!}
                                &nbsp; {{$file->Visibility->name}}</b>
                                <a href="javascript:void(0)" class="float-end edit-permission-visibility">
                                    <i class="material-icons-two-tone"> edit</i></a></span></li>
                        <li class="list-group-item">Versions : <b
                                class="float-end">{{ number_format($file->versions_count) }}</b></li>
                        <li class="list-group-item">Repository : <b
                                class="float-end">{{ $file->repository->Name }}</b>
                        </li>
                        <li class="list-group-item">Size : <b
                                class="float-end">{{  \Illuminate\Support\Number::fileSize( $file->current->Size, 2) }}</b>
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <div class="row">
                        @if(!$file->ext()->canCheckOut())
                            <div class="col-sm-6 col-12">
                                <button class="btn btn-secondary w-100 action-download-file" type="button"><i
                                        class="fas fa-download"></i> Download
                                </button>
                            </div>
                        @endif
                        @can('delete', $file)
                            <div class="col-sm-6 col-12">
                                <button class="btn btn-secondary w-100 file-action-trash" type="button"><i
                                        class="fas fa-trash"></i> delete
                                </button>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header py-3">
                    <div class="card-actions float-end">
                        <a href="javascript:void(0)" class="float-end click-summary-data"
                           data-summary_title='<i class="fas fa-tags"></i> Update File Tags'
                           data-click_url='{{ route('document-tags.create',[$file->DocumentId]) }}'
                        >
                            <i class="material-icons-two-tone"> edit</i></a>
                    </div>
                    <h5>File Tags </h5>
                </div>
                <div class="card-body">
                    @foreach($tags as $tag)
                        @if ($tag->Visibility->value === VisibilityEnum::Private->value)
                            <span class="badge rounded-pill text-bg-primary">{{ $tag->Name }}</span>
                        @else
                            <span class="badge rounded-pill text-bg-danger">{{ $tag->Name }}</span>
                        @endif
                    @endforeach
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
                @if($legalHold)
                    <div class="ribbon ribbon-top-left ribbon-danger"><span>Legal Hold</span></div>
                @elseif($checkedOut)
                    <div class="ribbon ribbon-top-left ribbon-info"><span>Checked Out</span></div>
                @endif
                <div class="card-body" style="min-height: 100px" id="FilePreviewPage">
                    <p class="text-center m-5"><i class="fas fa-spinner fa-spin fa-5x"></i><br>loading preview</p>
                </div>
            </div>
            <div class="card">
                    <div class="nav nav-pills card-header py-2">
                        <ul class="nav" role="tablist">
                            @if($file->ext()->canCheckOut())
                                <li class="nav-item"><a class="nav-link active" href="#tab-checkouts"
                                                        data-bs-toggle="tab"
                                                        role="tab" aria-selected="false" onclick="fetchCheckOutsTable()"
                                    >Check Out & In</a></li>
                            @endif
                            <li class="nav-item"><a class="nav-link {{ $file->ext()->canCheckOut()?'':'active' }}"
                                                    href="#tab-usersAndTeams"
                                                    data-bs-toggle="tab" role="tab" aria-selected="false"
                                                    onclick="fetchFilePermissionsTableTable()">
                                    Permissions & Sharing
                                </a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-activities" data-bs-toggle="tab"
                                                    role="tab" aria-selected="false" onclick="fetchActivitiesTable()"
                                >Activities</a></li>
                        </ul>
                    </div>
                </div>

            <div class="tab-content p-0">
                <div class="tab-pane m-2 {{ $file->ext()->canCheckOut()?'':'active show' }}" id="tab-usersAndTeams"
                     role="tabpanel">
                    <div class="card">
                        <div class="card-header py-3">
                            <h4>Permissions
                                <button class="btn btn-primary btn-sm float-end share-file-btn" type="button"><i
                                        class="fas fa-share"></i> share
                                </button>
                            </h4>
                        </div>
                        <div class="card-body">
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
                @if($file->ext()->canCheckOut())
                    <div class="tab-pane m-2 active show" id="tab-checkouts" role="tabpanel">
                        <div class="card">
                            <div class="card-header py-3">
                                <h4>Checkin & Checkout
                                    @if($checkedOut && $checkIn)
                                        <button class="btn btn-secondary btn-sm float-end action-checkin-file"
                                                type="button">
                                            <i class="fas fa-file-upload"></i> check in
                                        </button>
                                    @else
                                        <button class="btn btn-primary btn-sm float-end action-checkout-file"
                                                type="button">
                                            <i class="fas fa-file-download"></i> check out
                                        </button>
                                    @endif
                                </h4>
                            </div>
                            <div class="card-body">
                                <table id="fileCheckOutsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>By</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                        <th>Dated</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="tab-pane m-2" id="tab-activities" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
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
                    @if($file->ext()->canCheckOut())
                        <div class="onboarding-content with-gradient d-none modal-item" id="fileCheckOutModal">
                            <form action="{{ route('document-checkouts.store',[$file->DocumentId]) }}" method="post"
                                  id="fileCheckOutForm"> @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="CheckOutRemark">CheckOut Remark </label>
                                    <textarea name="CheckOutRemark" id="CheckOutRemark" rows="3" class="form-control"
                                              maxlength="500"></textarea>
                                    <p id="CheckOutRemark_error" class="invalid-feedback d-none error" role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="fileCheckOutBtn" type="submit">
                                        <i class="fas fa-file-download"></i> checkout
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item" id="fileCheckInModal">
                            <div class="progress mb-3 document-change d-none">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                     role="progressbar" id="CheckIn-progress-bar" style="width: 0" aria-valuenow="0"
                                     aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                        Complete</small></div>
                            </div>
                            <form action="{{ route('document-checkouts.update',[$file->DocumentId, 'restore']) }}"
                                  method="post"
                                  id="fileCheckInForm" enctype="multipart/form-data"> @csrf
                                <div class="mb-3">@method('PUT')
                                    <label class="form-label" for="CheckInDocument">Document </label>
                                    <input type="file" name="CheckInDocument" class="form-control"
                                           accept="{{ $file->ext()->getMimeType() }}" id="CheckInDocument">
                                    <p id="CheckInDocument_error" class="invalid-feedback d-none error"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="CheckInRemark">CheckIn Remark </label>
                                    <textarea name="CheckInRemark" id="CheckInRemark" rows="3" class="form-control"
                                              maxlength="500"></textarea>
                                    <p id="CheckInRemark_error" class="invalid-feedback d-none error" role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="fileCheckInBtn" type="submit">
                                        <i class="fas fa-file-upload"></i> check in
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content  with-gradient d-none modal-item" id="cancelCheckOutModal">
                            <h3 class="h3 text-danger text-center">
                                Cancel checkout for document {{ $file->Name }}
                            </h3>
                            <div class="mt-2 mb-2 text-center">
                                Are you sure you want to cancel this checkout ?
                            </div>

                            <form id="cancelCheckOutForm" method="post"> @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="CheckOutCancelReason">Cancel Reason <span
                                            class="text-danger">*</span></label>
                                    <textarea name="CheckOutCancelReason" id="CheckOutCancelReason" rows="3"
                                              class="form-control" maxlength="500"></textarea>
                                    <p id="CheckOutCancelReason_error" class="invalid-feedback d-none error"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">@method('delete')
                                    <button type="button" class="btn btn-success float-start"
                                            data-bs-dismiss="modal">
                                        no, keep
                                    </button>
                                    <button class="btn btn-danger float-end" id="cancelCheckOutBtn"
                                            type="submit"><i
                                            class="fas fa-trash"></i> yes, cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="onboarding-content with-gradient d-none modal-item" id="fileDownloadModal">
                            <form action="{{ route('file-download.store',[$file->DocumentId]) }}" method="post"
                                  id="fileDownloadForm"> @csrf
                                <input type="hidden" name="fetch_link" value="{{ $file->Name }}" class="d-none">
                                <h3 class="text-center">Download the file {{ $file->Name }}</h3>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="fileDownloadBtn" type="submit">
                                        <i class="fas fa-file-download"></i> download
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                    <div class="onboarding-content with-gradient d-none modal-item text-center" id="trashFileModal">
                        <h4 class="text-danger">
                            Trash Document <b class="rm-file-name">{{ $file->Name }}</b> ?
                        </h4>
                        <div class="alert alert-warning" role="alert">
                            <b>Note</b>This file will be deleted permanently
                        </div>
                        @if($checkedOut)
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i data-feather="alert-triangle"></i>
                                <div>This document has been checkout, cannot be deleted</div>
                            </div>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-end"
                                        data-bs-dismiss="modal">
                                    close
                                </button>
                            </div>
                        @else
                            <form id="trashFileForm" method="post"
                                  action="{{  route('files.destroy', [$file->repository->RepositoryId, $file->DocumentId]), }}"> @csrf
                                <div class="mt-4">@method('delete')
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        no, cancel
                                    </button>

                                    <button class="btn btn-danger float-end" id="trashFileBtn"
                                            type="submit"><i
                                            class="fas fa-trash"></i> yes, delete
                                    </button>

                                </div>
                            </form>
                        @endif

                    </div>
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
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateFileNameModal">
                        <form action="{{ route('files.update',[$file->repository->RepositoryId,$file->DocumentId]) }}"
                              method="post"
                              id="updateFileNameForm"> @csrf
                            @method('put')
                            <div class="mb-3">
                                <label class="form-label" for="Name">File Name</label>
                                <input type="text" class="form-control" id="Name" placeholder="Name"
                                       required name="Name" minlength="2"
                                       value="{{  pathinfo($file->Name, PATHINFO_FILENAME) }}">
                                <p id="Name_error" class="invalid-feedback d-none error" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateFileNameBtn" type="submit">
                                    <i
                                        class="fas fa-save"></i> rename
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
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const $Modal = $('#fileActionModal');
        $(function () {
            fetchFilePreview();
            @if($file->ext()->canCheckOut())
            fetchCheckOutsTable();
            @else
            fetchFilePermissionsTableTable();
            @endif
            $(document).on('click', '.edit-file-name', function () {
                $(".modal-item").addClass('d-none');
                $('#updateFileNameModal').removeClass('d-none');
                $('.modal-title').html('rename {{ $file->Name }}.');
                $Modal.modal('show');
            });
            $('form#updateFileNameForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateFileNameBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.file-action-trash', function () {
                $(".modal-title").html('<b class="text-danger">Delete</b>  : ' + $(this).data('title'));
                $(".modal-item").addClass('d-none');
                $('#trashFileModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#trashFileForm').submit(async function (e) {
                e.preventDefault();
                const response = await saveForm($(this), $('#trashFileBtn'), true, true, true);
                if (response) {
                    $Modal.modal('hide');
                    $('#' + response.data.id).remove();
                }
            });

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

            $(document).on('click', '.action-download-file', function () {
                $(".modal-item").addClass('d-none');
                $('#fileDownloadModal').removeClass('d-none');
                $('.modal-title').html('Download : {{ $file->Name }}.');
                $Modal.modal('show');
            });
            $('form#fileDownloadForm').submit(async function (e) {
                e.preventDefault();
                const data = await saveForm($(this), $('#fileDownloadBtn'), false, true, true);
                if (data) {
                    $Modal.modal('hide');
                    window.open(data.route, '_blank', 'noopener,noreferrer');
                }
            });

            $(document).on('click', '.action-checkin-file', function () {
                $(".modal-item").addClass('d-none');
                $('#fileCheckInModal').removeClass('d-none');
                $('.modal-title').html('Check In : {{ $file->Name }}.');
                $Modal.modal('show');
            });
            $(document).on('click', '.action-checkout-file', function () {
                $(".modal-item").addClass('d-none');
                $('#fileCheckOutModal').removeClass('d-none');
                $('.modal-title').html('Checkout : {{ $file->Name }}.');
                $Modal.modal('show');
            });
            $('form#fileCheckOutForm').submit(async function (e) {
                e.preventDefault();
                const data = await saveForm($(this), $('#fileCheckOutBtn'), false, true, true);
                if (data) {
                    $Modal.modal('hide');
                    window.open(data.route, '_blank', 'noopener,noreferrer');
                    fetchCheckOutsTable();
                }
            });

            $(document).on('click', '.action-checkout-cancel', function () {
                $(".modal-item").addClass('d-none');
                $('#cancelCheckOutModal').removeClass('d-none');
                $("#cancelCheckOutForm").attr('action', $(this).data('action'));
                $('.modal-title').html('<b class="text-danger">CANCEL</b> Document Checkout : {{ $file->Name }}.');
                $Modal.modal('show');
            });
            $('form#cancelCheckOutForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#cancelCheckOutBtn'), true, true, true)) {
                    $Modal.modal('hide');

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

            $("#Upload_image").change(function () {
                $('.document-change').removeClass('d-none');
            });
            $('form#fileCheckInForm').submit(function (e) {
                e.preventDefault();
                const btn = $("#fileCheckInBtn")
                btn.prop('disabled', true).addClass('disabled');
                $(this).ajaxSubmit({
                    dataType: 'json', beforeSubmit: function () {
                        $("#CheckIn-progress-bar").width('0%');
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        $("#CheckIn-progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                    },
                    success: function (data) {
                        window.setTimeout(function () {
                            window.location.replace(data.route);
                        }, 2000)
                        btn.prop('disabled', false).removeClass('disabled');
                        $('.document-change').addClass('d-none');
                        nSuccess(data.message);
                        $("#CheckIn-progress-bar").width('0%').html('0');
                        $Modal.modal('hide');
                    },
                    error: function (request) {
                        formRequest(request, true)
                    }, resetForm: true
                });
            });
        });

        function fetchFilePreview() {
            const previewContainer = document.getElementById('FilePreviewPage');
            $.ajax({
                url: "{{ route('file.preview', [$file->DocumentId]) }}",
                method: "GET",
                success: function (html) {
                    previewContainer.innerHTML = html;
                },
                error: function (jqXHR) {
                    previewContainer.innerHTML = `
            <div class="text-center m-5">
                <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                <p class="mt-2">Error loading preview: ${jqXHR.statusText}</p>
            </div>`;
                }
            });
        }

        function fetchCheckOutsTable() {
            if (!$.fn.DataTable.isDataTable('#fileCheckOutsTable')) {
                $('#fileCheckOutsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    ajax: {
                        url: '{{ route('document-checkouts.index',[$file->DocumentId]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'creator', name: 'creator.Name'},
                        {data: 'CheckOutRemark', name: 'CheckOutRemark'},
                        {data: 'Status', name: 'Status'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no checkouts available under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading checkouts.");
                });
            } else {
                $('#fileCheckOutsTable').DataTable().ajax.reload();
            }
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
