@php use App\Services\BR\ClientService; @endphp
@php use App\Models\BR\Client; @endphp
@php use App\Models\CRM\Discussion; @endphp
@php use App\Enums\CallStatusEnum; @endphp
@php use Carbon\Carbon; @endphp
@php use App\Http\Requests\Call\StartCallRequest; @endphp
@php use App\Http\Requests\Call\StartMeetingRequest; @endphp
@php use App\Models\Communication\Call; @endphp
@php use App\Models\CRM\Meeting; @endphp
@extends('layouts.app')

@section('title')
    Client: {{ $client->ClientID }}
@endsection

@section('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Gwendolyn&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }

        .gwendolyn-regular {
            font-family: "Gwendolyn", cursive;
            font-weight: 400;
            font-style: normal;
        }

    </style>
@endsection
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">CRM</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                <div class="card-body mx-1 mb-0 mt-1">
                    @include('snippets.client_summary', ['client'=>$client,'show_summary'=>true])
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h5 class="h6 card-title">Contacts</h5>
                    <div class="text center">
                        @php
                            $phone = (new ClientService($client))->phoneNo();
                        @endphp
                        @if(is_string($phone))
                            <div class="btn-group">
                                <button type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" class="btn btn-link dropdown-toggle">
                                    {{ $phone }}
                                </button>
                                <div class="dropdown-menu" style="">
                                    <a class="dropdown-item disabled text-decoration-line-through"
                                       href="javascript:void(0)"><i class="fas fa-phone-alt"></i> Call</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item send-message-to-action" href="javascript:void(0)"
                                       data-info="{{ route('client-sms.store', [$client->ClientID]) }}~{{ $client->Name }}~{{ $phone }}">
                                        <i class="fas fa-message"></i> Message</a>
                                </div>
                            </div>
                        @endif

                        {{--
                                                @if(!empty($client->Phone1))
                                                    <a href="tel:{{ $client->Phone1 }}"
                                                       class="btn btn-lg btn-link me-1 my-1">{{ $client->Phone1 }}</a>
                                                @endif
                                                @if(!empty($client->Phone2))
                                                    <a href="tel:{{ $client->Phone2 }}"
                                                       class="btn btn-lg btn-link me-1 my-1">{{ $client->Phone2 }}</a>
                                                @endif
                                                @if(!empty($client->Mobile))
                                                    <a href="tel:{{ $client->Mobile }}"
                                                       class="btn btn-lg btn-link me-1 my-1">{{ $client->Mobile }}</a>
                                                @endif--}}
                        @if(!empty($client->Email))
                            <a href="javascript:void(0)"
                               data-info="{{ route('client-mail.store', [$client->ClientID]) }}~{{ $client->Name }}~{{ $client->Email }}"
                               class="btn btn-lg btn-link me-1 my-1 send-mail-to-action">{{ $client->Email }}</a>
                        @endif
                    </div>
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h5 class="h6 card-title">Marketing Lists <a href="#" class="float-end click-summary-data"
                                                                 data-click_url="{{ route('client-marketing-lists.index',$client->ClientID) }}"
                                                                 data-summary_title="Marketing Lists"><i
                                class="fas fa-edit"></i></a></h5>
                    @foreach($MarketingListMember as $list)
                        <a href="{{ route('marketing-list.show',[$list->slug]) }}"
                           class="btn btn-pill btn-secondary btn-sm">{{ $list->Label }}</a>
                    @endforeach
                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h5 class="h6 card-title">About</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3"><i class="align-middle"
                                            data-feather="map-pin"></i> {{ $client->Address1 }} {{ $client->Address2 }}
                            , {{ $client->CityID }}, {{ $client->CountryID }}
                        </li>

                        <li class="mb-3">Notes: <i class="align-middle" data-feather="info"></i> <br> <span
                                style="text-align: justify">{{ $client->Notes }}</span></li>
                        <li class="mb-3 text-center">
                            <a href="#" style="font-size: 40px" class=" w-100 modal-show-image gwendolyn-regular">signature</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-8 col-xxl-9">
            <div class="row">
                <div class="col-lg-6 col-12">
                    <div class="card">
                        <div class="card-body d-flex align-items-start row p-3">
                            <div class="col-4">
                                <button class="btn btn-outline-primary text-center w-100 add-party-notes-btn"
                                        type="button"
                                        data-action="{{ route('client-notes.store',[$client->ClientID]) }}"><i
                                        class="fas fa-plus"></i> <br> note
                                </button>
                            </div>
                            <div class="col-4">
                                <button type="button"
                                        class="btn btn-outline-primary text-center w-100 add-party-ticket-btn"
                                        data-action="{{ route('client-tickets.store',[$client->ClientID]) }}">
                                    <i class="align-middle" data-feather="check-square"></i> <br>add a ticket
                                </button>
                            </div>
                            <div class="col-4">
                                <div class="btn-group w-100" data-action="">
                                    <button type="button"
                                            class="btn btn-outline-primary w-100 text-center dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-calendar-plus"></i> <br> others
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item add-party-appointment-btn"
                                               href="javascript:void(0);"
                                               data-action="{{ route('client-schedule.meeting',[$client->ClientID]) }}"><i
                                                    class="fas fa-calendar-plus"></i> schedule an appointment</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item add-party-scheduled-call-btn"
                                               href="javascript:void(0);"
                                               data-action="{{ route('client-schedule.call',[$client->ClientID]) }}"> <i
                                                    class="align-middle" data-feather="phone-forwarded"></i>
                                                schedule a
                                                call</a></li>
                                        <li><a class="dropdown-item" id="triggerStartMeetingBtn"
                                               href="javascript:void(0);">
                                                <i class="fas fa-walking"></i> start unscheduled meeting</a></li>
                                        <li><a class="dropdown-item" id="triggerStartCallBtn"
                                               href="javascript:void(0);"><i class="align-middle"
                                                                             data-feather="phone-outgoing"></i> start
                                                unscheduled call</a></li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="card">
                        @if($introducer instanceof Client)
                            <a class="card-body d-flex align-items-start text-decoration-none"
                               href="{{ route('clients.show',$introducer->ClientID) }}">
                                {!! $introducer->getImage('width="42" height="42" class="rounded-circle me-2" alt=".."') !!}
                                <div class="flex-grow-1 h6">
                                    introducer<br>
                                    <strong>{{ $introducer->Name }}</strong>
                                </div>
                            </a>
                        @else
                            <div class="card-body d-flex align-items-start">
                                {!! $client->getImage('width="42" height="42" class="rounded-circle me-2" alt=".."') !!}
                                <div class="flex-grow-1 h6">
                                    introducer<br>
                                    <strong>Self</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="card">
                        <div class="card-body d-flex align-items-start">
                            <div class="flex-grow-1 h6">
                                relationship manager<br>
                                <strong>{{ ($client->RelationshipManagerID)??"NONE" }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
                @if($call instanceof Call)
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
                                    Plan End <br> <b>
                                        @if($call->EndOn instanceof Carbon\Carbon)
                                            {{ $call->EndOn?->diffInMinutes($call->StartOn,true) }} min
                                        @else
                                            Unkown / Unscheduled Call
                                        @endif
                                    </b>
                                </div>
                                @if($schedule instanceof \App\Models\CRM\Schedule)
                                    <div class="col-12"><b>Notes</b> <br>{{ $schedule->Notes }}</div>
                                @endif
                                <div class="col-12">
                                    <hr>
                                </div>
                                <form action="{{ route('client-calls.update',[$client->ClientID, $call->CallID]) }}"
                                      method="post" class="col-12"
                                      id="OngoingCallForm">
                                    @method('put') @csrf
                                    <div class="row">
                                        <div class="col-md-6 col-12 mb-3">
                                            <label class="form-label" for="call_discussion">Discussion <span
                                                    class="text-danger">*</span> </label>
                                            <textarea name="call_discussion" id="call_discussion" class="form-control"
                                                      rows="5" maxlength="5000" minlength="5">
                                               {{($call->discussion instanceof Discussion)?$call->discussion->Discussion:''}}
                                           </textarea>
                                            <p id="call_discussion_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-6 col-12 mb-3">
                                            <label class="form-label" for="private_notes">Client Notes </label>
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
                                {{--<div class="col-4">
                                    <button class="btn btn-warning w-100" type="button" id="triggerUnreachableBtn"><i class="fas fa-phone-slash"></i> Unreachable</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-info w-100" type="button" id="triggerRescheduleBtn"><i class="fas fa-refresh"></i> Reschedule</button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-primary w-100" type="button" id="triggerStartCallBtn"><i class="fas fa-phone"></i> Start Call</button>
                                </div>--}}
                            </div>
                        </div>
                    </div>
                @elseif($meeting instanceof Meeting)
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border border-bottom pb-0">
                                <h3 class="card-title">Meeting : {{ $meeting->Title }} <span
                                        class="float-end text-black"> Timer : <b id="meetingTimer"></b></span></h3>
                            </div>
                            <div class="card-body ">
                                {{--<div class="col-md-4 col-12 text-center">
                                    Start <br> <b>{{ $meeting->StartOn->format('M d, Y h:i a') }}</b>
                                </div>
                                <div class="col-md-4 col-12 text-center">
                                    Timer <br><b id="meetingTimer"></b>
                                </div>
                                <div class="col-md-4 col-12 text-center">
                                    Plan End <br> <b>{{ $meeting->EndOn->diffInMinutes($meeting->StartOn,true) }}
                                        min</b>
                                </div>
                                <div class="col-12"><b>Notes</b> <br>{{ $meeting->Notes }}</div>
                                <div class="col-12">
                                    <hr>
                                </div>--}}
                                <form
                                    action="{{ route('client-meetings.update',[$client->ClientID, $meeting->MeetingID]) }}"
                                    method="post" id="OngoingMeetingForm">
                                    @method('put') @csrf
                                    <div class="row">
                                        <div class="col-md-6 col-12 mb-3">
                                            <label class="form-label" for="ongoing_meeting_title">Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ongoing_meeting_title"
                                                   name="ongoing_meeting_title"
                                                   placeholder="Title" value="{{ $meeting->Title }}">
                                            <p id="ongoing_meeting_title_error"
                                               class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-6 col-12 mb-3">
                                            <label class="form-label" for="ongoing_meeting_location">Location <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="ongoing_meeting_location"
                                                   name="ongoing_meeting_location"
                                                   placeholder="Location" value="{{ $meeting->Location }}">
                                            <p id="ongoing_meeting_location_error"
                                               class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-6 col-12 mb-3">
                                            <label for="ongoing_meeting_users" class="form-label">Attendee (s) <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control " name="ongoing_meeting_users[]"
                                                    id="ongoing_meeting_users" multiple required>
                                                @foreach($meeting?->users as $attendee)
                                                    <option value="{{ $attendee->UserID }}"
                                                            selected>{{ $attendee->Name }}
                                                        - {{ $attendee->UserID }}</option>
                                                @endforeach
                                            </select>
                                            <p id="ongoing_meeting_users_error"
                                               class="invalid-feedback d-none error col-12" role="alert"></p>
                                        </div>
                                        <div class="col-md-6 col-12 mb-3">&nbsp;</div>
                                        <div class="col-md-6 col-12 mb-3">
                                            <label class="form-label" for="ongoing_meeting_discussion">Discussion <span
                                                    class="text-danger">*</span> </label>
                                            <textarea name="ongoing_meeting_discussion" id="ongoing_meeting_discussion"
                                                      class="form-control" rows="5" maxlength="5000" minlength="5"
                                            >{{($meeting->discussion instanceof Discussion)?$meeting->discussion->Discussion:''}}</textarea>
                                            <p id="ongoing_meeting_discussion_error"
                                               class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-md-6 col-12 mb-3">
                                            <label class="form-label" for="ongoing_meeting_notes">Confidential
                                                Notes </label>
                                            <textarea name="ongoing_meeting_notes" id="ongoing_meeting_notes"
                                                      class="form-control"
                                                      rows="5" maxlength="5000"></textarea>
                                            <p id="ongoing_meeting_notes_error"
                                               class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mt-4">
                                        <button class="btn btn-primary float-end" id="OngoingMeetingBtn" type="submit">
                                            <i
                                                class="fas fa-stopwatch"></i> end meeting
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @elseif($schedule instanceof \App\Models\CRM\Schedule)
                    <div class="col-12">
                        <div class="card">
                            @if($schedule->ScheduledType === Call::getPrimaryKey())
                                <div class="card-header border border-bottom pb-0">
                                    <h3 class="card-title">Scheduled Call </h3>
                                </div>
                                <div class="card-body row">
                                    <div class="col-md-4 col-12 text-center">
                                        Start <br> <b>{{ $schedule->StartOn->format('M d, Y h:i a') }}</b>
                                    </div>
                                    <div class="col-md-4 col-12 text-center">
                                        End <br><b> {{ $schedule->EndOn->format('M d, Y h:i a') }}</b>
                                    </div>
                                    <div class="col-md-4 col-12 text-center">
                                        Duration <br> <b>{{ $schedule->EndOn->diffInMinutes($schedule->StartOn,true) }}
                                            min</b>
                                    </div>
                                    <div class="col-12"><b>Notes</b> <br>{{ $schedule->Notes }}</div>
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-warning w-100" type="button" id="triggerUnreachableBtn">
                                            <i
                                                class="fas fa-phone-slash"></i> Unreachable
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-info w-100" type="button" id="triggerRescheduleBtn"><i
                                                class="fas fa-refresh"></i> Reschedule
                                        </button>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-primary w-100" type="button" id="triggerStartCallBtn"><i
                                                class="fas fa-phone"></i> Start Call
                                        </button>
                                    </div>
                                </div>
                            @elseif($schedule->scheduled instanceof Meeting)
                                <div class="card-header border border-bottom pb-0">
                                    <h3 class="card-title">Scheduled Meeting : {{ $schedule->scheduled->Title }} </h3>
                                </div>
                                <div class="card-body row">
                                    <div class="col-md-4 col-12 text-center">
                                        Start <br> <b>{{ $schedule->StartOn->format('M d, Y h:i a') }}</b>
                                    </div>
                                    <div class="col-md-4 col-12 text-center">
                                        End <br><b> {{ $schedule->EndOn->format('M d, Y h:i a') }}</b>
                                    </div>
                                    <div class="col-md-4 col-12 text-center">
                                        Duration <br> <b>{{ $schedule->EndOn->diffInMinutes($schedule->StartOn,true) }}
                                            min</b>
                                    </div>
                                    <div class="col-12"><b>Notes</b> <br>{{ $schedule->Notes }}</div>
                                    <div class="col-12">
                                        <hr>
                                    </div>
                                    <div class="col-6">
                                        &nbsp;
                                    </div>
                                    <div class="col-6">
                                        <button class="btn btn-primary w-100" type="button" id="triggerStartMeetingBtn"
                                                data-info="{{ $schedule->scheduled->MeetingID }}">
                                            <i class="fas fa-phone"></i> Start Meeting
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="card-header">
                                    <h3 class="card-title">Unknown Schedule: could not get details </h3>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body py-0">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab"
                                                    role="tab"
                                                    aria-selected="false">Activities</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-4" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false"
                                                    onclick="fetchDiscussionsTable()">Discussions</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-6" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchScheduleTable()">Schedule</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="#tab-5" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchCallsTable()">Calls</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-10" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchAppointmentsTable()">Appointments</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="#tab-3" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchNotesTable()">Private Notes</a>
                            </li>
                            <li class="nav-item"><a class="nav-link " href="#tab-2" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchAccountsTable()">Portfolio</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="#tab-1" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchRelationsTable()">Relations</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="#tab-7" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchTicketsTable()">Tickets</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-8" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchMailsTable()">Emails</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-9" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchSMSTable()">Messages</a></li>
                            <li class="nav-item"><a class="nav-link" href="#tab-12" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchContactsTable()">Contacts</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" href="#tab-11" data-bs-toggle="tab" role="tab"
                                                    aria-selected="false" onclick="fetchReviewsTable()">Feedback</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-content">
                    <div class="tab-pane active show" id="tab-0" role="tabpanel" aria-labelledby="profile-tab-1">
                        <div class="card">
                            <div class="card-header"><h5>Activities</h5></div>
                            <div class="card-body">
                                <div id="activitiesMain"></div>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-primary d-none" id="loadMoreBtn"
                                            onclick="fetchActivities()">Load more
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-1" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Relations - Next of Kin</h5></div>
                            <div class="card-body">
                                <table id="relationsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>ClientID</th>
                                        <th>Name</th>
                                        <th>Relation</th>
                                        <th>Type</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane m-2" id="tab-2" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Portfolio: Client Accounts</h5></div>
                            <div class="card-body">
                                <table id="accountsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Account ID</th>
                                        <th>ClearBalance</th>
                                        <th>Status</th>
                                        <th>Last</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-3" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Private Notes</h5></div>
                            <div class="card-body">
                                <table id="notesTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="w-50">Note</th>
                                        <th>On</th>
                                        <th>actions</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-4" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>discussions</h5></div>
                            <div class="card-body">
                                <table id="discussionsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>From</th>
                                        <th>Discussion</th>
                                        <th>Dated</th>
                                        <th>actions</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-5" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Call Logs</h5></div>
                            <div class="card-body">
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
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-6" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Schedule <small>Calls/Meetings ...</small></h5></div>
                            <div class="card-body">
                                <table id="scheduleTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>actions</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-7" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="mx-1 mb-2">
                                            <select class="form-control w-100 filter-field" name="TicketStatus"
                                                    id="TicketStatus">
                                                <option value="all">Status: Any & All</option>
                                                @foreach(App\Enums\TicketStatusEnum::class::cases() as $status)
                                                    <option value="{{ $status->value }}"
                                                        {{ (App\Enums\TicketStatusEnum::Active->value === $status->value)?'selected':'' }}>
                                                        Status: {{ $status->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="float-end">
                                            <button type="button" class="btn btn-primary add-party-ticket-btn"
                                                    data-action="{{ route('client-tickets.store',[$client->ClientID]) }}">
                                                <i class="align-middle" data-feather="check-square"></i> add a ticket
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="ticketsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Dated</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-8" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Emails <small>Incoming & Outgoing</small></h5></div>
                            <div class="card-body">
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
                    <div class="tab-pane m-2" id="tab-9" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Messages <small>Incoming & Outgoing</small></h5></div>
                            <div class="card-body">
                                <table id="MessagesTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Source</th>
                                        <th>Dated</th>
                                        <th>actions</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane m-2" id="tab-10" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Meeting Appointments</h5></div>
                            <div class="card-body">
                                <table id="AppointmentsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane m-2" id="tab-11" role="tabpanel">
                        <div class="card">
                            <div class="card-header"><h5>Reviews</h5></div>
                            <div class="card-body">
                                <table id="reviewsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Rate</th>
                                        <th>Sentiment</th>
                                        <th>Source</th>
                                        <th>Dated</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane m-2" id="tab-12" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6 col-12"><h5>Contacts <small>Other Contacts</small></h5></div>
                                    <div class="col-md-6 col-12">
                                        <div class="float-end">
                                            <button class="btn btn-primary click-summary-data" type="button"
                                                    data-click_url="{{ route('client-contacts.create',[$client->ClientID]) }}"
                                                    data-summary_title="Add Contact">
                                                <i class="fas fa-plus-circle"></i> add contact
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table id="contactsTable"
                                       class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Label</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>action</th>
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
    </div>
    <div class="modal fade" id="clientsActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">..</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="onboarding-content with-gradient align-items-center text-center d-none modal-item"
                         id="showImageModal">
                        {!! $client->getSignature('alt=".." class="w-100"',true) !!}
                    </div>
                    @if($schedule instanceof \App\Models\CRM\Schedule)
                        <div class="onboarding-content with-gradient d-none modal-item" id="CallUnreachableModal">
                            <form action="{{ route('call.unreachable',[$schedule->ScheduleID]) }}" method="post"
                                  id="CallUnreachableForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-control" required name="type" id="type">
                                        <option disabled selected>Select a Type</option>
                                        @foreach(CallStatusEnum::unreachable() as $option)
                                            <option value="{{ $option->value }}">{{ $option->description() }}</option>
                                        @endforeach
                                    </select>
                                    <p id="type_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="unreachable_start">Called Time <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime" id="unreachable_start"
                                           name="unreachable_start" required placeholder="Select start..">
                                    <p id="unreachable_start_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="unreachable_comment">Comment </label>
                                    <textarea name="unreachable_comment" id="unreachable_comment" class="form-control"
                                              rows="2" maxlength="250" minlength="5"></textarea>
                                    <p id="unreachable_comment_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-warning float-end" id="CallUnreachableBtn" type="submit"><i
                                            class="fas fa-phone-slash"></i> mark unreachable
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item" id="CallRescheduleModal">
                            <form action="{{ route('call.reschedule',[$schedule->ScheduleID]) }}" method="post"
                                  id="CallRescheduleForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="reschedule_start">Called Time <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime" id="reschedule_start"
                                           name="reschedule_start" required placeholder="Select start..">
                                    <p id="reschedule_start_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="schedule_start">Next Start <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime"
                                           value="{{ Carbon::now()->addDay()->setHour(8)->setMinute(0)->format('Y-m-d H:i') }}"
                                           id="schedule_start" name="schedule_start" placeholder="Select start..">
                                    <p id="schedule_start_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="schedule_discussion">Discussion <span
                                            class="text-danger">*</span> </label>
                                    <textarea name="schedule_discussion" id="schedule_discussion" class="form-control"
                                              rows="2" maxlength="5000" minlength="5">Client requested to be called later.</textarea>
                                    <p id="schedule_discussion_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="schedule_notes">Private Notes </label>
                                    <textarea name="schedule_notes" id="schedule_notes" class="form-control" rows="2"
                                              maxlength="5000"></textarea>
                                    <p id="schedule_notes_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-info float-end" id="CallRescheduleBtn" type="submit"><i
                                            class="fas fa-refresh"></i> reschedule
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                    <div class="onboarding-content with-gradient d-none modal-item" id="StartCallModal">
                        <form action="{{ route('client-calls.store',[$client->ClientID]) }}" method="post"
                              id="StartCallForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="call_initiated">Called Time <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime" id="call_initiated"
                                       name="call_initiated" required placeholder="Select start..">
                                <p id="call_initiated_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <input type="hidden" class="d-none" id="schedule" name="schedule" readonly
                                   value="{{ ($schedule instanceof \App\Models\CRM\Schedule)?$schedule->ScheduleID:StartCallRequest::NoSchedule }}">
                            <p id="schedule_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="StartCallBtn" type="submit"><i
                                        class="fas fa-clock"></i> start call
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="onboarding-content with-gradient d-none modal-item" id="StartMeetingModal">
                        <form action="{{ route('client-meetings.store',[$client->ClientID]) }}" method="post"
                              id="StartMeetingForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="meeting_initiated_title">Title <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="`meeting_initiated_title`"
                                       name="meeting_initiated_title"
                                       placeholder="Title"
                                       value="{{ ($schedule instanceof \App\Models\CRM\Schedule)?$schedule->Title:'' }}">
                                <p id="meeting_initiated_title" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="meeting_initiated_location">Location <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="meeting_initiated_location"
                                       name="meeting_initiated_location"
                                       placeholder="Location"
                                       value="{{ ($schedule instanceof \App\Models\CRM\Schedule && $schedule->scheduled instanceof Meeting)?$schedule->scheduled->Location:'' }}">
                                <p id="meeting_location_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="meeting_initiated">Start Time <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-datetime" id="meeting_initiated"
                                       name="meeting_initiated" required placeholder="Select start..">
                                <p id="meeting_initiated_error" class="invalid-feedback d-none error col-12"
                                   role="alert"></p>
                            </div>
                            <input type="hidden" class="d-none" id="meeting_schedule" name="meeting_schedule" readonly
                                   value="{{ ($schedule instanceof \App\Models\CRM\Schedule)?$schedule->ScheduleID:StartMeetingRequest::NoSchedule }}">
                            <p id="meeting_schedule_error" class="invalid-feedback d-none error col-12"
                               role="alert"></p>
                            <hr>
                            <div class="mt-4">
                                <button type="button" class="btn btn-secondary float-start"
                                        data-bs-dismiss="modal">
                                    cancel
                                </button>
                                <button class="btn btn-primary float-end" id="StartMeetingBtn" type="submit"><i
                                        class="fas fa-clock"></i> start meeting
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
    @include('snippets.actions.tickets')
    @include('snippets.actions.mailto')
    @include('snippets.actions.notes')
    @include('snippets.actions.schedule')
    @include('snippets.actions.sms')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const $Modal = $('#clientsActionsModal');
        let accountsTable = null, relationsTable = null, callsTable = null, discussionsTable = null, notesTable = null,
            scheduleTable = null, ticketsTable = null, unreachable_start = null, reschedule_start = null,
            call_initiated = null, meeting_initiated = null, EmailsTable = null, MessagesTable = null,
            AppointmentsTable = null, ticketsTableRoute = '';
        window.ClientActivitiesOldest = 100100100100;
        window.ClientActivitiesHasMore = true;
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';

            $('#TicketStatus').on('change', async function () {
                await fetchTicketsTable();
            });

            $(document).on('click', '.modal-show-image', function () {
                $(".modal-item").addClass('d-none');
                $('#showImageModal').removeClass('d-none');
                $('.modal-title').html('{{ $client->ClientID }} signature');
                $Modal.modal('show');
            });

            @if($call instanceof Call)
            durationTimer(document.getElementById("callTimer"), '{{ $call->StartOn->toDateTimeString() }}')

            $('form#OngoingCallForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#OngoingCallBtn'), true, false, true);
            });

            @elseif($meeting instanceof Meeting)
            durationTimer(document.getElementById("meetingTimer"), '{{ $meeting->StartOn->toDateTimeString() }}')

            $('form#OngoingMeetingForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#OngoingMeetingBtn'), true, false, true);
            });

            $('#ongoing_meeting_users').select2({
                placeholder: "Select other attendees ...", minimumInputLength: 2,
                ajax: {
                    url: '{{route('users.select2',['filter_current'=>'rzr.co.ke'])}}',
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

            @foreach($meeting?->users as $attendee)
            $("#ongoing_meeting_users option[value='{{ $attendee->UserID }}']").prop("selected", true).trigger("change")
            @endforeach
            @elseif($schedule instanceof \App\Models\CRM\Schedule)
            $(document).on('click', '#triggerUnreachableBtn', function () {
                if (unreachable_start !== null) {
                    unreachable_start.destroy();
                }
                unreachable_start = flatpickr("#unreachable_start", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    minTime: "06:00",
                    minuteIncrement: 1,
                    defaultDate: moment().format('hh:mm'),
                    maxTime: moment().add(1, 'min').format('HH:mm'),
                });

                $(".modal-item").addClass('d-none');
                $('#CallUnreachableModal').removeClass('d-none');
                $('.modal-title').html('Mark call as unreachable');
                $Modal.modal('show');
            });
            $('form#CallUnreachableForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#CallUnreachableBtn'), false, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '#triggerStartCallBtn', function () {
                startACall();
            });

            $(document).on('click', '#triggerStartMeetingBtn', function () {
                startAMeeting();
            });

            $(document).on('click', '#triggerRescheduleBtn', function () {
                if (reschedule_start !== null) {
                    reschedule_start.destroy();
                }
                reschedule_start = flatpickr("#reschedule_start", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    minTime: "06:00",
                    minuteIncrement: 1,
                    allowInput: true,
                    defaultDate: moment().format('HH:mm'),
                    maxTime: moment().add(1, 'min').format('HH:mm'),
                });
                $(".modal-item").addClass('d-none');
                $('#CallRescheduleModal').removeClass('d-none');
                $('.modal-title').html('Disused to Reschedule with Client.');
                $Modal.modal('show');
            });
            $('form#CallRescheduleForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#CallRescheduleBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            flatpickr("#schedule_start", {
                enableTime: true,
                altInput: true,
                minDate: moment().add(1, 'hour').format('YYYY-MM-DD hh:mm'),
                defaultDate: moment().add(1, 'day').set({
                    hour: 8,
                    minute: 0,
                    second: 0,
                    millisecond: 0
                }).format('YYYY-MM-DD hh:mm'),
                altFormat: "F j, Y H:i",
                dateFormat: "Y-m-d H:i",
            });
            @else
            $(document).on('click', '#triggerStartCallBtn', function () {
                startACall();
            });
            $(document).on('click', '#triggerStartMeetingBtn', function () {
                startAMeeting();
            });
            @endif

            $('form#StartCallForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#StartCallBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });
            $('form#StartMeetingForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#StartMeetingBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            fetchActivities();
        });

        function startACall() {
            if (call_initiated === null) {
                /*//  call_initiated.setDate(moment().format('hh:mm'));
              }else{*/
                call_initiated = flatpickr("#call_initiated", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    minTime: "06:00",
                    minuteIncrement: 1,
                    defaultDate: moment().format('hh:mm'),
                    maxTime: moment().add(1, 'min').format('HH:mm'),
                });
            }


            $(".modal-item").addClass('d-none');
            $('#StartCallModal').removeClass('d-none');
            $('.modal-title').html('Start a call with Member: {{ $client->ClientID }}');
            $Modal.modal('show');
        }

        function startAMeeting() {
            if (meeting_initiated === null) {
                /*//  meeting_initiated.setDate(moment().format('hh:mm'));
              }else{*/
                meeting_initiated = flatpickr("#meeting_initiated", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    minTime: "06:00",
                    minuteIncrement: 1,
                    defaultDate: moment().format('hh:mm'),
                    maxTime: moment().add(1, 'min').format('HH:mm'),
                });

            }

            $(".modal-item").addClass('d-none');
            $('#StartMeetingModal').removeClass('d-none');
            $('.modal-title').html('Start a meeting with Member: {{ $client->ClientID }}');
            $Modal.modal('show');
        }

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
            if (callsTable === null) {
                callsTable = $('#callsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('client-calls.index',[$client->ClientID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "user",
                                sort: "NoteID",
                            }, name: 'NoteID', searchable: false
                        },
                        {data: 'CallStatusID', name: 'CallStatusID'},
                        {data: 'StartOn', name: 'StartOn'},
                        {data: 'EndOn', name: 'EndOn'},
                        {data: 'Duration', name: 'Duration', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>No Calls record found</p>"
                    }
                });

                callsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the notes.");
                    console.log(er);
                });
            } else {
                callsTable.ajax.reload();
            }
        }

        function fetchTicketsTable() {

            let url = '{{ route('client-tickets.index',[$client->ClientID]) }}' + '?_status=' + $('#TicketStatus').val();

            /* if ($.fn.DataTable.isDataTable('#ticketsTable')) {
                 $("#ticketsTable").destroy();
             }*/
            if (url !== ticketsTableRoute) {
                if (ticketsTable != null) {
                    ticketsTable.destroy();
                    ticketsTable = null;
                }
                ticketsTableRoute = url;
            }

            if (ticketsTable === null) {
                ticketsTable = $('#ticketsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[0, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: url,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'TicketID', name: 'TicketID'},
                        {data: 'category', name: 'category'},
                        {data: 'Title', name: 'Title'},
                        {data: 'Priority', name: 'Priority'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                    ], "oLanguage": {
                        "sEmptyTable": "no tickets under current filter, <a href='#' class='add-party-ticket-btn' data-action='{{ route('client-tickets.store',[$client->ClientID]) }}'>create one"
                    }
                });

                ticketsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading tickets.");
                    console.log(er);
                });
            } else {
                ticketsTable.ajax.reload();
            }
        }

        function fetchNotesTable() {
            if (notesTable === null) {
                notesTable = $('#notesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('client-notes.index',[$client->ClientID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "DT_RowIndex",
                                sort: "NoteID",
                            }, name: 'NoteID', searchable: false
                        },
                        {data: 'Notes', name: 'Notes'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>There are no notes found here</p>"
                    }
                });

                notesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the notes.");
                    console.log(er);
                });
            } else {
                notesTable.ajax.reload();
            }
        }

        function fetchScheduleTable() {
            if (scheduleTable === null) {
                scheduleTable = $('#scheduleTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('client-schedule.index',[$client->ClientID]) }}',
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
                        "sEmptyTable": "<p>There is no schedule found here.</p>"
                    }
                });

                scheduleTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the schedule.");
                    console.log(er);
                });
            } else {
                scheduleTable.ajax.reload();
            }
        }

        function fetchAppointmentsTable() {
            if (AppointmentsTable === null) {
                AppointmentsTable = $('#AppointmentsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('client-meetings.index',[$client->ClientID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Title', name: 'Title'},
                        {data: 'StartOn', name: 'StartOn'},
                        {data: 'EndOn', name: 'EndOn'},
                        {data: 'StatusID', name: 'StatusID'},
                    ], "oLanguage": {
                        "sEmptyTable": `<p>There are  no appointments/meetings found here.</p>`
                    }
                });

                AppointmentsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading meetings.");
                    console.log(er);
                });
            } else {
                AppointmentsTable.ajax.reload();
            }
        }

        function fetchDiscussionsTable() {
            if (discussionsTable === null) {
                discussionsTable = $('#discussionsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [1]}
                    ],
                    ajax: {
                        url: '{{ route('clients.discussions',[$client->ClientID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {
                            data: {
                                _: "DT_RowIndex",
                                sort: "DiscussionID",
                            }, name: 'DiscussionID', searchable: false
                        },
                        {data: 'SourceType', name: 'SourceType'},
                        {data: 'Discussion', name: 'Discussion'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<p>There are no discussions found here</p>"
                    }
                });

                discussionsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the client discussions.");
                    console.log(er);
                });
            } else {
                discussionsTable.ajax.reload();
            }
        }

        function fetchAccountsTable() {
            if (accountsTable === null) {
                accountsTable = $('#accountsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: document.URL,
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        /*  {data: 'OurBranchID', name: 'OurBranchID'},*/
                        {
                            data: 'product.Description',
                            "mRender": function (data, type, full) {
                                if (full.product.ProductID) {
                                    return full.product.ProductID + ' - ' + full.product.Description;
                                }
                                return full.ProductID.toString().toUpperCase();
                            }

                        },
                        {data: 'AccountID', name: 'AccountID'},

                        /*{data: 'ProductID', name: 'ProductID'},*/
                        {data: 'ClearBalance', name: 'ClearBalance'},
                        {
                            data: 'status.Description',
                            "mRender": function (data, type, full) {
                                return full.status.Description;
                            }
                        },
                        /* {data: 'AccountStatusID', name: 'AccountStatusID'},*/
                        {data: 'LastCreditTrxDate', name: 'LastCreditTrxDate'},

                        /* {data: 'accounts_count', name: 'accounts_count', orderable: false, searchable: false},*/
                    ], "oLanguage": {
                        "sEmptyTable": "<div class='text-center'><img class='img-fluid' style='height:30vh' src='{{ asset('assets/img/errors/404.svg') }}' alt='?'></div>"
                    }
                });

                accountsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the client portfolio.");
                    console.log(er);
                });
            } else {
                accountsTable.ajax.reload();
            }
        }

        function fetchRelationsTable() {
            if (relationsTable === null) {
                relationsTable = $('#relationsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('clients.relations',[$client->ClientID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'ClientID', name: 'ClientID'},
                        {data: 'Name', name: 'Name'},
                        {data: 'relation.Description', name: 'relation.Description'},
                        {data: 'type.Description', name: 'type.Description'},
                        {data: 'Mobile', name: 'Mobile'},
                        {data: 'status.Description', name: 'status.Description'},
                    ], "oLanguage": {
                        "sEmptyTable": "<p class='text-center'>No relations found</p>"
                    }
                });

                relationsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the relations list.");
                    console.log(er);
                });
            } else {
                relationsTable.ajax.reload();
            }
        }

        function fetchMailsTable() {
            if (EmailsTable === null) {
                EmailsTable = $('#EmailsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    ajax: {
                        url: '{{ route('client-mail.index', [$client->ClientID]) }}',
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
                        "sEmptyTable": "<span class='text-center'>No records found</span>"
                    }
                });

                EmailsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading emails.");
                });
            } else {
                EmailsTable.ajax.reload();
            }
        }

        function fetchSMSTable() {
            if (MessagesTable === null) {
                MessagesTable = $('#MessagesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    ajax: {
                        url: '{{ route('client-sms.index', [$client->ClientID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "Type", name: 'Type'},
                        {data: 'source', name: 'source', orderable: false, searchable: false},
                        {data: 'Dated', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "<span class='text-center'>No records found</span>"
                    }
                });

                MessagesTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the messages.");
                });
            } else {
                MessagesTable.ajax.reload();
            }

        }

        async function fetchActivities() {
            if (window.ClientActivitiesHasMore) {
                await $.get('{{ route('clients.activities',[$client->ClientID]) }}?last_view=' + window.ClientActivitiesOldest, function (data) {
                        $("#activitiesMain").append(data);
                    }
                ).fail(function () {
                    nError('fetching activities failed');
                });
            }
            if (!window.ClientActivitiesHasMore) {
                $("#loadMoreBtn").addClass('d-none');
            } else {
                $("#loadMoreBtn").removeClass('d-none');
            }
        }

        function appendActivity(activity) {
            window.LeadActivitiesOldest = parseInt(activity.id);
            $("#activitiesMain").prepend(activity.html);
        }

        function fetchReviewsTable() {
            if (!$.fn.DataTable.isDataTable('#reviewsTable')) {
                $('#reviewsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[4, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('client.feedbacks', [$client->ClientID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Rating', name: 'Rating'},
                        {data: 'Tonality', name: 'Tonality'},
                        {data: 'Source', name: 'Source'},
                        {data: 'CreatedOn', name: 'CreatedOn'}
                    ], "oLanguage": {
                        "sEmptyTable": "No reviews under this filter."
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading the reviews.");
                    console.log(er);
                });
            } else {
                $('#reviewsTable').DataTable().ajax.reload();
            }
        }

        function fetchContactsTable() {
            if (!$.fn.DataTable.isDataTable('#contactsTable')) {
                $('#contactsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('client-contacts.index',[$client->ClientID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'Label', name: 'Label'},
                        {data: 'Phone', name: 'Phone'},
                        {data: 'Email', name: 'Email'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no contact under current filter, <a  href='javascript: void(0)' class='click-summary-data' data-click_url='{{ route('client-contacts.create',[$client->ClientID]) }}' data-summary_title='Add Contact'>create one</a>"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading client contacts.");
                    // console.log(er);
                });
            } else {
                $('#contactsTable').DataTable().ajax.reload();
            }
        }
    </script>
@endsection
