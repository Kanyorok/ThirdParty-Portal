@php use App\Enums\MeetingStatusEnum; @endphp
@php use Carbon\CarbonInterface; @endphp
@php use App\Services\MeetingService; @endphp
@php use App\Models\BR\Client; @endphp
@php use App\Models\CRM\Lead; @endphp
@php use App\Models\Auth\User; @endphp

@php
    // Determine party ID for cancel link
    $partyId = null;
    if (isset($parties)) {
        if ($parties instanceof Client || $parties instanceof Lead) {
            $party = $parties;
            $partyId = $party->ClientID ?? $party->LeadID;
        } elseif ($parties->first() instanceof User || $parties->first()?->UserID) {
            $party = null;
        }
    }
@endphp

<style>
    .ribbon {
        width: 150px;
        height: 150px;
        overflow: hidden;
        position: absolute;
    }

    .ribbon::before,
    .ribbon::after {
        position: absolute;
        z-index: -1;
        content: '';
        display: block;
        border: 5px solid #2980b9;
    }

    .ribbon-info span {
        background-color: rgba(var(--bs-info-rgb));
        color: #fff;
    }

    .ribbon-danger span {
        background-color: rgba(var(--bs-danger-rgb));
        color: #fff;
    }

    .ribbon span {
        position: absolute;
        display: block;
        width: 225px;
        padding: 15px 0;
        box-shadow: 0 5px 10px rgba(0, 0, 0, .1);
        font: 700 18px/1 'Lato', sans-serif;
        text-shadow: 0 1px 1px rgba(0, 0, 0, .2);
        text-transform: uppercase;
        text-align: center;
    }

    .ribbon-top-left {
        top: -10px;
        left: -10px;
    }

    .ribbon-top-left::before,
    .ribbon-top-left::after {
        border-top-color: transparent;
        border-left-color: transparent;
    }

    .ribbon-top-left::before { top: 0; right: 0; }
    .ribbon-top-left::after { bottom: 0; left: 0; }

    .ribbon-top-left span {
        right: -25px;
        top: 30px;
        transform: rotate(-45deg);
    }
</style>

