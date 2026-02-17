@php use App\Models\Auth\User;use App\Models\BR\Client;use App\Models\CRM\DebtRecovery\LoanAssignment;use App\Services\BR\ClientService; @endphp
@php @endphp
@extends('layouts.app')

@section('title')
    Loan: {{ $loan->AccountID }}
@endsection

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
    <li class="breadcrumb-item"><a href="{{ route('debt-collection.index') }}">Debt Collection</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h3 class="text-center my-2">{{ $loan->AccountID }}</h3>
                </div>
                <div class="card-body border-bottom">
                    @if($client instanceof Client)
                        @include('snippets.client_summary', ['client'=>$client,'show_summary'=>true])
                        <div class="mt-1">
                            <h5 class="h6 card-title">Contacts</h5>
                            <div class="text center">
                                @php
                                    $ClientService = new ClientService($client);

                                @endphp
                                @if(is_string($ClientService->phoneNo()))
                                    <div class="btn-group">
                                        <button type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false" class="btn btn-link dropdown-toggle">
                                            {{ $ClientService->phoneNo() }}
                                        </button>
                                        <div class="dropdown-menu" style="">
                                            <a class="dropdown-item disabled text-decoration-line-through"
                                               href="javascript:void(0)"><i class="fas fa-phone-alt"></i> Call</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item send-message-to-action" href="javascript:void(0)"
                                               data-info="{{ route('debt-sms.store', [$loan->AccountID]) }}~{{ $client->Name }}~{{ $ClientService->phoneNo() }}">
                                                <i class="fas fa-message"></i> Message</a>
                                        </div>
                                    </div>
                                @endif

                                @if(is_string($ClientService->getEmail()))
                                    <a href="javascript:void(0)"
                                       data-info="{{ route('client-mail.store', [$client->ClientID]) }}~{{ $client->Name }}~{{ $ClientService->getEmail() }}"
                                       class="btn btn-lg btn-link me-1 my-1 send-mail-to-action">{{ $ClientService->getEmail() }}</a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body border-bottom pt-2">
                    <p class="mb-0 fw-bold">Assignee
                        <button class="btn btn-sm btn-primary float-end trigger-reassign-loan-modal"><i
                                class="fas fa-edit"></i> reassign
                        </button>
                    </p>
                    <div class="clearfix"></div>
                    <hr class="my-0">
                    @if($assignment instanceof  LoanAssignment && $assignment->user instanceof User)
                        @include('snippets.user_summary', ['user'=>$assignment->user])
                    @else
                        <h4 class="mt-2 text-center">Unassigned</h4>
                    @endif
                </div>
                <div class="card-body my-2">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3"><span>Product : </span><b class="float-end">{{ $loan->ProductName }}</b></li>
                        <li class="mb-3"><span>Classification : </span><b
                                class="float-end">{{ $loan->Classification }}</b></li>
                        <li class="mb-3"><span>Frequency : </span><b class="float-end">{{ $loan->Frequency }}</b></li>
                        <li class="mb-3"><span>Balance : </span><b
                                class="float-end">{{ number_format($loan->OutstandingBalance,2) }}</b></li>
                        <li class="mb-3"><span>Arrears Amount: </span><b
                                class="float-end">{{ number_format($loan->ArrearsAmount,2) }}</b></li>
                        <li class="mb-3"><span>Arrears Days: </span><b
                                class="float-end">{{ number_format($loan->ArrearsDays) }}</b>
                        </li>
                        <li class="mb-3"><span>Maturity Date: </span><b
                                class="float-end">{{ $loan->MaturityDate->format('M d, Y') }}</b></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body d-flex align-items-start row p-3">
                            <div class="col-4">
                                <button class="btn btn-outline-primary text-center w-100 create-new-task"
                                        type="button"
                                        data-action="{{ route('debt-collection-tasks.store',[$loan->AccountID]) }}"><i
                                        class="fa-solid fa-list-check"></i> <br> new task
                                </button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-primary text-center w-100 add-party-appointment-btn"
                                        type="button"
                                        data-action="{{ route('debt-collection-schedule.meeting',[$loan->AccountID]) }}">
                                    <i class="fas fa-calendar-plus"></i> <br> appointment
                                </button>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-outline-primary text-center w-100 add-party-scheduled-call-btn"
                                        type="button"
                                        data-action="{{ route('debt-collection-schedule.call',[$loan->AccountID]) }}">
                                    <i class="align-middle" data-feather="phone-forwarded"></i> <br> schedule a call
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 tab">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab" role="tab"
                                                aria-selected="false">Activities</a></li>
                        @if($loan->guarantors_count>0)
                            <li class="nav-item"><a class="nav-link" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false"
                                                    onclick="fetchGuarantorsTable()">Guarantors</a>
                            </li>
                        @elseif($loan->collaterals_count>0)
                            <li class="nav-item"><a class="nav-link" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false"
                                                    onclick="fetchCollateralsTable()">Collaterals</a>
                            </li>
                        @else
                            <li class="nav-item"><a class="nav-link" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false">Guarantorship</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link" href="#tab-2" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchSMSTable()">Messages</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-3" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchTasksTable()">Tasks</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-4" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchScheduleTable()">Schedule</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-5" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchAssignmentsTable()">Assignment
                                History</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active m-2" id="tab-0" role="tabpanel">
                            <div id="activitiesMain" class="px-2 pt-0 w-100 activities" style="max-height: 100vh"
                                 data-url="{{  route('debt-collection.activities',[$loan->AccountID]) }}"></div>
                            <div class="d-grid text-center" id="activitiesMessage"></div>
                        </div>
                        <div class="tab-pane m-2" id="tab-1" role="tabpanel">
                            @if($loan->guarantors_count>0)
                                <div class="row">
                                    <div class="col-sm-6 col-12">
                                        List of Guarantors
                                    </div>
                                    <div class="col-sm-6 col-12">
                                        <div class="card-actions float-end">
                                            <button class="btn btn-sm btn-primary mx-2" id="triggerSMSGuarantorsBtn"
                                                    title="send sms to all"><i class="fas fa-message"></i></button>
                                            <button class="btn btn-sm btn-primary mx-2" disabled
                                                    title="send email to all">
                                                <i
                                                    class="fas fa-envelope"></i></button>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="table-responsive">
                                        <table id="guarantorsTable"
                                               class="table table-striped no-footer dtr-inline w-100 ">
                                            <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Party</th>
                                                <th>Amount</th>
                                                <th>Dated</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table></div>
                                    </div>
                                </div>
                            @elseif($loan->collaterals_count>0)
                                <div class="row">
                                    <div class="col-12"><h3>Collaterals</h3></div>
                                    <div class="col-12">
                                        <div class="table-responsive">
                                        <table id="collateralsTable"
                                               class="table table-striped no-footer dtr-inline w-100">
                                            <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Name</th>
                                                <th>Value</th>
                                                <th>Dated</th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table></div>
                                    </div>
                                </div>
                            @else
                                <div class="my-2">
                                    <div class="alert alert-primary m-0" role="alert">
                                        <div class="alert-icon">
                                            <i class="far fa-fw fa-bell"></i>
                                        </div>
                                        <div class="alert-message">
                                            <strong>Self Guarantor</strong> This loan is self Guaranteed by the member.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="tab-pane m-2" id="tab-2" role="tabpanel">
                            <div class="table-responsive">
                            <table id="MessagesTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 ">
                                <thead>
                                <tr>
                                    <th>SMS ID</th>
                                    <th>Party</th>
                                    <th>Dated</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table></div>
                        </div>
                        <div class="tab-pane m-2" id="tab-3" role="tabpanel">
                            <div class="table-responsive">
                            <table id="tasksTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 ">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="w-50">Task</th>
                                    <th>Due On</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table></div>
                        </div>
                        <div class="tab-pane m-2" id="tab-4" role="tabpanel">
                            <div class="table-responsive">
                            <table id="scheduleTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 ">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Type</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table></div>
                        </div>
                        <div class="tab-pane m-2" id="tab-5" role="tabpanel">
                            <div class="table-responsive">
                            <table id="assignmentsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 ">
                                <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>User</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Notes</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="debtCollectionActionsModel" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($loan->guarantors_count>0)
                        <div class="onboarding-content with-gradient d-none modal-item" id="messageToGuarantorsModal">
                            <div class="accordion accordion-flush mb-3" id="accordionHelp">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="flush-headingOne">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"
                                                aria-expanded="false" aria-controls="flush-collapseOne">
                                            Help Notes
                                        </button>
                                    </h2>
                                    <div id="flush-collapseOne" class="accordion-collapse collapse"
                                         aria-labelledby="flush-headingOne" data-bs-parent="#accordionHelp">
                                        <div class="accordion-body">
                                            <ul class="guarantor-group guarantor-group-flush">
                                                <li class="guarantor-group-item">You can use <code> #name</code> to be
                                                    replaced by their name while sending.
                                                </li>
                                                <li class="guarantor-group-item">You can use <code> #amount</code> to be
                                                    replaced Guarantee Amount while sending.
                                                </li>
                                                <li class="guarantor-group-item">You can use <code> #arrears</code> to
                                                    be
                                                    replaced by Days in Arrears <code>{{ $loan->ArrearsDays }}</code>
                                                    while sending.
                                                </li>
                                                <li class="guarantor-group-item">You can use <code> #loanee</code> to be
                                                    replaced by Name of Loanee <code>{{ $loan->AccountName }}</code>
                                                    while sending.
                                                </li>
                                                <li class="guarantor-group-item">You can use <code> #product</code> to
                                                    be
                                                    replaced by Product Name <code>{{ $loan->ProductName }}</code>
                                                    while sending.
                                                </li>
                                                <li class="guarantor-group-item">You can use <code> #account</code> to
                                                    be
                                                    replaced by Loan Account ID <code>{{ $loan->AccountID }}</code>
                                                    while sending.
                                                </li>
                                                <li class="guarantor-group-item">You can use <code> #date</code> to be
                                                    replaced by
                                                    <code>{{ $loan->processDate->format('M d, Y') }}</code> while
                                                    sending.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <form method="post" id="messageToGuarantorsForm"
                                  action="{{ route('debt-collection.guarantors.sms', [$loan->AccountID]) }}">
                                @csrf
                                <div class="mb-3 col-12">
                                    <label class="form-label" for="guarantor_message_content">Content <span
                                            class="text-danger">*</span></label> &nbsp; <b
                                        class="float-end text-info"
                                        id="guarantorMsgCounter"></b>
                                    <textarea name="guarantor_message_content" id="guarantor_message_content"
                                              class="form-control" rows="4"
                                              maxlength="5000" minlength="2"></textarea>
                                    <p id="guarantor_message_content" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="messageToGuarantorsBtn" type="submit">
                                        <i
                                            class="fas fa-plane-departure"></i> send
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                    <div class="onboarding-content with-gradient d-none modal-item" id="reassignLoanModal">
                        <form action="{{ route('loan-assignment.store',[$loan->AccountID]) }}" method="post"
                              id="reassignLoanForm">
                            @csrf
                            <div class="mb-3">
                                <label for="Assignee" class="form-label">User </label>
                                <select class="form-control" name="Assignee" id="Assignee" required></select>
                                <p id="Assignee_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="reassignLoanBtn" type="submit"><i
                                        class="fas fa-shuffle"></i> reassign
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
    @include('snippets.actions.tasks')
    @include('snippets.actions.mailto')
    @include('snippets.actions.schedule')
    @php
        $help = '<div class="accordion accordion-flush mb-3" id="accordionHelp"> <div class="accordion-item"><h2 class="accordion-header" id="flush-headingOne">
    <button class="accordion-button collapsed" type="button"
            data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"
            aria-expanded="false" aria-controls="flush-collapseOne">
        Help Notes
    </button>
