@php use Illuminate\Support\Str; @endphp
@extends('layouts.app')

@section('title')
    {{ Str::upper($planner->PlannerID) }}
@endsection
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
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center"><a href="{{ route('marketing-planner.show',$planner->PlannerID) }}"
                                               class="text-black text-decoration-underline"> {{ Str::upper($planner->PlannerID) }}</a>
                    </h2>
                    <p class="text-center">{{ $planner->Name }}</p>
                    <p class="text-center fw-bold">{{ $planner->Status->name }}</p>

                    {{-- <hr>
                     @include('snippets.behind_scenes',['model'=>$planner])--}}

                    <hr>
                    <p class="text-justify">{!! $planner->Notes !!}</p>

                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchActivitiesTable()">Activities</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-1" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Planner details</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab"
                                            aria-selected="false">Actions</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane m-2 active show" id="tab-0" role="tabpanel">
                        <div class="row mb-0">
                            <div class="col-8 mb-0">
                                <h3 class="mb-1 mt-2">
                                    Planed Activities
                                </h3>
                            </div>
                            <div class="col-4 mb-0">
                                <button class="float-end btn btn-primary add-marketing-activity" type="button">
                                    <i class="fas fa-plus-circle"></i> add activity
                                </button>
                            </div>
                        </div>
                        <hr class="mt-0 mb-2">
                        <table id="planActivityTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Budget</th>
                                <th>actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane m-2" id="tab-1" role="tabpanel">
                        <form action="{{ route('marketing-planner.update',[$planner->PlannerID]) }}" method="post"
                              id="updatePlannerForm">
                            @csrf
                            <div class="mb-3">@method('put')
                                <label class="form-label" for="Name">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control planner-form" disabled id="Name" name="Name"
                                       required
                                       placeholder="Name" value="{{ $planner->Name }}">
                                <p id="Name_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label for="Branch" class="form-label">Branch <span class="text-danger">*</span> <small
                                        class="text-muted">Only Branches with Manager</small></label>
                                <select class="form-control planner-form" disabled name="Branch" id="Branch" required>
                                    @foreach($Branches as $Branch)
                                        <option value="{{ $Branch->BranchID }}"
                                            {{ (trim($planner->BranchId) === $Branch->BranchID)?'selected':'' }}
                                        >{{ $Branch->Name }}</option>
                                    @endforeach
                                </select>
                                <p id="Branch_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <div class="mb-3 ">
                                <label for="Mode" class="form-label">Marketing Mode<span
                                        class="text-danger">*</span></label>
                                <select class="form-control planner-form" disabled name="Mode" id="Mode" required>
                                    @foreach($MarketingModes as $Mode)
                                        <option value="{{ $Mode->ID }}"
                                            {{ ($planner->Mode === $Mode->ID)?'selected':'' }}
                                        >{{ $Mode->Description }}</option>
                                    @endforeach
                                </select>
                                <p id="Mode_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="Notes">Notes </label>
                                <textarea name="Notes" id="Notes" rows="3" class="form-control planner-form" disabled
                                          maxlength="1000">{{ $planner->Notes }}</textarea>
                                <p id="Notes_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <button type="button" class="btn btn-secondary d-none float-start"
                                            id="plannerCancelBtn">
                                        cancel
                                    </button>
                                    <button type="button" class="btn btn-primary float-start" id="plannerEditBtn">
                                        edit {{ Str::upper($planner->PlannerID) }} plan
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-primary d-none float-end" id="updatePlannerBtn"
                                            type="submit"><i
                                            class="fas fa-save"></i> save Plan Changes
                                    </button>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                    <div class="tab-pane m-2" id="tab-2" role="tabpanel">
                        <div class="my-3 text-center">
                            <h2 class="text-success">Submit for approval</h2>
                            <p>Once submitted, you will not be able to cancel or update.</p>

                            <button type="button" class="btn btn-success planner-submit" id="submitApprovalBtn">
                                <i class="fas fa-plane-departure"></i> submit for approval
                            </button>
                        </div>
                        <hr>
                        <div class="my-3 text-center">
                            <h2 class="text-danger">Cancel Plan</h2>
                            <p>Once canceled, this plan can only be recovered via a db intervention, kindly be
                                careful</p>
                            <button type="button" class="btn btn-danger trash-planner"><i class="fas fa-trash"></i>
                                cancel planner
                            </button>
                        </div>
                    </div>
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
                    <div class="onboarding-content with-gradient d-none modal-item" id="addActivityPlannerModal">
                        <form action="{{ route('planner-activities.store',[$planner->PlannerID]) }}" method="post"
                              class="row"
                              id="addActivityPlannerForm"> @csrf
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="activity_name">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="activity_name" name="activity_name"
                                       placeholder="Name">
                                <p id="activity_name_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="activity_budget">Budget <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="activity_budget" name="activity_budget"
                                       placeholder="Budget">
                                <p id="activity_budget_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="activity_location">Location <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="activity_location" name="activity_location"
                                       placeholder="Location">
                                <p id="activity_location_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label for="activity_users" class="form-label">User(s)</label>
                                <select class="form-control " name="activity_users[]" id="activity_users" multiple
                                        required>
                                </select>
                                <p id="activity_users_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="activity_start">Start <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime" id="activity_start"
                                       name="activity_start" placeholder="Select start.">
                                <p id="activity_start_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="activity_end">End <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime" readonly id="activity_end"
                                       name="activity_end" placeholder="Select end.">
                                <p id="activity_end_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="activity_materials">Materials </label>
                                <textarea name="activity_materials" id="activity_materials" rows="3"
                                          class="form-control"
                                          minlength="2"></textarea>
                                <p id="activity_materials_end_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3 col-md-6 col-12">
                                <label class="form-label" for="activity_notes">Notes </label>
                                <textarea name="activity_notes" id="activity_notes" rows="3" class="form-control"
                                          minlength="2"></textarea>
                                <p id="activity_notes_end_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class=" ol-12 mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addActivityPlannerBtn" type="submit">
                                    <i class="fas fa-save"></i>add activity
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="trashActivityPlannerModal">
                        <h4 class="text-danger">
                            Trash Marketing Planner Activity <b id="trashActivityPlanner"></b> ?
                        </h4>
                        <p class="text-muted">This action is non reversible, are you sure ?</p>
                        <form id="trashActivityPlannerForm" method="post"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashActivityPlannerBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center" id="trashPlannerModal">
                        <h4 class="text-danger">
                            Trash Marketing Planner <b>{{ $planner->Name }}</b> ({{ Str::upper($planner->PlannerID) }})?
                        </h4>
                        <p class="text-muted">This action is non reversible, are you sure ?</p>
                        <form id="trashPlannerForm" method="post"
                              action="{{ route('marketing-planner.destroy',[$planner->PlannerID]) }}"> @csrf @method('delete')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-danger float-end" id="trashPlannerBtn"
                                        type="submit"><i
                                        class="fas fa-trash"></i> yes, trash
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item text-center"
                         id="submitPlannerModal">
                        <h4 class="text-success">
                            Submit Marketing Plan <b>{{ $planner->Name }}</b> ({{ Str::upper($planner->PlannerID) }})
                            for Approval?
                        </h4>
                        <p class="text-muted">This action is non reversible, are you sure ?</p>
                        <form id="submitPlannerForm" method="post"
                              action="{{ route('marketing-planner.submit',[$planner->PlannerID]) }}"> @csrf @method('put')
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    no, cancel
                                </button>
                                <button class="btn btn-success float-end" id="submitPlannerBtn"
                                        type="submit"><i
                                        class="fas fa-check"></i> yes, submit
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
    <script src='{{ asset('assets/plugins/moment/moment-with-locales.js') }}'></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script src="{{ asset('assets/plugins/rangePlugin.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>   const EditPlannerBtn = $("#plannerEditBtn"), CancelPlannerBtn = $("#plannerCancelBtn"),
            UpdatePlannerBtn = $("#updatePlannerBtn"), $Modal = $('#PlannerActionsModal')
        let planActivityTable = null;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchActivitiesTable();
            $('#activity_users').select2({
                placeholder: "Choose users ...", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{{route('users.select2')}}',
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

            $(document).on('click', '.planner-submit', function () {
                $(".modal-title").html('Submit plan : {{ $planner->PlannerID }}');
                $(".modal-item").addClass('d-none');
                $('#submitPlannerModal').removeClass('d-none');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#submitPlannerForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#submitPlannerBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.add-marketing-activity', function () {
                $(".modal-title").html('Add Marketing Activity to plan : {{ $planner->PlannerID }}');
                $(".modal-item").addClass('d-none');
                $('#addActivityPlannerModal').removeClass('d-none');
                $('#activity_users').val([]).change();
                $Modal.children().first().addClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addActivityPlannerForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addActivityPlannerBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchActivitiesTable();
                }
            });

            EditPlannerBtn.on('click', function () {
                CancelPlannerBtn.removeClass('d-none');
                UpdatePlannerBtn.removeClass('d-none');
                EditPlannerBtn.addClass('d-none');
                $('.planner-form').prop('disabled', false);
            });

            CancelPlannerBtn.on('click', function () {
                cancel();
                $('form#updatePlannerForm').trigger("reset");
            });

            $('form#updatePlannerForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), UpdatePlannerBtn, true, false, true)) {
                    cancel();
                }
            });

            $(document).on('click', '.trash-planner', function () {
                $(".modal-item").addClass('d-none');
                $('#trashPlannerModal').removeClass('d-none');
                $('.modal-title').html('<b class="text-danger">Delete</b>  Plan {{ $planner->Name }}');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#trashPlannerForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashPlannerBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.trash-planner-activity', function () {
                $(".modal-item").addClass('d-none');
                $('#trashActivityPlannerModal').removeClass('d-none');
                const stuff = $(this).data('info').split('~');
                $('.modal-title').html('<b class="text-danger">Delete</b>  ' + stuff[1]);
                $("#trashActivityPlanner").html(stuff[1]);
                $('#trashActivityPlannerForm').attr('action', stuff[0]);
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#trashActivityPlannerForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashActivityPlannerBtn'), false, true, true, true)) {
                    $Modal.modal('hide');
                    fetchActivitiesTable();
                }
            });

            flatpickr("#activity_start", {
                minDate: moment().add(10, 'm').format('YYYY-MM-DD hh:mm'),
                mode: 'range',
                dateFormat: "Y-m-d",
                allowInput: true,
                "plugins": [new rangePlugin({input: "#activity_end"})]
            });
        });

        function fetchActivitiesTable() {
            if (planActivityTable === null) {
                planActivityTable = $('#planActivityTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'asc']],
                    /*"columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],*/
                    ajax: {
                        url: '{{ route('planner-activities.index',[ $planner->PlannerID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Name', name: 'Name'},
                        {data: 'Location', name: 'Location'},
                        {data: 'StartOn', name: 'StartOn'},
                        {data: 'EndOn', name: 'EndOn'},
                        {data: 'Budget', name: 'Budget'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no activities under this filter"
                    }
                });

                planActivityTable.on('error', function (er) {
                    nWarning("an issue occurred while loading activities.");
                    console.log(er);
                });
            } else {
                planActivityTable.ajax.reload();
            }
        }

        function cancel() {
            CancelPlannerBtn.addClass('d-none');
            UpdatePlannerBtn.addClass('d-none');
            EditPlannerBtn.removeClass('d-none');
            $('.planner-form').prop('disabled', true);
        }
    </script>
@endsection