<div>
    @if($meeting->StatusID->value === MeetingStatusEnum::Canceled->value)
        <div class="ribbon ribbon-top-left ribbon-danger"><span>Canceled</span></div>
    @endif

    <ul class="list-group list-group-flush">
        <li class="list-group-item">Start: <span class="float-end">{{ $meeting->StartOn->format('M d, Y h:ia') }}</span></li>
        <li class="list-group-item">End: <span class="float-end">{{ $meeting->EndOn->format('M d, Y h:ia') }}</span></li>
        <li class="list-group-item">Duration: 
            <span class="float-end">
                {{ $meeting->StartOn->diffForHumans($meeting->EndOn, CarbonInterface::DIFF_ABSOLUTE, short: true, parts: 2) }}
            </span>
        </li>
        <li class="list-group-item">Location: <span class="float-end">{{ (new MeetingService($meeting))->getVenue(true) }}</span></li>
        <li class="list-group-item">Source: <span class="float-end">{{ $service->source() }}</span></li>
    </ul>

    {{-- Summary --}}
    @if($parties instanceof Client)
        @include('snippets.client_summary', ['client' => $parties])
    @elseif($parties instanceof Lead)
        @include('snippets.lead_summary', ['lead' => $parties])
    @elseif($meeting->Type === Client::getPrimaryKey())
        <h4 class="mb-0">Members</h4><hr class="mt-0">
        <div class="row">
            @foreach($parties as $client)
                <a href="{{ route('clients.show',[$client->ClientID]) }}" title="{{ $client->Name }}" class="col-3 text-center align-content-center">
                    {!! $client->getImage('class="rounded-circle me-1 mb-1" style="height: 50px;" width="50"') !!}
                    <p>{{ $client->ClientID }}</p>
                </a>
            @endforeach
            @if($parties->hasMorePages())
                <div class="col-3 text-center align-content-center">
                    <img src="https://placehold.co/200x200/green/FFF?font=roboto&text=%2B{{ number_format($parties_count) }}" alt="More"
                         class="rounded-circle me-1 mb-1" style="height: 50px;" width="50"/>
                </div>
            @endif
        </div>
    @elseif($meeting->Type === User::getPrimaryKey())
        <h4 class="mb-0">Users/staff</h4><hr class="mt-0">
        <div class="row">
            @foreach($parties as $user)
                <a href="{{ route('users.show',[$user->UserID]) }}" title="{{ $user->Name }}" class="col-3 text-center align-content-center">
                    {!! $user->getImage('class="rounded-circle me-1 mb-1" style="height: 50px;" width="50"') !!}
                    <p>{{ $user->UserID }}</p>
                </a>
            @endforeach
            @if($parties->hasMorePages())
                <div class="col-3 text-center align-content-center">
                    <img src="https://placehold.co/200x200/green/FFF?font=roboto&text=%2B{{ number_format($parties_count) }}" alt="More"
                         class="rounded-circle me-1 mb-1" style="height: 50px;" width="50"/>
                </div>
            @endif
        </div>
    @endif

    <p class="mb-3">Notes: <i class="align-middle" data-feather="info"></i><br>
        <span style="text-align: justify">{{ $schedule->Notes }}</span>
    </p>

    @include('snippets.behind_scenes', ['model' => $schedule])

    <p class="mb-0">Action</p>
    <hr class="mt-0">
    <div class="row">
        <div class="col-md-6 col-12">
            <button class="btn btn-danger w-100 action-button"
                    @if($service->cancelable())
                        onclick="triggerTrashSchedule()"
                    @else
                        disabled
                    @endif>
                <i class="fas fa-trash-alt"></i> cancel
            </button>
        </div>
        <div class="col-md-6 col-12">
            <button class="btn btn-success w-100 action-button"
                    @if($service->actionable())
                        @if($party instanceof Client)
                            onclick="scheduleRedirect('{{ $service->actionLink($party->ClientID) }}')"
                        @elseif($party instanceof Lead)
                            onclick="scheduleRedirect('{{ $service->actionLink($party->LeadID) }}')"
                        @else
                            onclick="scheduleRedirect('{{ $service->actionLink('') }}')"
                        @endif
                    @else
                        disabled
                    @endif>
                <i class="fas fa-calendar"></i> start meeting
            </button>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="callActionsModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel {{ $schedule->Title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content text-center">
                    <h4 class="text-danger">Cancel Meeting Schedule <b>{{ $schedule->Title }}</b> ?</h4>
                    <div class="mt-2 mb-2">You are about to cancel this schedule, confirm below?</div>
                    <hr>
                    <form id="deleteCallScheduleForm" action="{{ $service->cancelLink($partyId ?? '') }}" method="post">
                        @csrf
                        @method('delete')
                        <div class="mt-4">
                            <button type="button" class="btn btn-success float-start" data-bs-dismiss="modal">no, keep</button>
                            <button class="btn btn-danger float-end" id="deleteCallScheduleBtn" type="submit">
                                <i class="fas fa-trash"></i> yes, cancel meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    @if($service->actionable())
    function scheduleRedirect(url) {
        nSuccess('redirecting to meeting.');
        window.bsOffcanvas.hide();
        setTimeout(() => window.location.replace(url), 1000);
    }
    @endif

    @if($service->cancelable())
    function triggerTrashSchedule() {
        $("#callActionsModal").modal('show');
    }

    $(function () {
        $('form#deleteCallScheduleForm').submit(async function (e) {
            e.preventDefault();
            let data = await saveForm($(this), $('#deleteCallScheduleBtn'), false, true, true);
            if (data) {
                $("#callActionsModal").modal('hide');
                window.bsOffcanvas.hide();
                if (typeof window.calendar === "object") {
                    let event = window.calendar.getEventById(data.event.id);
                    if (event) {
                        event.remove();
                        window.calendar.addEvent(data.event);
                    }
                }
                if (typeof fetchScheduleTable === "function") {
                    fetchScheduleTable();
                }
            }
        });
    });
    @endif
</script>
