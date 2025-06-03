@php use App\Enums\MarketingListEnum;use App\Models\BR\Client;use App\Models\CRM\Lead; @endphp
@php @endphp
@php @endphp
@extends('layouts.app')

@section('title','Marketing Lists')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
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
                <button class="btn btn-primary ms-2 modal-create-static-list" type="button">
                    <i class="fas fa-plus-circle"></i> Add a Static List
                </button>

                <button class="btn btn-primary ms-2 modal-create-dynamic-list" type="button">
                    <i class="fas fa-plus-circle"></i> Add a Dynamic List
                </button>
            </div>
        </div>
        <div class="col-12 mt-3">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="marketingListsTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Label</th>
                            <th>Contacts</th>
                            <th>Source</th>
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
    <div class="modal fade" id="MarketingListActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" id="MarketingListChild">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createStaticListModal">
                        <form action="{{ route('marketing-list.store') }}" method="post" id="createStaticListForm">
                            @csrf
                            <div class="mb-3"><input type="hidden" name="Type"
                                                     value="{{ MarketingListEnum::Static->value  }}">
                                <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Label" name="Label" required
                                       placeholder="Label">
                                <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Party">Party <span class="text-danger">*</span></label>
                                <select class="form-control" name="Party" id="Party" required>
                                    <option value="null">Members & Leads</option>
                                    <option selected value="{{ Client::getPrimaryKey() }}">Members</option>
                                    <option value="{{ Lead::getPrimaryKey() }}">Leads</option>
                                </select>
                                <p id="Party_error" class="invalid-feedback d-none error col-12" role="alert"></p>
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
                                <button class="btn btn-primary float-end" id="createStaticListBtn" type="submit"><i
                                        class="fas fa-save"></i> add static list
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="createDynamicListModal">
                        <form action="{{ route('marketing-list.store') }}" method="post" id="createDynamicListForm">
                            @csrf
                            <div class="mb-3"><input type="hidden" name="Type"
                                                     value="{{ MarketingListEnum::Dynamic->value  }}">
                                <label class="form-label" for="e_Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="e_Label" name="Label" required
                                       placeholder="Label">
                                <p id="e_Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="e_Party">Party <span class="text-danger">*</span></label>
                                <select class="form-control" name="Party" id="e_Party" required>
                                    <option disabled selected>Select a Party</option>
                                    <option value="{{ Client::getPrimaryKey() }}">Members</option>
                                    <option value="{{ Lead::getPrimaryKey() }}">Leads</option>
                                </select>
                                <p id="e_Party_error" class="invalid-feedback d-none error col-12" role="alert"></p>
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
                                <label class="form-label" for="e_Notes">Notes </label>
                                <textarea name="Notes" id="e_Notes" rows="3" class="form-control"
                                          maxlength="1000"></textarea>
                                <p id="e_Notes_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mt-4">
                                <hr>
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="createDynamicListBtn" type="submit"><i
                                        class="fas fa-save"></i> add a dynamic list
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

    <script> const $Modal = $('#MarketingListActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchMarketingListsTable();

            $(document).on('click', '.modal-create-static-list', function () {
                $(".modal-title").html('Add a Static Marketing List');
                $(".modal-item").addClass('d-none');
                $('#createStaticListModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $('form#createStaticListForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createStaticListBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.modal-create-dynamic-list', function () {
                $(".modal-title").html('Create a Dynamic Marketing List');
                $(".modal-item").addClass('d-none');
                $('#createDynamicListModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createDynamicListForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createDynamicListBtn'), true, true, true, true)) {
                    $Modal.modal('hide');
                }
            });

        });

        function fetchMarketingListsTable() {
            if (!$.fn.DataTable.isDataTable('#marketingListsTable')) {
                $('#marketingListsTable').DataTable({
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
                        {data: 'Source', name: 'Source', searchable: false},
                        {data: 'Notes', name: 'Notes'},
                        {data: 'LastContacted', name: 'LastContacted'},
                    ], "oLanguage": {
                        "sEmptyTable": "no Lists found under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading marketing lists.");
                    // console.log(er);
                });
            } else {
                $('#marketingListsTable').DataTable().ajax.reload();
            }
        }

    </script>
@endsection
