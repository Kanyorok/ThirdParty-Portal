@php use App\Enums\Core\VisibilityEnum; @endphp
@extends('dms.layout')

@section('title')
    {{ $type->ValidationTypeId}}
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    <li class="breadcrumb-item"><a href="#">Settings</a></li>
    <li class="breadcrumb-item"><a href="{{ route('document-validation-type.index') }}">Validation Types</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 ">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center h4">{!! $type->Visibility->icon() !!} {{ $type->Name }} </h2>
                    <p class="text-center">{{ $type->ValidationTypeId }}</p>
                    <p class="text-center">Validations : <b>{{ number_format($type->validations_count) }}</b></p>
                    <p class="text-center">{{ $type->Notes }}</p>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            @can('update', $type)
                                <button class="btn btn-primary btn-sm modal-update-validation-type w-100" type="button">
                                    <i
                                        class="fas fa-edit"></i> update
                                </button>
                            @endcan
                        </div>
                        <div class="col-sm-6 col-12">
                            @can('delete', $type)
                                <button class="btn btn-danger btn-sm modal-trash-validation-type w-100" type="button"><i
                                        class="fas fa-trash"></i> delete
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    @include('snippets.behind_scenes',['model'=>$type])
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title ">
                        Approvers
                        <span class="float-end">
                            <button type="button" class="btn btn-primary add-approver-btn btn-sm">
                            <i class="align-middle" data-feather="plus-circle"></i> add approver
                        </button>
                        <button class="btn btn-link float-end btn-sm" type="button" onclick="fetchApproversTable()"><i
                                class="fas fa-refresh"></i></button>
                        </span>
                    </h5>
                </div>
                <div class="card-body pt-1 table-responsive">
                    <table id="typeApproversTable"
                           class="table table-striped no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
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

    <div class="modal fade" id="SignatureActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="updateValidationTypeModal">
                        <form action="{{ route('document-validation-type.update',[$type->ValidationTypeId]) }}"
                              method="post"
                              id="updateValidationTypeForm"> @csrf
                            <div class="mb-3">@method('put')
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required
                                       placeholder="Name" value="{{ $type->Name }}">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3"
                                          class="form-control">{{ $type->Notes }}</textarea>
                                <p id="Notes_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="updateValidationTypeBtn" type="submit"><i
                                        class="fas fa-save"></i>
                                    update {{ \Illuminate\Support\Str::limit($type->ValidationTypeId ,20) }}
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="trashValidationTypeModal">
                        <h4 class="text-danger">
                            Trash Validation Type: <b>{{ $type->Name }}</b> ?
                        </h4>
                        <form id="trashValidationTypeForm" method="post"
                              action="{{ route('document-validation-type.destroy',[$type->ValidationTypeId]) }}"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashValidationTypeBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="addValidationTypeApproverModal">
                        <form action="{{ route('doc-validation-type-approvers.store',[$type->ValidationTypeId]) }}"
                              method="post"
                              id="addValidationTypeApproverForm">
                            @csrf
                            <div class="mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-control " name="role" id="role" required>
                                    <option selected disabled>select a role.</option>
                                    <option
                                        value="{{ \App\Enums\Core\RoleEnum::Admin->value }}">{{ \App\Enums\Core\RoleEnum::Admin->description() }}</option>
                                    <option
                                        value="{{ \App\Enums\Core\RoleEnum::Write->value }}">{{ \App\Enums\Core\RoleEnum::Write->description(['append'=>['w' =>'Validate']]) }}</option>
                                </select>
                                <p id="role_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="approver" class="form-label">User/Team <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="approver" id="approver" required>
                                </select>
                                <p id="approver_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addValidationTypeApproverBtn"
                                        type="submit"><i
                                        class="fas fa-plus"></i> add
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content text-center with-gradient d-none modal-item"
                         id="trashValidationTypeApproverModal">
                        <h3 class="h3 text-danger">Remove <b id="trashValidationTypeApprover"></b> as Validator</h3>
                        <div class="mt-2 mb-2">
                            Are you sure you want to remove this user/team ?
                        </div>
                        <hr>
                        <form id="trashValidationTypeApproverForm" method="post"> @csrf
                            <div class="mt-4">@method('delete')
                                <button type="button" class="btn btn-success float-start"
                                        data-bs-dismiss="modal">
                                    no, keep
                                </button>
                                <button class="btn btn-danger float-end" id="trashValidationTypeApproverBtn"
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
    <script>
        const $Modal = $('#SignatureActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchApproversTable();

            $(document).on('click', '.modal-update-validation-type', function () {
                $(".modal-title").html('Update Validation Type');
                $(".modal-item").addClass('d-none');
                $('#updateValidationTypeModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#updateValidationTypeForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#updateValidationTypeBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-trash-validation-type', function () {
                $(".modal-title").html('Trash Validation Type');
                $(".modal-item").addClass('d-none');
                $('#trashValidationTypeModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#trashValidationTypeForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashValidationTypeBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.add-approver-btn', function () {
                $(".modal-item").addClass('d-none');
                $('#addValidationTypeApproverModal').removeClass('d-none');
                $('.modal-title').html('Add Approver');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addValidationTypeApproverForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addValidationTypeApproverBtn'), false, true, true)) {
                    fetchApproversTable();
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.share-permission-trash', function () {
                const name = $(this).data('info');
                $(".modal-item").addClass('d-none');
                $("#trashValidationTypeApproverForm").attr('action', $(this).data('click_url'));
                $('#trashValidationTypeApprover').html(name);
                $('#trashValidationTypeApproverModal').removeClass('d-none');
                $('.modal-title').html('Remove approver');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#trashValidationTypeApproverForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashValidationTypeApproverBtn'), false, true, true)) {
                    fetchApproversTable();
                    $Modal.modal('hide');
                }
            });

            $('#approver').select2({
                placeholder: "Select  Users / Teams", minimumInputLength: 2,
                dropdownParent: $Modal,
                width: '100%',
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
        });

        function fetchApproversTable() {
            if (!$.fn.DataTable.isDataTable('#typeApproversTable')) {
                $('#typeApproversTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    ajax: {
                        url: '{{ route('doc-validation-type-approvers.index',[$type->ValidationTypeId]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'party', name: 'party'},
                        {data: 'Role', name: 'Role'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no approvers under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading approvers.");
                });
            } else {
                $('#typeApproversTable').DataTable().ajax.reload();
            }
        }

    </script>
@endsection
