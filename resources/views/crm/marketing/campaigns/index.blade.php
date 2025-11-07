@php use App\Enums\CampaignTypeEnum; @endphp
@extends('layouts.app')

@section('title','Marketing Campaigns')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <div class="float-end">
                        <button class="btn btn-primary float-end ms-2 modal-create-campaign btn-sm" type="button"><i
                                class="fas fa-plus-circle"></i> Add a Campaign
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="campaignTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Label</th>
                            <th>Status</th>
                            <th>Contacts</th>
                            <th>Dated</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="CampaignActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="createCampaignModal">
                        <form action="{{ route('campaigns.store') }}" method="post" id="createCampaignForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Label" name="Label" required
                                       placeholder="Label">
                                <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="MarketingList" class="form-label">Campaign </label>
                                <select class="form-control" name="MarketingList" id="MarketingList" required>
                                    <option selected disabled>Select a List</option>
                                    @foreach($MarketingLists as $MarketingList)
                                        <option value="{{ $MarketingList->slug }}">{{ $MarketingList->Label }}</option>
                                    @endforeach
                                </select>
                                <p id="MarketingList_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="Type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="Type" id="Type" required>
                                    <option selected disabled>select a Type</option>
                                    @foreach(CampaignTypeEnum::cases() as $Type)
                                        <option value="{{ $Type->value }}">{{ $Type->name }}</option>
                                    @endforeach
                                </select>
                                <p id="Type_error" class="invalid-feedback d-none error col-12" role="alert"></p>
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
                                <button class="btn btn-primary float-end" id="createCampaignBtn" type="submit"><i
                                        class="fas fa-save"></i> add a campaign
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

    <script> const $Modal = $('#CampaignActionsModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchCampaignsTable();

            $(document).on('click', '.modal-create-campaign', function () {
                $(".modal-title").html('Add a Campaign');
                $(".modal-item").addClass('d-none');
                $('#createCampaignModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#createCampaignForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createCampaignBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $("#MarketingList").select2({
                dropdownParent: $Modal,
            });

        });

        function fetchCampaignsTable() {
            if (!$.fn.DataTable.isDataTable('#campaignTable')) {
                $('#campaignTable').DataTable({
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
                        {data: 'Status', name: 'Status'},
                        {data: 'contacts_count', name: 'contacts_count'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no campaigns under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading campaigns.");
                });
            } else {
                $('#campaignTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
