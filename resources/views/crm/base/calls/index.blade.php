@extends('layouts.app')

@section('title','Incoming Call')
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
        <div class="col-12">
            <div class="card mb-3">
                <form class="card-body row  px-2" id="searchForm">
                    <div class="col-12 col-md-10">
                        <input type="search" class="form-control w-100 search-form-item" name="phone" autocomplete="off"
                               maxlength="50" id="phone"
                               placeholder="phone no 254700XXXYYY" value="{{ ($phone)??'' }}">
                    </div>
                    <div class="col-12 col-md-2">
                        <button class="btn btn-primary w-100" id="searchFormBtn" type="submit"><i
                                class="fas fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @if(is_null($phone))
            <div class="mb-3">
                <div class="alert alert-primary" role="alert">
                    <div class="alert-message">
                        <strong>Hello there!</strong> Please search for a contact number using the form above.
                    </div>
                </div>
            </div>
        @else
            @if($party instanceof \App\Models\BR\Client)
                <div class="col-md-4 col-xxl-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Member Summary</h5>
                        </div>
                        <div class="card-body mx-1 mb-0 mt-1">
                            @include('snippets.client_summary', ['client'=>$party,'show_summary'=>true])
                        </div>
                        <div class="card-body">
                            <h5 class="h6 card-title">About</h5>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3"><i class="align-middle"
                                                    data-feather="map-pin"></i> {{ $party->Address1 }} {{ $party->Address2 }}
                                    , {{ $party->CityID }}, {{ $party->CountryID }}
                                </li>

                                <li class="mb-3">Notes: <i class="align-middle" data-feather="info"></i> <br> <span
                                        style="text-align: justify">{{ $party->Notes }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 col-xxl-9">
                    <div class="card">
                        <div class="card-header border border-bottom pb-0">
                            <h3 class="card-title">Start a call with Member: {{ $party->ClientID }} </h3>
                        </div>
                        <div class="card-body row">
                            <form action="{{ route('client-calls.store',[$party->ClientID]) }}" method="post"
                                  id="StartCallForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="call_initiated">Call Time <span
                                                class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime" id="call_initiated"
                                           name="call_initiated" required placeholder="Select start..">
                                    <p id="call_initiated_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <input type="hidden" class="d-none" id="schedule" name="schedule" readonly
                                       value="{{ \App\Http\Requests\Call\StartCallRequest::NoSchedule }}">
                                <div class="row">
                                    <div class="col-md-6 col-12">&nbsp;</div>
                                    <div class="col-md-6 col-12">
                                        <button class="btn btn-primary w-100" type="submit" id="StartCallBtn"><i
                                                    class="fas fa-phone"></i> Start Incoming Call
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($party instanceof \App\Models\CRM\Lead)
                <div class="col-md-4 col-xxl-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Lead Summary</h5>
                        </div>
                        <div class="card-body mx-1 mb-0 mt-1">
                            @include('snippets.lead_summary', ['lead'=>$party, 'show_summary'=>true])
                        </div>
                        <div class="card-body">
                            <hr class="my-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><b>Industry</b><span
                                        class="float-end">{{ $party->industry?->Description }} </span></li>
                                <li class="list-group-item"><b>Source</b><span
                                        class="float-end">{{ $party->source?->Description }} </span></li>
                                <li class="list-group-item"><b>Customer Type</b><span
                                        class="float-end">{{ $party->customerType?->Description }} </span></li>
                                @if(!empty($party->Website))
                                    <li class="list-group-item"><b>Website </b><a href="{{ $party->Website }}"
                                                                                  class="float-end"> {{ $party->Website }} </a>
                                    </li>
                                @endif
                                @if(!empty($party->JobTitle))
                                    <li class="list-group-item"><b>Job Title </b><span
                                            class="float-end"> {{ $party->JobTitle }} </span></li>
                                @endif
                                <li class="list-group-item"><b>Last Contacted </b><span
                                        class="float-end">{{ $party->LastContacted?->format('d M, Y h:i A') }}</span>
                                </li>
                            </ul>
                            <p class="p-2 mt-3"><b>Notes</b><br>
                                {{ $party->Notes }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 col-xxl-9">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Relationship Officer</h3>
                        </div>
                        <div class="card-body">
                            @if($party->RelationshipManager instanceof \App\Models\Auth\User)
                                @include('snippets.user_summary', ['user'=>$party->RelationshipManager])
                            @else
                                <h3>Unknown / Unassigned</h3>
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header border border-bottom pb-0">
                            <h3 class="card-title">Start a call with Lead </h3>
                        </div>
                        <div class="card-body row">
                            <form action="{{ route('lead-calls.store',[$party->LeadID]) }}" method="post"
                                  id="StartCallForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="call_initiated">Call Time <span
                                                class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime" id="call_initiated"
                                           name="call_initiated" required placeholder="Select start..">
                                    <p id="call_initiated_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <input type="hidden" class="d-none" id="schedule" name="schedule" readonly
                                       value="{{ \App\Http\Requests\Call\StartCallRequest::NoSchedule }}">
                                <div class="row">
                                    <div class="col-md-6 col-12">&nbsp;</div>
                                    <div class="col-md-6 col-12">
                                        <button class="btn btn-primary w-100" type="submit" id="StartCallBtn"><i
                                                    class="fas fa-phone"></i> Start Incoming Call
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($party instanceof \App\Models\CRM\Contact)
                <div class="col-md-4 col-xxl-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Unattached Contact</h5>
                        </div>
                        <h3 class="text-center">{{ $party->Label }}</h3>
                        <div class="card-body mx-1 mb-0 mt-1">
                            @include('snippets.behind_scenes',['model'=>$party])
                        </div>
                    </div>
                </div>
                <div class="col-md-8 col-xxl-9">
                    <div class="card">
                        <div class="card-header border border-bottom pb-0">
                            <h3 class="card-title">Start a call with Unattached Contact </h3>
                        </div>
                        <div class="card-body row">
                            <form action="{{ route('contacts-calls.store',[$party->ContactID]) }}" method="post"
                                  id="StartCallForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="call_initiated">Call Time <span
                                                class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime" id="call_initiated"
                                           name="call_initiated" required placeholder="Select start..">
                                    <p id="call_initiated_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <input type="hidden" class="d-none" id="schedule" name="schedule" readonly
                                       value="{{ \App\Http\Requests\Call\StartCallRequest::NoSchedule }}">
                                <div class="row">
                                    <div class="col-md-6 col-12">&nbsp;</div>
                                    <div class="col-md-6 col-12">
                                        <button class="btn btn-primary w-100" type="submit" id="StartCallBtn"><i
                                                    class="fas fa-phone"></i> Start Incoming Call
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-4 col-xxl-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Unknown Caller</h5>
                        </div>
                        <div class="card-body mx-1 mb-0 mt-1">

                        </div>
                    </div>
                </div>
                <div class="col-md-8 col-xxl-9">
                    <div class="card">
                        <div class="card-header border border-bottom pb-0">
                            <h3 class="card-title">Start a call with ? ? </h3>
                        </div>
                        <div class="card-body row">
                            <form action="{{ route('contact.call.incoming.start') }}" method="post" id="StartCallForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label" for="call_initiated">Call Time <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-datetime" id="call_initiated"
                                           name="call_initiated" required placeholder="Select start..">
                                    <p id="call_initiated_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="Phone">Caller Phone No <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="Phone" name="Phone" required
                                           value="{{ $phone }}">
                                    <p id="Phone_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="Name">Caller Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="Name" name="Name" required autofocus>
                                    <p id="Name_error" class="invalid-feedback d-none error col-12"
                                       role="alert"></p>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-12">&nbsp;</div>
                                    <div class="col-md-6 col-12">
                                        <button class="btn btn-primary w-100" type="submit" id="StartCallBtn"><i
                                                class="fas fa-phone"></i> Start Incoming Call
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

@endsection
@section('scripts')
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script>
        $(function () {
            flatpickr("#call_initiated", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                minTime: "06:00",
                minuteIncrement: 1,
                defaultDate: moment().subtract(1, 'min').format('hh:mm'),
                maxTime: moment().add(1, 'min').format('HH:mm'),
            });

            $('form#StartCallForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#StartCallBtn'), true, true, true);

            });
        });
    </script>
@endsection
