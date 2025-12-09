@php use App\Models\Auth\User; @endphp
@php use App\Helpers\SystemHelper; @endphp
@php use App\Models\CRM\Discussion; @endphp
@php use App\Enums\LeadTypeEnum; @endphp
@php use App\Enums\CallStatusEnum; @endphp
@php use Carbon\Carbon; @endphp
@php use App\Http\Requests\Call\StartCallRequest; @endphp
@php use App\Http\Requests\Call\StartMeetingRequest; @endphp
@php use App\Enums\Core\RoleEnum; @endphp
@php use App\Enums\LeadStatusEnum; @endphp
@php use App\Enums\LocalityTypeEnum; @endphp
@php use App\Models\Communication\Call; @endphp
@php use App\Models\CRM\Meeting; @endphp
@extends('layouts.app')

@section('title')
    {{ \Illuminate\Support\Str::padLeft($lead->LeadID,5,'0') }}
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
    <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
@endsection
@section('content')
   <div class="row">
        <div class="col-md-4 col-xxl-3">
            <div class="card">
                @if($won)
                    <h6 class="text-center mt-2">{{ $lead->Status->description() }}</h6>
                @else
                    <form class="card-body" id="leadStatusForm" action="{{ route('leads.status',[$lead->LeadID]) }}">
                        @csrf
                        <div class="m-0 p-0 row">@method('put')
                            <div class="col-12">
                                <p class="d-none" id="StatusMsg"></p>
                                <select class="form-control text-center" name="Status" id="Status" required>
                                    @foreach(App\Enums\LeadStatusEnum::cases() as $status)
                                        <option
                                            value="{{ $status->value }}" {{ ($status->value===$lead->Status->value)?'selected':'' }}>{{ $status->description() }}</option>
                                    @endforeach
                                </select>
                                <p id="Status_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                            </div>
                        </div>
                    </form>
                @endif
                <hr class="my-0">
                <div class="card-body mx-1 mb-0 mt-1">
                    @include('snippets.lead_summary', ['lead'=>$lead, 'show_summary'=>true])
                    <hr>
                    <h5 class="h6 card-title">
                        Contacts
                        @if(!$won)
                            <a href="#" class="float-end trigger-update-lead-modal">
                                <i class="fas fa-edit"></i>
                            </a>
                        @endif
                    </h5>

                    <div class="text-start">
                        <div class="btn-group">
                            <button type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    class="btn btn-link dropdown-toggle text-break text-start">
                                {{ $lead->Phone }}
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item disabled text-decoration-line-through"
                                   href="javascript:void(0)">
                                    <i class="fas fa-phone-alt"></i> Call
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item send-message-to-action" href="javascript:void(0)"
                                   data-info="{{ route('lead-sms.store', [$lead->LeadID]) }}~{{ $lead->Name }}~{{ $lead->Phone }}">
                                    <i class="fas fa-message"></i> Message
                                </a>
                            </div>
                        </div>

                        @if(!empty($lead->Email))
                            <div class="d-block mt-2">
                                <a href="javascript:void(0)"
                                   data-info="{{ route('lead-mail.store', [$lead->LeadID]) }}~{{ $lead->Name }}~{{ $lead->Email }}"
                                   class="btn btn-link text-break send-mail-to-action w-100 text-start">
                                    {{ $lead->Email }}
                                </a>
                            </div>
                        @endif
                    </div>

                </div>
                <hr class="my-0">
                <div class="card-body">
                    <h5 class="h6 card-title">Marketing Lists
                        @if(!$won)
                            <a href="#" class="float-end click-summary-data"
                               data-click_url="{{ route('lead-marketing-lists.index',$lead->LeadID) }}"
                               data-summary_title="Marketing Lists"><i
                                    class="fas fa-edit"></i></a>
                        @endif
                    </h5>
                    @foreach($MarketingListMember as $list)
                        <a href="{{ route('marketing-list.show',[$list->slug]) }}"
                           class="btn btn-pill btn-secondary btn-sm">{{ $list->Label }}</a>
                    @endforeach
                </div>
                <hr class="my-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><b>Location</b><span class="float-end">{{ $location }} </span></li>
                    <li class="list-group-item"><b>Industry</b><span
                            class="float-end">{{ $lead->industry?->Description }} </span></li>
                    <li class="list-group-item"><b>Source</b><span
                            class="float-end">{{ $lead->source?->Description }} </span></li>
                    <li class="list-group-item"><b>Customer Type</b><span
                            class="float-end">{{ $lead->customerType?->Description }} </span></li>
                    @if(!empty($lead->Website))
                        <li class="list-group-item"><b>Website </b><a href="{{ $lead->Website }}"
                                                                      class="float-end"> {{ $lead->Website }} </a></li>
                    @endif
                    @if(!empty($lead->JobTitle))
                        <li class="list-group-item"><b>Job Title </b><span
                                class="float-end"> {{ $lead->JobTitle }} </span></li>
                    @endif

                    <li class="list-group-item"><b>Introducer </b>
                        <details class="float-end">
                            <summary>{{ $lead->creator?->UserID }} </summary>
                            <p>{{ $lead->creator?->Name }}</p>
                        </details>
                    </li>
                    <li class="list-group-item"><b>Last Contacted </b><span
                            class="float-end">{{ $lead->LastContacted?->format('d M, Y h:i A') }}</span></li>
                    @if($won)
                        <li class="list-group-item"><b>Won On </b><span
                                class="float-end">{{ $lead->CreatedOn?->format('d M, Y h:i A') }}</span></li>
                    @endif
                </ul>
                <p class="p-2 mt-3"><b>Notes</b><br>
                    {{ $lead->Notes }}
                </p>
            </div>
        </div>
        <div class="col-md-8 col-xxl-9">
            <div class="row">
                @if(!$won)
                    <div class="col-lg-6 col-12">
                        <div class="card">
                            <div class="card-body d-flex align-items-start row p-3">
                                <div class="col-4">
                                    <button class="btn btn-outline-primary text-center w-100 add-party-notes-btn"
                                            type="button"
                                            data-action="{{ route('lead-notes.store',[$lead->LeadID]) }}"><i
                                            class="fas fa-plus"></i> <br> note
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-primary text-center w-100 add-party-appointment-btn"
                                            type="button"
                                            data-action="{{ route('lead-schedule.meeting',[$lead->LeadID]) }}">
                                        <i class="fas fa-calendar-plus"></i> <br> appointment
                                    </button>
                                </div>
                                <div class="col-4">
                                    <div class="btn-group w-100">
                                        <button type="button"
                                                class="btn btn-outline-primary text-center dropdown-toggle"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-calendar-plus"></i> <br> others
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item add-party-scheduled-call-btn"
                                                   href="javascript:void(0)"
                                                   data-action="{{ route('lead-schedule.call',[$lead->LeadID]) }}"> <i
                                                        class="align-middle" data-feather="phone-forwarded"></i>
                                                    schedule a
                                                    call</a></li>
                                            <li><a class="dropdown-item create-new-task " href="javascript:void(0)"
                                                   data-action="{{ route('lead-tasks.store',[$lead->LeadID]) }}"> <i
                                                        class="fa-solid fa-list-check"></i> create a task</a></li>
                                            {{--  <li><a class="dropdown-item text-muted disabled text-decoration-line-through"
                                                     href="#"><i
                                                          class="fas fa-walking"></i> start a meeting</a></li>
                                              <li><a class="dropdown-item text-muted disabled text-decoration-line-through"
                                                     href="#"><i class="align-middle" data-feather="phone-outgoing"></i> start
                                                      a call</a></li>--}}
                                            <li><a class="dropdown-item" id="triggerStartMeetingBtn"
                                                   href="javascript:void(0);">
                                                    <i class="fas fa-walking"></i> start unscheduled meeting</a></li>
                                            <li><a class="dropdown-item" id="triggerStartCallBtn"
                                                   href="javascript:void(0);"><i class="align-middle"
                                                                                 data-feather="phone-outgoing"></i>
                                                    start
                                                    unscheduled call</a></li>
                                            <li><a class="dropdown-item add-party-ticket-btn" href="javascript:void(0)"
                                                   data-action="{{ route('lead-tickets.store',[$lead->LeadID]) }}">
                                                    <i class="align-middle" data-feather="check-square"></i> add a
                                                    ticket
                                                </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-6 col-12">
                    <div class="card">
                        <div class="card-header m-0 p-1 border-bottom border-1">Relationship Officer
                            @if(!$won)
                                <button class="btn btn-sm btn-primary float-end trigger-reassign-lead-modal"><i
                                        class="fas fa-edit"></i> reassign
                                </button>
                            @endif
                        </div>
                        <div class="card-body align-items-start py-1 px-3 row">
                            @if($lead->RelationshipManager instanceof  User && ($lead->RelationshipManager->UserID !== SystemHelper::ID) && (!$lead->RelationshipManager->trashed()))
                                <div class="col-4">
                                    {!! $lead->RelationshipManager->getImage('width="42" height="42" class="rounded-circle me-2" alt=".."') !!}
                                </div>
                                <div class="col-8">
                                    <p class="mb-1">{{ $lead->RelationshipManager->Name }}</p>
                                    <p class="mb-1 fw-bold">{{ $lead->RelationshipManager->UserID }}</p>
                                </div>
                            @else
                                <div class="col-12">
                                    <h3 class="text-center my-3 text-danger">Not Assigned</h3>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                    @if($call instanceof Call)
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body row ">
                                <div class="col-md-4 col-12 text-center">
                                    Start <br> <b>{{ $call->StartOn?->format('M d, Y h:i a') }}</b>
                                </div>
                                <div class="col-md-4 col-12 text-center">
                                    Timer <br><b id="callTimer"></b>
                                </div>
                                <div class="col-md-4 col-12 text-center">
                                    Plan End <br> <b>{{ $call->EndOn?->diffInMinutes($call->StartOn,true) }} min</b>
                                </div>
                                @if($schedule instanceof \App\Models\CRM\Schedule)
                                    <div class="col-12"><b>Notes</b> <br>{{ $schedule->Notes }}</div>
                                @endif
                                <div class="col-12">
                                    <hr>
                                </div>
                                <form action="{{ route('lead-calls.update',[$lead->LeadID, $call->CallID]) }}"
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
                                    action="{{ route('lead-meetings.update',[$lead->LeadID, $meeting->MeetingID]) }}"
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
                                        Start <br> <b>{{ $schedule->StartOn?->format('M d, Y h:i a') }}</b>
                                    </div>
                                    <div class="col-md-4 col-12 text-center">
                                        End <br><b> {{ $schedule->EndOn?->format('M d, Y h:i a') }}</b>
                                    </div>
                                    <div class="col-md-4 col-12 text-center">
                                        Duration <br> <b>{{ $schedule->EndOn?->diffInMinutes($schedule->StartOn,true) }}
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
                                        Start <br> <b>{{ $schedule->StartOn?->format('M d, Y h:i a') }}</b>
                                    </div>
                                    <div class="col-md-4 col-12 text-center">
                                        End <br><b> {{ $schedule->EndOn?->format('M d, Y h:i a') }}</b>
                                    </div>
                                    <div class="col-md-4 col-12 text-center">
                                        Duration <br> <b>{{ $schedule->EndOn?->diffInMinutes($schedule->StartOn,true) }}
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

            <div class="card">
                <div class="card-body py-0">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" href="#tab-0" data-bs-toggle="tab" role="tab"
                                                aria-selected="false">Activities</a></li>
                        <li class="nav-item"><a class="nav-link " href="#tab-7" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchTasksTable()">Open Tasks</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-6" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchScheduleTable()">Schedule</a></li>
                        <li class="nav-item"><a class="nav-link " href="#tab-8" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchContactsTable()">Contacts</a></li>
                        <li class="nav-item"><a class="nav-link " href="#tab-2" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchProductsTable()">Product
                                Interested</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#tab-4" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchDiscussionsTable()">Discussions</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#tab-5" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchCallsTable()">Calls</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-3" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchNotesTable()">Private Notes</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-9" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchTicketsTable()">Tickets</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-10" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchMailsTable()">Emails</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-11" data-bs-toggle="tab" role="tab"
                                                aria-selected="false" onclick="fetchSMSTable()">Messages</a></li>
                        <li class="nav-item"><a class="nav-link" href="#tab-12" data-bs-toggle="tab"
                                                role="tab" aria-selected="false" onclick="fetchWatchersTable()"
                            >users & teams</a></li>
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
                <div class="tab-pane m-2" id="tab-2" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6 col-12"><h5>Product Interested</h5></div>
                                <div class="col-md-6 col-12">
                                    <div class="float-end">
                                        <button class="btn btn-primary add-lead-product" type="button"><i
                                                class="fas fa-plus-circle"></i> add product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="productsTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Dated</th>
                                    <th>action</th>
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
                        <div class="card-header"><h5>Discussions</h5></div>
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
                        <div class="card-header"><h5>Calls <small>Incoming & Outgoing</small></h5></div>
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
                        <div class="card-header"><h5>Schedule <small>Appointments & Calls</small></h5></div>
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
                        <div class="card-header"><h5>Task associated</h5></div>
                        <div class="card-body">
                            <table id="tasksTable"
                                   class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="w-50">Task</th>
                                    <th>Due On</th>
                                    <th>actions</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane m-2" id="tab-8" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6 col-12"><h5>Contacts <small>Other Contacts</small></h5></div>
                                <div class="col-md-6 col-12">
                                    <div class="float-end">
                                        <button class="btn btn-primary click-summary-data" type="button"
                                                data-click_url="{{ route('lead-contacts.create',[$lead->LeadID]) }}"
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

                <div class="tab-pane m-2" id="tab-9" role="tabpanel">

                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6 col-12">

                                </div>
                                <div class="col-md-6 col-12">
                                    <button type="button" class="btn btn-primary add-party-ticket-btn"
                                            data-action="{{ route('lead-tickets.store',[$lead->LeadID]) }}">
                                        <i class="align-middle" data-feather="check-square"></i> add a ticket
                                    </button>
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
                <div class="tab-pane m-2" id="tab-10" role="tabpanel">
                    <div class="card">
                        <div class="card-header"><h5>Emails <small>Incoming & outgoing</small></h5></div>
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
                            {{-- Email Details Modal --}}
                            <div class="modal fade" id="viewEmailModal" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Email Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" id="emailDetailsContent">
                                            <div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="tab-pane m-2" id="tab-11" role="tabpanel">
                    <div class="card">
                        <div class="card-header"><h5>SMS Messages <small>Incoming & Outgoing</small></h5></div>
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
                <div class="tab-pane m-0" id="tab-12" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6 col-12">
                                    <h5>Users and Teams Permissions</h5>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="float-end">
                                        <button type="button" class="btn btn-primary btn-sm  add-watcher-btn">
                                            <i class="align-middle" data-feather="share-2"></i> share
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="leadWatchersTable"
                                   class="table table-striped no-footer dtr-inline w-100 table-responsive">
                                <thead>
                                <tr>
                                    <th>No.</th>
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
        </div>
    </div>
    @if(!$won)
        <div class="modal fade" id="leadActionsModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">..</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="onboarding-content with-gradient d-none modal-item" id="addLeadProductModal">
                            <form action="{{ route('lead-products.store',[$lead->LeadID]) }}" method="post"
                                  id="addLeadProductForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="lead_product" class="form-label">Product <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control " name="lead_product" id="lead_product">
                                    </select>
                                    <p id="lead_product_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="product_notes">Notes </label>
                                    <textarea name="product_notes" id="product_notes" rows="3" class="form-control"
                                              maxlength="1000"></textarea>
                                    <p id="product_notes_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="addLeadProductBtn" type="submit"><i
                                            class="fas fa-save"></i> add product
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item" id="statusLeadLossModal">
                            <form action="{{ route('leads.status',[$lead->LeadID]) }}" method="post"
                                  id="statusLeadLossForm">
                                @csrf
                                <div class="mb-3">@method('put')
                                    <label for="e_Status" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control text-center" name="Status" id="e_Status" required
                                            readonly="">
                                        <option value="{{ LeadStatusEnum::Cold->value }}"
                                                selected>{{ LeadStatusEnum::Cold->description() }}</option>
                                    </select>
                                    <p id="e_Status_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>

                                <div class="mb-3">
                                    <label for="LossReason" class="form-label">LossReason <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" name="LossReason" id="LossReason" required>
                                        <option selected disabled>Select an Option</option>
                                        @foreach($LossReasons as $reason)
                                            <option value="{{ $reason->ID }}">{{ $reason->Description }}</option>
                                        @endforeach
                                    </select>
                                    <p id="LossReason_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="statusLeadLossBtn" type="submit"><i
                                            class="fas fa-save"></i> change status
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item" id="updateLeadModal">
                            <div class="progress mb-3 avatar-change d-none">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                     role="progressbar" id="progress-bar" style="width: 0" aria-valuenow="0"
                                     aria-valuemin="0" aria-valuemax="100"><small class="sr-only">0%
                                        Complete</small></div>
                            </div>
                            <form action="{{ route('leads.update',[$lead->LeadID]) }}" method="post" id="updateLeadForm"
                                  enctype="multipart/form-data"> @csrf
                                <div class="row">@method('put')
                                    <div class="col-sm-6 col-12  mb-3 text-center">
                                        <input type="hidden" name="Type" class="d-none"
                                               value="{{ $lead->Type->value }}">
                                        <input type="file" name="image" class="d-none" accept="image/*"
                                               style="display: none;" id="Upload_image">
                                        <label for="Upload_image">
                                            {!! $lead->getImage('id="image_upload_preview" alt=".." class="img-fluid img-thumbnail mb-2"
                                                 width="200" height="200"',true) !!}</label>
                                        <p id="image_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="col-sm-6 col-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="Phone">Phone Number <span
                                                    class="text-danger">* &nbsp; <span id="PhonePrefix"></span></span>
                                            </label>
                                            <input type="text" class="form-control" id="Phone" name="Phone"
                                                   placeholder="Phone Number" required value="{{ $lead->Phone }}">
                                            <p id="Phone_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="Email">Email </label>
                                            <input type="text" class="form-control" id="Email" name="Email"
                                                   placeholder="Email" value="{{ $lead->Email }}">
                                            <p id="Email_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>

                                    </div>
                                    <div class="col-sm-6 col-12 mb-3">
                                        <label for="Country" class="form-label">Country <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" name="Country" id="Country" required>
                                            @foreach($Countries as $Country)
                                                <option value="{{ $Country->CountryCode }}"
                                                        {{ ($Country->Id === $lead->CountryId)?'selected':'' }} data-phone="{{$Country->PhoneCode}}"
                                                        data-location="{{ route('locality.select2',['country'=>$Country->CountryCode]) }}">{{ $Country->Flag}} {{ $Country->Name}}</option>
                                            @endforeach
                                        </select>
                                        <p id="Country_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="col-sm-6 col-12 mb-3">
                                        <label for="Location" class="form-label">Location <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control locations" name="Location" id="Location"
                                                required disabled>
                                            <option selected
                                                    value="{{ $lead->LocationID }}">{{ $location }}</option>
                                        </select>
                                        <p id="Location_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="col-sm-6 col-12 mb-3">
                                        <label class="form-label" for="Name">Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="Name" name="Name" required
                                               placeholder="Name" value="{{ $lead->Name }}">
                                        <p id="Name_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    @if($lead->Type->value === LeadTypeEnum::Individual->value)
                                        <div class="col-sm-6 col-12 mb-3">
                                            <label class="form-label" for="Surname">Surname <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="Surname" name="Surname" required
                                                   placeholder="Surname" value="{{ $lead->OtherNames }}">
                                            <p id="Surname_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-sm-6 col-12 mb-3">
                                            <label class="form-label" for="JobTitle">Job Title </label>
                                            <input type="text" class="form-control" id="JobTitle" name="JobTitle"
                                                   required
                                                   placeholder="Job Title eg Sole Proprietor"
                                                   value="{{ $lead->JobTitle }}">
                                            <p id="JobTitle_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                        <div class="col-sm-6 col-12 mb-3">
                                            <label for="Gender" class="form-label">Gender <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control" name="Gender" id="Gender" required>
                                                @foreach(App\Enums\Employee\GenderEnum::getAll() as $gender)
                                                    <option value="{{ $gender->value }}">{{ $gender->name }}</option>
                                                @endforeach
                                            </select>
                                            <p id="Gender_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                    @elseif($lead->Type->value === LeadTypeEnum::Company->value)
                                        <div class="col-sm-6 col-12 mb-3">
                                            <label class="form-label" for="Website">Website </label>
                                            <input type="url" class="form-control" id="Website" name="Website"
                                                   placeholder="https://example.com"
                                                   value="{{ $lead->Website }}">
                                            <p id="Website_error" class="invalid-feedback d-none error col-12"
                                               role="alert"></p>
                                        </div>
                                    @endif
                                    <div class="col-sm-6 col-12 mb-3">
                                        <label for="Industry" class="form-label">Industry <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" name="Industry" id="Industry" required>
                                            @foreach($Industries as $Industry)
                                                <option
                                                    value="{{ $Industry->ID }}" {{ ($Industry->ID===(integer)$lead->Industry)?'selected':'' }} >{{ $Industry->Description }}</option>
                                            @endforeach
                                        </select>
                                        <p id="Industry_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="col-sm-6 col-12 mb-3">
                                        <label for="Source" class="form-label">Source <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" name="Source" id="Source" required>
                                            @foreach($MarketingModes as $Source)
                                                <option
                                                    value="{{ $Source->ID }}" {{ ($Source->ID===$lead->Source)?'selected':'' }}>{{ $Source->Description }}</option>
                                            @endforeach
                                        </select>
                                        <p id="Source_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="col-sm-6 col-12 mb-3">
                                        <label for="CustomerType" class="form-label">Customer Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" name="CustomerType" id="CustomerType" required>
                                            @foreach($CustomerTypes as $CustomerType)
                                                <option
                                                    value="{{ $CustomerType->ID }}" {{ ($CustomerType->ID===$lead->CustomerType)?'selected':'' }}>{{ $CustomerType->Description }}</option>
                                            @endforeach
                                        </select>
                                        <p id="CustomerType_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="Notes">Notes </label>
                                        <textarea name="Notes" id="Notes" rows="3"
                                                  class="form-control">{{ $lead->Notes }}</textarea>
                                        <p id="Notes_end_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-primary float-end" id="updateLeadBtn" type="submit"><i
                                            class="fas fa-save"></i>
                                        update {{ \Illuminate\Support\Str::limit($lead->Name,20) }}
                                    </button>
                                </div>
                            </form>
                        </div>
                        @if($schedule instanceof \App\Models\CRM\Schedule)
                            <div class="onboarding-content with-gradient d-none modal-item" id="CallUnreachableModal">
                                <form action="{{ route('call.unreachable',[$schedule->ScheduleID]) }}" method="post"
                                      id="CallUnreachableForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Type <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" required name="type" id="type">
                                            <option disabled selected>Select a Type</option>
                                            @foreach(CallStatusEnum::unreachable() as $option)
                                                <option
                                                    value="{{ $option->value }}">{{ $option->description() }}</option>
                                            @endforeach
                                        </select>
                                        <p id="type_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="unreachable_start">Called Time <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control flatpickr-datetime"
                                               id="unreachable_start"
                                               name="unreachable_start" required placeholder="Select start..">
                                        <p id="unreachable_start_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="unreachable_comment">Comment </label>
                                        <textarea name="unreachable_comment" id="unreachable_comment"
                                                  class="form-control"
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
                                        <button class="btn btn-warning float-end" id="CallUnreachableBtn" type="submit">
                                            <i
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
                                        <label class="form-label" for="schedule_start">Next Start <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control flatpickr-datetime"
                                               value="{{ Carbon::now()->addDay()->setHour(8)->setMinute(0)->format('Y-m-d H:i') }}"
                                               id="schedule_start" name="schedule_start" placeholder="Select start..">
                                        <p id="schedule_start_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="schedule_discussion">Discussion <span
                                                class="text-danger">*</span> </label>
                                        <textarea name="schedule_discussion" id="schedule_discussion"
                                                  class="form-control"
                                                  rows="2" maxlength="5000" minlength="5">Client requested to be called later.</textarea>
                                        <p id="schedule_discussion_error" class="invalid-feedback d-none error col-12"
                                           role="alert"></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="schedule_notes">Private Notes </label>
                                        <textarea name="schedule_notes" id="schedule_notes" class="form-control"
                                                  rows="2"
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
                            <form action="{{ route('lead-calls.store',[$lead->LeadID]) }}" method="post"
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
                            <form action="{{ route('lead-meetings.store',[$lead->LeadID]) }}" method="post"
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
                                <input type="hidden" class="d-none" id="meeting_schedule" name="meeting_schedule"
                                       readonly
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
                        <div class="onboarding-content text-center with-gradient d-none modal-item"
                             id="trashLeadWatcherModal">
                            <h3 class="h3 text-danger">Remove Watcher <b id="trashLeadWatcher"></b>
                                from L{{ $lead->LeadID }}
                            </h3>
                            <div class="mt-2 mb-2">
                                Are you sure you want to remove this watcher ?
                            </div>
                            <hr>
                            <form id="trashLeadWatcherForm" method="post"> @csrf
                                <div class="mt-4">@method('delete')
                                    <button type="button" class="btn btn-success float-start"
                                            data-bs-dismiss="modal">
                                        no, keep
                                    </button>
                                    <button class="btn btn-danger float-end" id="trashLeadWatcherBtn"
                                            type="submit"><i
                                            class="fas fa-trash"></i> yes, remove
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item" id="addLeadWatcherModal">
                            <form action="{{ route('lead-watchers.store',[$lead->LeadID]) }}" method="post"
                                  id="addLeadWatcherForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="share_role" class="form-label">Role <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control " name="share_role" id="share_role" required>
                                        <option selected disabled>select a role.</option>
                                        @foreach(RoleEnum::getAll() as $role)
                                            <option value="{{ $role->value }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    <p id="share_role_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label for="share_party" class="form-label">User/Team </label>
                                    <select class="form-control" name="share_party" id="share_party" required>
                                    </select>
                                    <p id="share_party_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <hr>
                                <div class="mt-4">
                                    <button type="button" class="btn btn-secondary float-start"
                                            data-bs-dismiss="modal">
                                        cancel
                                    </button>
                                    <button class="btn btn-info float-end" id="addLeadWatcherBtn" type="submit"><i
                                            class="fas fa-share-alt"></i> share
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="onboarding-content with-gradient d-none modal-item" id="reassignLeadModal">
                            <form action="{{ route('lead.reassign',[$lead->LeadID]) }}" method="post"
                                  id="reassignLeadForm"> @method('PUT')
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
                                    <button class="btn btn-primary float-end" id="reassignLeadBtn" type="submit"><i
                                            class="fas fa-shuffle"></i> reassign
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('scripts')
    @if(!$won)
        @include('snippets.actions.tasks')
        @include('snippets.actions.tickets')
        @include('snippets.actions.notes')
        @include('snippets.actions.schedule')
    @endif
    @include('snippets.actions.mailto')
    @include('snippets.actions.sms')
    <script src="{{asset('assets/libs/jquery-form/jquery.form.min.js')}}"></script>
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>

    <script>const $Modal = $('#leadActionsModal');
        let productsTable = null, relationsTable = null, callsTable = null, discussionsTable = null, notesTable = null,
            tasksTable = null, contactsTable = null, ticketsTable = null, EmailsTable = null, MessagesTable = null,
            scheduleTable = null, unreachable_start = null, reschedule_start = null,
            call_initiated = null, meeting_initiated = null, AppointmentsTable = null;
        window.LeadActivitiesOldest = 100100100100;
        window.LeadActivitiesHasMore = true;
        window.currentStatus = '{{ $lead->Status->value }}'
        $(function () {
            $.fn.dataTable.ext.errMode = 'none';
            fetchActivities();

            $('#share_party').select2({
                placeholder: "Search a user or team (t:)", minimumInputLength: 2,
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

            $(document).on('click', '.add-watcher-btn', function () {
                $(".modal-item").addClass('d-none');
                $('#addLeadWatcherModal').removeClass('d-none');
                $('.modal-title').html('<b>Share</b> lead ');
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addLeadWatcherForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addLeadWatcherBtn'), false, true, true)) {
                    fetchWatchersTable();
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.lead-watchers-trash', function () {
                const name = $(this).data('info');
                $(".modal-item").addClass('d-none');
                $("#trashLeadWatcherForm").attr('action', $(this).data('click_url'));
                $('#trashLeadWatcher').html(name);
                $('#trashLeadWatcherModal').removeClass('d-none');
                $('.modal-title').html('<b>Remove</b> lead watcher : ' + name);
                $Modal.children().first().removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#trashLeadWatcherForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#trashLeadWatcherBtn'), false, true, true)) {
                    fetchWatchersTable();
                    $Modal.modal('hide');
                }
            });

            $('#Status').on('change', async function () {
                if (this.value === window.currentStatus) {
                    return;
                }
                switch (this.value) {
                    case '{{ LeadStatusEnum::Hot->value }}':
                    case '{{ LeadStatusEnum::Warm->value }}':
                        let msg = $('#StatusMsg');
                        msg.removeClass('d-none');
                        $('.form-control').addClass('disabled');
                        if (!await saveForm($("#leadStatusForm"), msg, true, false, true)) {
                            $('.form-control').addClass('disabled');
                            $('#Status').val(window.currentStatus).change();
                        }
                        break;
                    case '{{ LeadStatusEnum::Won->value }}':
                        showOffCanvasMain('Lead -> Client (won)', '{{ route('leads.edit',[$lead->LeadID]) }}');
                        $('#Status').val(window.currentStatus).change();
                        break;
                    case '{{ LeadStatusEnum::Cold->value }}':
                        $(".modal-item").addClass('d-none');
                        $('#statusLeadLossModal').removeClass('d-none');
                        $('.modal-title').html('change lead status.');
                        $('.modal-dialog').removeClass('modal-lg');
                        $('#Status').val(window.currentStatus).change();
                        $Modal.modal('show');
                        break;
                    default:
                        $('#Status').val(window.currentStatus).change();
                        nWarning('unknown status set');
                }
            });
            $('form#statusLeadLossForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#statusLeadLossBtn'), true, true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $('#Country').val('{{ $lead->country->CountryCode }}').change().select2({
                placeholder: "Select a Country",
                dropdownParent: $Modal
            }).on('change', function () {
                const option = $(this).find('option:selected');
                $('#PhonePrefix').html(option.data('phone'));
                $('#Location').prop('disabled', false).select2('destroy').val(null).select2({
                    placeholder: "Search for the Location",
                    minimumInputLength: 2,
                    dropdownParent: $Modal,
                    ajax: {
                        url: option.data('location'),
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {q: $.trim(params.term)};
                        },
                        processResults: function (data) {
                            return {
                                results: $.map(data, function (item) {
                                    return {text: item.Name, id: item.ID}
                                })
                            };
                        },
                        cache: true
                    }
                });
            });
            $('#Location').select2();

            {{--   $('#Location').select2({
                placeholder: "Select a Town/City", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: "{ { route('locality.select2') }}?type={ { LocalityTypeEnum::City->value }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {text: item.Name, id: item.ID}
                            })
                        };
                    },
                    cache: true
                }
            }); --}}
            $("#Upload_image").change(function () {
                $('.avatar-change').removeClass('d-none');
                $('.avatar-changed').addClass('d-none');
                readURL(this);
            });

            $(document).on('click', '.trigger-reassign-lead-modal', function () {
                $(".modal-item").addClass('d-none');
                $('#reassignLeadModal').removeClass('d-none');
                $('.modal-title').html('<b class="text-warning fw-bold ">RE ASSIGN</b> lead ');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#reassignLeadForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#reassignLeadBtn'), true, true, true)) {
                    $Modal.modal('hide');
                }
            });

            $(document).on('click', '.trigger-update-lead-modal', function () {
                $(".modal-item").addClass('d-none');
                $('#updateLeadModal').removeClass('d-none');
                $('.modal-title').html('update lead');
                $('.modal-dialog').addClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#updateLeadForm').submit(function (e) {
                e.preventDefault();
                const saveBtn = $('#updateLeadBtn');
                const btnContent = saveBtn.html();
                $(".form-control").removeClass('is-invalid');
                $('.error').addClass('d-none');
                saveBtn.prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
                $(this).ajaxSubmit({
                    dataType: 'json', beforeSubmit: function () {
                        $("#progress-bar").width('0%');
                    },
                    uploadProgress: function (event, position, total, percentComplete) {
                        $("#progress-bar").width(percentComplete + '%').html('<small id="progress-status">' + percentComplete + ' % Complete</small>');
                    },
                    success: function (data) {
                        $('.avatar-change').addClass('d-none');
                        $('.avatar-changed').removeClass('d-none');
                        nSuccess(data.message);
                        $("#progress-bar").width('0%').html('0');
                        window.setTimeout(function () {
                            window.location.replace(data.route);
                        }, 3000)
                        saveBtn.html('<i class="fas fa-check-double"></i> saved successfully.');
                        $Modal.modal('hide');
                    },
                    error: function (request) {
                        saveBtn.prop('disable', false).removeClass('disabled').prop('type', 'submit').html(btnContent);
                        formRequest(request, true)
                    }, resetForm: true
                });
                return false;
            });

            $('#lead_product').select2({
                placeholder: "search for a product", minimumInputLength: 2,
                dropdownParent: $Modal,
                ajax: {
                    url: '{{route('products.select2')}}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {q: $.trim(params.term)};
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (product) {
                                return {text: product.Description + ' - ' + product.ProductID, id: product.ProductID}
                            })
                        };
                    },
                    cache: true
                }
            });
            $(document).on('click', '.add-lead-product', function () {
                $(".modal-item").addClass('d-none');
                $('#addLeadProductModal').removeClass('d-none');
                $('.modal-title').html('add an interested product');
                $('.modal-dialog').removeClass('modal-lg');
                $Modal.modal('show');
            });
            $('form#addLeadProductForm').submit(async function (e) {
                e.preventDefault();
                if (await saveForm($(this), $('#addLeadProductBtn'), false, true, true)) {
                    $Modal.modal('hide');
                    fetchProductsTable();
                }
            });

            //Initiated
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
                //dropdownParent: $Modal,
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
                                return {text: item.Name + ' - ' + item.UserID, id: item.UserID}
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
                    window.setTimeout(function () {
                           window.location.replace('{{ route('leads.show',[$lead->LeadID]) }}');
                        }, 3000)
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
            $('.modal-title').html('Start a call with Lead');
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
            $('.modal-title').html('Start a meeting with Lead');
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

        function fetchWatchersTable() {
            if (!$.fn.DataTable.isDataTable('#leadWatchersTable')) {
                $('#leadWatchersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[3, 'desc']],
                    ajax: {
                        url: '{{ route('lead-watchers.index',[$lead->LeadID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "DT_RowIndex", name: 'DT_RowIndex', searchable: false, orderable: false},
                        {data: 'party', name: 'party'},
                        {data: 'Role', name: 'Role'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "no workflow under this filter"
                    }
                }).on('error', function () {
                    nWarning("an issue occurred while loading workflow.");
                    // console.log(er);
                });
            } else {
                $('#leadWatchersTable').DataTable().ajax.reload();
            }
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
                        url: '{{ route('lead-calls.index',[$lead->LeadID]) }}',
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

        async function fetchActivities() {
            if (window.LeadActivitiesHasMore) {
                await $.get('{{ route('leads.activities',[$lead->LeadID]) }}?last_view=' + window.LeadActivitiesOldest, function (data) {
                        $("#activitiesMain").append(data);
                    }
                ).fail(function () {
                    nError('fetching activities failed');
                });
            }
            if (!window.LeadActivitiesHasMore) {
                $("#loadMoreBtn").addClass('d-none');
            } else {
                $("#loadMoreBtn").removeClass('d-none');
            }
        }

        function fetchNotesTable() {
            if (notesTable === null) {
                notesTable = $('#notesTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('lead-notes.index',[$lead->LeadID]) }}',
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
                        url: '{{ route('leads.discussions',[$lead->LeadID]) }}',
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
                    nWarning("an issue occurred while loading the lead discussions.");
                    console.log(er);
                });
            } else {
                discussionsTable.ajax.reload();
            }
        }

        function fetchProductsTable() {
            if (productsTable === null) {
                productsTable = $('#productsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('lead-products.index',[$lead->LeadID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: 'ProductID', name: 'ProductID'},
                        {data: 'ProductName', name: 'ProductName'},
                        {data: 'CreatedOn', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ], "oLanguage": {
                        "sEmptyTable": "No products <a class='add-lead-product' href='#'>added</a> or under current filter"
                    }
                });

                productsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading the lead portfolio.");
                    console.log(er);
                });
            } else {
                productsTable.ajax.reload();
            }
        }

        function appendActivity(activity) {
            window.LeadActivitiesOldest = parseInt(activity.id);
            $("#activitiesMain").prepend(activity.html);
        }


        function loadEmailDetailsModal(id) {
            if (!id) return;

            $('#emailDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
            $('#viewEmailModal').modal('show');

            const leadId = {{ $lead->LeadID }}; // Make sure $lead is available in the view
            const url = "{{ route('lead-mail.show', ['lead' => ':lead_id', 'lead_mail' => ':id']) }}"
                .replace(':lead_id', leadId)
                .replace(':id', id);

            $.get(url)
                .done(function (response) {
                    $('#emailDetailsContent').html(response);
                })
                .fail(function (jqXHR) {
                    $('#emailDetailsContent').html('<div class="text-danger">Failed to load email details</div>');
                    codeNotify(jqXHR.status);
                });
        }


        function fetchMailsTable() {
            if (EmailsTable === null) {
                EmailsTable = $('#EmailsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    order: [[2, 'desc']],
                    ajax: {
                        url: '{{ route('lead-mail.index', [$lead->LeadID]) }}',
                        error: function (jqXHR) {
                            codeNotify(jqXHR.status);
                        }
                    },
                    columns: [
                        {data: "Type", name: 'Type'},
                        {data: 'Subject', name: 'Subject'},
                        {data: 'Dated', name: 'CreatedOn'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ],
                    oLanguage: {
                        sEmptyTable: "<span class='text-center'>No records found</span>"
                    }
                });

                // View on double-click
                $('#EmailsTable tbody').on('dblclick', 'tr', function () {
                    const data = EmailsTable.row(this).data();
                    if (data?.id) {
                        loadEmailDetailsModal(data.id);
                    }
                });

                // View on button click
                $(document).on('click', '.view-email', function () {
                    const id = $(this).data('id');
                    loadEmailDetailsModal(id);
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
                        url: '{{ route('lead-sms.index', [$lead->LeadID]) }}',
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
                        url: '{{ route('lead-schedule.index',[$lead->LeadID]) }}',
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

        function fetchTasksTable() {
            if (tasksTable === null) {
                tasksTable = $('#tasksTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('lead-tasks.index',[$lead->LeadID]) }}',
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
                        "sEmptyTable": "no tasks under current filter, <a href='#' class='create-new-task' data-action='{{ route('lead-tasks.store',[$lead->LeadID]) }}'>create one"
                    }
                });

                tasksTable.on('error', function (er) {
                    nWarning("an issue occurred while loading tasks.");
                    console.log(er);
                });
            } else {
                tasksTable.ajax.reload();
            }
        }

        function fetchTicketsTable() {
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
                        url: '{{ route('lead-tickets.index',[$lead->LeadID]) }}',
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
                        "sEmptyTable": "no tickets under current filter, <a href='#' class='add-party-ticket-btn' data-action='{{ route('lead-tickets.store',[$lead->LeadID]) }}'>create one"
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

        function fetchContactsTable() {
            if (contactsTable === null) {
                contactsTable = $('#contactsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    "order": [[2, 'desc']],
                    "columnDefs": [
                        {"className": "text-center", "targets": [2]}
                    ],
                    ajax: {
                        url: '{{ route('lead-contacts.index',[$lead->LeadID]) }}',
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
                        "sEmptyTable": "no contact under current filter, <a  href='javascript: void(0)' class='click-summary-data' data-click_url='{{ route('lead-contacts.create',[$lead->LeadID]) }}' data-summary_title='Add Contact'>create one</a>"
                    }
                });

                contactsTable.on('error', function (er) {
                    nWarning("an issue occurred while loading contacts.");
                    console.log(er);
                });
            } else {
                contactsTable.ajax.reload();
            }
        }

        function readURL(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#image_upload_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
