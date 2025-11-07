@extends('layouts.app')

@section('title','Unattached Contact')
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
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Unattached Contact</h5>
                </div>
                <h3 class="text-center">{{ $contact->Label }}</h3>
                <div class="card-body mx-1 mb-0 mt-1">
                    <div class="text center">
                        @if(!empty($contact->Phone))
                            <div class="btn-group">
                                <button type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false"
                                        class="btn btn-link dropdown-toggle">
                                    {{ $contact->Phone }}
                                </button>
                                <div class="dropdown-menu" style="">
                                    <a class="dropdown-item disabled text-decoration-line-through"
                                       href="javascript:void(0)"><i class="fas fa-phone-alt"></i> Call</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item disabled" href="javascript:void(0)">
                                        <i class="fas fa-message"></i> Message</a>
                                </div>
                            </div>
                        @endif @if(!empty($contact->Email))
                            <a href="javascript:void(0)"
                               class="btn btn-lg btn-link me-1 my-1 disabled">{{ $contact->Email }}</a>
                        @endif
                    </div>
                </div>
                @if(!$call instanceof \App\Models\Communication\Call)
                    <div class="card-body mx-1 mb-0 mt-1">
                        <div class="mt-1 border-top border-1 py-3">
                            <div class="row">
                                <div class="col-6">
                                    <div class="btn-group w-100">
                                        <button type="button" class="btn btn-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Lead
                                        </button>
                                        <div class="dropdown-menu" style="">
                                            <a class="dropdown-item  click-summary-data" href="javascript:void(0)"
                                               data-click_url="{{ route('leads.create',['type'=>\App\Enums\LeadTypeEnum::Individual->name,'contact'=>$contact->ContactID]) }}"
                                               data-summary_title="Add Individual Lead"
                                            ><i class="fas fa-plus-circle"></i> New Individual Lead</a>
                                            <a class="dropdown-item  click-summary-data" href="javascript:void(0)"
                                               data-click_url="{{ route('leads.create',['type'=>\App\Enums\LeadTypeEnum::Company->name, 'contact'=>$contact->ContactID]) }}"
                                               data-summary_title="Add Corporate Lead"
                                            ><i class="fas fa-plus-circle"></i> New Corporate Lead</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               id="triggerLeadContactBtn"><i class="fas fa-address-card"></i> Attach to
                                                Lead</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="btn-group w-100">
                                        <button type="button" class="btn btn-info dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Member
                                        </button>
                                        <div class="dropdown-menu" style="">
                                            <a class="dropdown-item" href="javascript:void(0)"
                                               id="triggerClientContactBtn"><i class="fas fa-address-card"></i> Attach
                                                to Member</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="card-body mx-1 mb-0 mt-1">
                    @include('snippets.behind_scenes',['model'=>$contact])
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xxl-9">
            <div class="row">
                @if($call instanceof \App\Models\Communication\Call)
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body row ">
                                <div class="col-md-4 col-12 text-center">
                                    Start <br> <b>{{ $call->StartOn->format('M d, Y h:i a') }}</b>
                                </div>
                                <div class="col-md-4 col-12 text-center">
                                    Timer <br><b id="callTimer"></b>
                                </div>
                                <div class="col-md-4 col-12 text-center">
                                    Plan End <br> <b>Unknown</b>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                                <form action="{{ route('contacts-calls.update',[$contact->ContactID, $call->CallID]) }}"
                                      method="post" class="col-12"
                                      id="OngoingCallForm">
                                    @method('put') @csrf
                                    <div class="row">
                                        <div class="col-md-6 col-12 mb-3">
                                            <label class="form-label" for="call_discussion">Discussion <span
                                                    class="text-danger">*</span> </label>
                                            <textarea name="call_discussion" id="call_discussion" class="form-control"
                                                      rows="5" maxlength="5000"
                                                      minlength="5">{{($call->discussion instanceof \App\Models\CRM\Discussion)?$call->discussion->Discussion:''}}</textarea>
                                            <p id="call_discussion_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-6 col-12 mb-3">
                                            <label class="form-label" for="private_notes">Confidential Notes </label>
                                            <textarea name="private_notes" id="private_notes" class="form-control"
                                                      rows="5" maxlength="5000"></textarea>
                                            <p id="private_notes_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-secondary float-start" disabled
                                        >
                                            rescheduled
                                        </button>
                                        <button class="btn btn-primary float-end" id="OngoingCallBtn" type="submit"><i
                                                class="fas fa-phone-slash"></i> end call
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="tab">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchCallsTable()">Calls</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tab-1" data-bs-toggle="tab" role="tab"
                                            aria-selected="false" onclick="fetchMailsTable()">Emails</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-0" role="tabpanel">
                        <table id="callsTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Duration</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane m-2" id="tab-1" role="tabpanel">
                        <table id="EmailsTable"
                               class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                            <thead>
                            <tr>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Dated</th>
                                <th>actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="contactActionModal" tabindex="-1" role="dialog" aria-hidden="true"
         data-bs-backdrop="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient d-none modal-item" id="addClientContactModal">
                        <form action="{{ route('contacts.attach.client',[$contact->ContactID]) }}" method="post"
                              id="addClientContactForm" class="row">
                            @csrf
                            <div class="mb-3">@method('PUT')
                                <label for="client" class="form-label">Member <span class="text-danger">*</span></label>
                                <select class="form-control" name="client" id="client" required></select>
                                <p id="client_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addClientContactBtn" type="submit">
                                    <i class="fas fa-save"></i> attach contact to member
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="addLeadContactModal">
                        <form action="{{ route('contacts.attach.lead',[$contact->ContactID]) }}" method="post"
                              id="addLeadContactForm" class="row">
                            @csrf
                            <div class="mb-3">@method('PUT')
                                <label for="lead" class="form-label">Lead <span class="text-danger">*</span></label>
                                <select class="form-control" name="lead" id="lead" required></select>
                                <p id="lead_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="addLeadContactBtn" type="submit">
                                    <i class="fas fa-save"></i> attach contact to lead
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
    
    <script>const $Modal = $('#contactActionModal');
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchCallsTable();

            @if($call instanceof \App\Models\Communication\Call)
            durationTimer(document.getElementById("callTimer"), '{{ $call->StartOn->toDateTimeString() }}')

            $('form#OngoingCallForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#OngoingCallBtn'), true, false, true);
            });
            @else
            $('#client').select2({
                placeholder: "Choose a client...", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{{route('clients.select2')}}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name + ' - ' + item.ClientID, id: item.ClientID}
                            })
                        };
                    },
                    cache: true
                }
            });

            $(document).on('click', '#triggerClientContactBtn', function () {
                $(".modal-item").addClass('d-none');
                $('#addClientContactModal').removeClass('d-none');
                $('.modal-title').html('<b> Attach </b> Contact to Member');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addClientContactForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addClientContactBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $('#lead').select2({
                placeholder: "Choose a lead...", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{{route('leads.select2')}}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name, id: item.LeadID}
                            })
                        };
                    },
                    cache: true
                }
            });

            $(document).on('click', '#triggerLeadContactBtn', function () {
                $(".modal-item").addClass('d-none');
                $('#addLeadContactModal').removeClass('d-none');
                $('.modal-title').html('<b>Attach </b> Contact to Lead');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addLeadContactForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addLeadContactBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            @endif
        });

        function durationTimer(timerElement, startTimeString) {
            const startTime = new Date(startTimeString).getTime();
            const now = new Date().getTime();
            const distance = Math.abs((now - startTime));
            /*  console.log(distance);
              console.log(startTime);
              console.log(now);*/
            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const x = setInterval(function () {
                seconds = parseInt(seconds);
                minutes = parseInt(minutes);
                seconds++;
                if (seconds >= 60) {
                    seconds = 0;
                    minutes++;
                } else if (seconds < 10) {
                    seconds = "0" + seconds
                }
                if (minutes >= 60) {
                    minutes = 0;
                    hours++;
                } else if (minutes < 10) {
                    minutes = "0" + minutes
                }

                timerElement.innerHTML = hours + ":" + minutes + ":" + seconds;

            }, 1000);
        }

        function fetchCallsTable() {
            if (!$.fn.DataTable.isDataTable('#callsTable')) {
                $('#callsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // "order": [[3, 'asc']],
                    ajax: {
                        url: "{{ route('contacts-calls.index',[$contact->ContactID]) }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "user",
                                sort: "CallID",
                            }, name: 'CallID', searchable: false
                        },
                        {data: 'CallStatusID', name: 'CallStatusID'},
                        {data: 'StartOn', name: 'StartOn'},
                        {data: 'EndOn', name: 'EndOn'},
                        {data: 'Duration', name: 'Duration', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no calls  under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading contacts.");
                });
            } else {
                $('#callsTable').DataTable().ajax.reload();
            }
        }

        function fetchMailsTable() {
            if (!$.fn.DataTable.isDataTable('#EmailsTable')) {
                $('#EmailsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    // "order": [[3, 'asc']],
                    ajax: {
                        url: "{{ route('contacts-mail.index',[$contact->ContactID]) }}",
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "Type", name: 'Type'},
                        {data: 'Subject', name: 'Subject'},
                        {data: 'Dated', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no emails  under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading contact emails.");
                });
            } else {
                $('#EmailsTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
