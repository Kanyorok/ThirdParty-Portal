@extends('layouts.app')

@section('title','Marketing Planner')
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
                        @if($isMarketingManager)
                            <button class="btn btn-primary float-end ms-2 modal-create-marketing-planner btn-sm"
                                    type="button"><i
                                    class="fas fa-plus-circle"></i> Create a global Planner
                            </button>
                        @else
                            <button class="btn btn-primary float-end ms-2 modal-create-marketing-planner btn-sm"
                                    type="button"><i
                                    class="fas fa-plus-circle"></i> Create a new Planner
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table id="campaignTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Mode</th>
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
    <div class="modal fade" id="PlannerActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($isMarketingManager)
                        <div class="onboarding-content with-gradient d-none modal-item" id="createPlannerModal">
                            <form action="{{ route('master-planner.store') }}" method="post" id="createPlannerForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="Name" name="Name" required
                                           placeholder="Name">
                                    <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="mb-3 ">
                                    <label for="plans" class="form-label">Marketing Plans<span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="plans[]" id="plans" required multiple>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->PlannerID }}">{{ $plan->Name }}
                                                - {{ $plan->branch?->BranchName }} Branch
                                            </option>
                                        @endforeach
                                    </select>
                                    <p id="plans_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
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
                                    <button class="btn btn-primary float-end" id="createPlannerBtn" type="submit"><i
                                            class="fas fa-save"></i> create a Planner
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                    <div class="onboarding-content with-gradient d-none modal-item" id="createPlannerModal">
                        <form action="{{ route('marketing-planner.store') }}" method="post" id="createPlannerForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="Name" name="Name" required
                                       placeholder="Name">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="Branch" class="form-label">Branch <span class="text-danger">*</span> <small
                                        class="text-muted">Only Branches with Manager or/& Operation</small></label>
                                <select class="form-control" name="Branch" id="Branch" required>
                                    <option selected disabled>select an branch</option>
                                    @foreach($Branches as $Branch)
                                        <option
                                            value="{{ $Branch->BranchID }}">{{ $Branch->Name }}</option>
                                    @endforeach
                                </select>
                                <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3 ">
                                <label for="Mode" class="form-label">Marketing Mode<span
                                        class="text-danger">*</span></label>
                                <select class="form-control" name="Mode" id="Mode" required>
                                    <option selected disabled>select an category</option>
                                    @foreach($MarketingModes as $Mode)
                                        <option value="{{ $Mode->ID }}">{{ $Mode->Description }}</option>
                                    @endforeach
                                </select>
                                <p id="Mode_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
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
                                <button class="btn btn-primary float-end" id="createPlannerBtn" type="submit"><i
                                        class="fas fa-save"></i> create a Planner
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const $Modal = $('#PlannerActionsModal');
        let campaignTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchPlannerTable();

            @if($isMarketingManager)
            $(document).on('click', '.modal-create-marketing-planner', function () {
                $(".modal-title").html('Create a Global Planner');
                $(".modal-item").addClass('d-none');
                $('#createPlannerModal').removeClass('d-none');
                $Modal.modal('show');
            });
            $("#plans").select2({
                dropdownParent: $Modal,
            });
            @else
            $(document).on('click', '.modal-create-marketing-planner', function () {
                $(".modal-title").html('Add a Planner');
                $(".modal-item").addClass('d-none');
                $('#createPlannerModal').removeClass('d-none');
                $Modal.modal('show');
            });
            @endif

            $('form#createPlannerForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#createPlannerBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

        });

        function fetchPlannerTable() {
            if (campaignTable === null) {
                campaignTable = $('#campaignTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [3]}
                    ],
                    ajax: {
                        url: document.url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'PlannerID', name: 'PlannerID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'Status', name: 'Status'},
                        {data: 'mode.Description', name: 'mode.Description'},
                        {data: 'ModifiedOn', name: 'ModifiedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "No marketing plans under filter."
                    }
                });

                campaignTable.on('error', function (er) {
                    nWarning("an issue occurred while loading Planner.");
                    console.log(er);
                });
            } else {
                campaignTable.ajax.reload();
            }
        }

    </script>
@endsection