</h2>
<div id="flush-collapseOne" class="accordion-collapse collapse"
     aria-labelledby="flush-headingOne" data-bs-parent="#accordionHelp">
    <div class="accordion-body">
        <ul class="guarantor-group guarantor-group-flush">
            <li class="guarantor-group-item">You can use <code> #name</code> to be
                replaced by their name while sending.
            </li>
            <li class="guarantor-group-item">You can use <code> #amount</code> to be
                replaced Guarantee Amount while sending.
            </li>
            <li class="guarantor-group-item">You can use <code> #arrears</code> to
                be
                replaced by Days in Arrears <code>'. $loan->ArrearsDays .'</code>
                while sending.
            </li>
            <li class="guarantor-group-item">You can use <code> #loanee</code> to be
                replaced by Name of Loanee <code>'. $loan->AccountName .'</code>
                while sending.
            </li>
             <li class="guarantor-group-item">You can use <code> #product</code> to be replaced by Product Name <code>{{ $loan->ProductName }}</code>
                                                    while sending. </li>
            <li class="guarantor-group-item">You can use <code> #account</code> to
                be
                replaced by Loan Account ID <code>'. $loan->AccountID .'</code>
                while sending.
            </li>
            <li class="guarantor-group-item">You can use <code> #date</code> to be
                replaced by
                <code>'. $loan->processDate->format('M d, Y') .'</code> while
                sending.
            </li>
        </ul>
    </div>
