@extends('dms.layout')

@section('title','Document Validation Types')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    <li class="breadcrumb-item"><a href="#">Settings</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="float-end">
                        {{--@can(PermissionEnum::ProductDevelopmentWrite->value, ProductDevelopment::class)--}}
                        <button class="btn btn-primary float-end ms-2 modal-create-validation-type btn-sm"
                                type="button">
                            <i class="fas fa-plus-circle"></i> Add a New Type
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="documentValidationTypesTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Validations</th>
                            <th style="max-width: 25vw;">Notes</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DocumentValidationTypeActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item"
                         id="createDocumentValidationTypeModal">
                        <form method="post" id="createDocumentValidationTypeForm"
                              action="{{ route('document-validation-type.store') }}"> @csrf
                            <div class="mb-3">
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required
                                       placeholder="Name">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="Approvers" class="form-label">Validators <span class="text-danger">*</span></label>
                                <select class="form-control" name="Approvers[]" id="Approvers" multiple required>
                                </select>
                                <p id="Approvers_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control"></textarea>
                                <p id="Notes_end_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createDocumentValidationTypeBtn"
                                        type="submit"><i
                                        class="fas fa-save"></i> add
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
        let documentValidationTypesTable = null;
        const $Modal = $('#DocumentValidationTypeActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchDocumentValidationTypesTable();

            $(document).on('click', '.modal-create-validation-type', function () {
                $(".modal-title").html('Add a new Validation Type');
                $(".modal-item").addClass('d-none');
                $('#createDocumentValidationTypeModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createDocumentValidationTypeForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createDocumentValidationTypeBtn'), false, true, true)) {
                    fetchDocumentValidationTypesTable();
                    $Modal.modal('hide');
                }
            });

            $('#Approvers').select2({
                placeholder: "Select  Users / Teams", minimumInputLength: 2,
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
        });

        function fetchDocumentValidationTypesTable() {
            if (documentValidationTypesTable === null) {
                documentValidationTypesTable = $('#documentValidationTypesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    /*"order": [[5, 'desc']],*/
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
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'validations_count', name: 'validations_count', searchable: false},
                        {data: 'Notes', name: 'Notes'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no validations types found !"
                    }
                });

                documentValidationTypesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading document validation.");
                });
            } else {
                documentValidationTypesTable.ajax.reload();
            }
        }
    </script>
@endsection
