@extends('layouts.app')

@section('title','Loans Lists')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12 col-md-3">
            <h1 class="h3 d-inline align-middle">@yield('title')</h1>
        </div>
        <div class="col-12 col-md-9 ">
            <div class="float-end">
                @can('debt',\App\Models\CRM\MarketingList::class)
                    <button class="btn btn-secondary  ms-2 modal-create-loans-list" type="button">
                        <i class="fas fa-plus-circle"></i> Add a Loans List
                    </button>
                @endcan
            </div>
        </div>
        <div class="col-12 mt-3">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="loansListsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Label</th>
                            <th>Loans</th>
                            <th>Description</th>
                            <th>Last Contact</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="LoansListActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" id="LoansListChild">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createLoanListModal">
                        <form action="{{ route('loans-list.store') }}" method="post" id="createLoanListForm">
                            @csrf
                            <div class="mb-3"><input type="hidden" name="Source"
                                                     value="{{ \App\Models\BR\DebtProduct::getPrimaryKey()  }}">
                                <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Label" name="Label" required
                                       placeholder="Label">
                                <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Visibility">Visibility <span class="text-danger">*</span></label>
                                <select class="form-control" name="Visibility" id="Visibility" required>
                                    @foreach(\App\Enums\Core\VisibilityEnum::cases() as $Visibility)
                                        <option
                                            value="{{ $Visibility->value }}" {{ ($Visibility->value===\App\Enums\Core\VisibilityEnum::Private->value)?'selected':'' }}>{!! $Visibility->icon() !!} {{ $Visibility->description() }}</option>
                                    @endforeach
                                </select>
                                <p id="Party_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control"
                                          maxlength="1000"></textarea>
                                <p id="Notes_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createLoanListBtn" type="submit"><i
                                        class="fas fa-save"></i> add loans list
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
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script> const $Modal = $('#LoansListActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchLoansListsTable();

            $(document).on('click', '.modal-create-loans-list', function () {
                $(".modal-title").html('Add a New Loans List');
                $(".modal-item").addClass('d-none');
                $('#createLoanListModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createLoanListForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createLoanListBtn   '), true, true, true)) {
                    $Modal.modal('hide');
                }
            });


        });

        function fetchLoansListsTable() {
            if (!$.fn.DataTable.isDataTable('#loansListsTable')) {
                $('#loansListsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // "order": [[3, 'asc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: getDocumentUrl(),
                        error: function (request) {
                            if (request.status === 400 && request.responseJSON.message) {
                                nWarning(request.responseJSON.message);
                            } else {
                                codeNotify(request.status);
                            }
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Label', name: 'Label'},
                        {data: 'parties_count', name: 'parties_count', searchable: false},
                        {data: 'Notes', name: 'Notes'},
                        {data: 'LastContacted', name: 'LastContacted'},
                    ], "oLanguage": {
                        "sEmptyTable": "no Lists found under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading loans lists.");
                });
            } else {
                $('#loansListsTable').DataTable().ajax.reload();
            }
        }

    </script>
@endsection