</div>
</div>
</div>'
    @endphp
    @include('snippets.actions.sms',['help'=> $help])
    @include('snippets.actions.activities')

    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const $Modal = $('#debtCollectionActionsModel');
        $(function () {
            @if($loan->guarantors_count>0)
            $('#guarantor_message_content').keyup(function () {
                $('#guarantorMsgCounter').html(parseInt((this.value.length / 168) + 1) + " sms's");
            });

            $(document).on('click', '#triggerSMSGuarantorsBtn', function () {
                $(".modal-item").addClass('d-none');
                $('.modal-title').html('Send a SMS Messages to Loan  Guarantors');
                $('#messageToGuarantorsModal').removeClass('d-none');
                $Modal.modal('show');
            });

            $('form#messageToGuarantorsForm').submit(async function (e) {
                e.preventDefault();
                let response = await saveForm($(this), $('#messageToGuarantorsBtn'), false, true, true);
                if (response) {
                    $Modal.modal('hide');
                    if (typeof response.activity === "object") {
                        appendAct($('#activitiesMain'), response.activity.html, true);
                    }
                    if (typeof response.activities === "object") {
                        $.map(response.activities, function (activity) {
                            appendAct($('#activitiesMain'), activity.html, true);
                        });
                    }
                }
            });
            @endif

            $('#Assignee').select2({
                placeholder: "Search a user", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{!! route('users.select2') !!}',
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
            $(document).on('click', '.trigger-reassign-loan-modal', function () {
                $(".modal-item").addClass('d-none');
                $('#reassignLoanModal').removeClass('d-none');
                $('.modal-title').html('<b class="text-warning fw-bold ">RE ASSIGN</b> loan ');
                $Modal.modal('show');
            });
            $('form#reassignLoanForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#reassignLoanBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
        });

        @if($loan->guarantors_count>0)
        function fetchGuarantorsTable() {
            if (!$.fn.DataTable.isDataTable('#guarantorsTable')) {
                $('#guarantorsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    ajax: {
                        url: '{{ route('debt-collection.guarantors',[$loan->AccountID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "DT_RowIndex",
                                sort: "GuarantorID",
                            }, name: 'GuarantorID', searchable: true
                        },
                        {
                            data: {
                                _: "client",
                                sort: "client.Name",
                            }, name: 'client.Name', searchable: true
                        },
                        {data: 'GuaranteeAmount', name: 'GuaranteeAmount'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no guarantors under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading guarantors.");
                });
            } else {
                $('#guarantorsTable').DataTable().ajax.reload();
            }
        }
        @elseif($loan->collaterals_count>0)
        function fetchCollateralsTable() {
            if (!$.fn.DataTable.isDataTable('#collateralsTable')) {
                $('#collateralsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    ajax: {
                        url: '{{ route('debt-collection.collaterals',[$loan->AccountID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'CollateralID', name: 'CollateralID'},
                        {data: 'collateral.Description', name: 'collateral.Description'},
                        {data: 'NetCollateralValue', name: 'NetCollateralValue'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no collaterals under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading collaterals.");
                });
            } else {
                $('#collateralsTable').DataTable().ajax.reload();
            }
        }
        @endif
        function fetchSMSTable() {
            if (!$.fn.DataTable.isDataTable('#MessagesTable')) {
                $('#MessagesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    ajax: {
                        url: '{{ route('debt-sms.index', [$loan->AccountID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "SMSId", name: 'SMSId'},
                        {data: 'party', name: 'party', orderable: false, searchable: false},
                        {data: 'Dated', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "<span class='text-center'>No records found</span>"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading collaterals.");
                });
            } else {
                $('#MessagesTable').DataTable().ajax.reload();
            }
        }

        function fetchTasksTable() {
            if (!$.fn.DataTable.isDataTable('#tasksTable')) {
                $('#tasksTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    ajax: {
                        url: '{{ route('debt-collection-tasks.index', [$loan->AccountID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Notes', name: 'Notes'},
                        {data: 'Dated', name: 'Dated'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no tasks under current filter, <a href='#' class='create-new-task' data-action='{{ route('debt-collection-tasks.store',[$loan->AccountID]) }}'>create one"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading tasks.");
                });
            } else {
                $('#tasksTable').DataTable().ajax.reload();
            }
        }

        function fetchScheduleTable() {
            if (!$.fn.DataTable.isDataTable('#scheduleTable')) {
                $('#scheduleTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    ajax: {
                        url: '{{ route('debt-collection-schedule.index', [$loan->AccountID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'ScheduledType', name: 'ScheduledType'},
                        {data: 'StartOn', name: 'StartOn'},
                        {data: 'EndOn', name: 'EndOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>There is no schedule entities under this filter.</p>"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading schedule entities.");
                });
            } else {
                $('#scheduleTable').DataTable().ajax.reload();
            }
        }

        function fetchAssignmentsTable() {
            if (!$.fn.DataTable.isDataTable('#assignmentsTable')) {
                $('#assignmentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    ajax: {
                        url: '{{ route('loan-assignment.index', [$loan->AccountID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'user', name: 'user'},
                        {data: 'StartOn', name: 'StartOn'},
                        {data: 'EndOn', name: 'EndOn'},
                        {data: 'Notes', name: 'Notes'},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>There are no assignments under this filter.</p>"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading schedule entities.");
                });
            } else {
                $('#assignmentsTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
