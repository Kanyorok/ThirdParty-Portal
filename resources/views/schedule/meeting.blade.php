<div>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Start: <span class="float-end">{{ $meeting->StartOn->format('M d, Y h:ia') }}</span>
        </li>
        <li class="list-group-item">End: <span class="float-end">{{ $meeting->EndOn->format('M d, Y h:ia') }}</span>
        </li>
        <li class="list-group-item">Duration: <span
                class="float-end">{{ $meeting->StartOn->diffForHumans($meeting->EndOn, \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true,parts: 2) }}</span>
        </li>
        <li class="list-group-item">Location: <span
                class="float-end">{{ (new \App\Services\MeetingService($meeting))->getVenue(true)  }}</span>
        </li>
        <li class="list-group-item">Source : <span
                class="float-end">{{ $service->source() }}</span></li>
    </ul>
    @if($parties instanceof \App\Models\BR\Client)
        @php
            $party = $parties;
        @endphp
        @include('snippets.client_summary', ['client'=>$parties])
    @elseif($parties instanceof \App\Models\Lead)
        @php
            $party = $parties;
        @endphp
        @include('snippets.lead_summary', ['lead'=>$parties])
    @elseif($meeting->Type === \App\Models\BR\Client::getPrimaryKey())
        <h4 class="mb-0">members</h4>
        <hr class="mt-0">
        <div class="row">
            @foreach($parties as $client)
                <a href="{{ route('clients.show',[$client->ClientID]) }}" title="{{ $client->Name }}"
                   class="col-3 text-center align-content-center">
                    {!! $client->getImage('class="rounded-circle me-1 mb-1" style="height: 50px;" width="50"') !!}
                    <p>{{ $client->ClientID }}</p>
                </a>
                @php
                    $party = $parties;
                @endphp
            @endforeach
            @if($parties->hasMorePages())
                <div class="col-3  text-center align-content-center">
                    <img
                        src="https://placehold.co/200x200/green/FFF?font=roboto&text=%2B{{ number_format($parties_count) }}"
                        alt="More" class="rounded-circle me-1 mb-1" style="height: 50px;" width="50"/>
                </div>
            @endif
        </div>
    @elseif($meeting->Type === \App\Models\User::getPrimaryKey())
        <h4 class="mb-0">Users/staff</h4>
        <hr class="mt-0">
        <div class="row">
            @foreach($parties as $user)
                <a href="{{ route('users.show',[$user->UserID]) }}" title="{{ $user->Name }}"
                   class="col-3 text-center align-content-center">
                    {!! $user->getImage('class="rounded-circle me-1 mb-1" style="height: 50px;" width="50"') !!}
                    <p>{{ $user->UserID }}</p>
                </a>
                @php
                    $party = $parties;
                @endphp
            @endforeach
            @if($parties->hasMorePages())
                <div class="col-3  text-center align-content-center">
                    <img
                        src="https://placehold.co/200x200/green/FFF?font=roboto&text=%2B{{ number_format($parties_count) }}"
                        alt="More" class="rounded-circle me-1 mb-1" style="height: 50px;" width="50"/>
                </div>
            @endif
        </div>
    @endif
    <p class="mb-3">Notes: <i class="align-middle" data-feather="info"></i> <br>
        <span style="text-align: justify">{{ $schedule->Notes }}</span>
    </p>

    @include('snippets.behind_scenes',['model'=>$schedule])
    <p class="mb-0">actions</p>
    <hr class="mt-0">
    <div class="form-buttons- row">
        <div class="col-md-6 col-12">
            <button class="btn btn-danger w-100 action-button"
                    @if($service->cancelable())
                        onclick="triggerTrashSchedule()"
                    @else
                        disabled
                    @endif
                    type="button"><i
                    class="fas fa-trash-alt"></i> cancel
            </button>
        </div>
        <div class="col-md-6 col-12">
            <button class="btn btn-success w-100 action-button"
                    @if($service->actionable())
                        @if($party instanceof \App\Models\BR\Client)
                            onclick="scheduleRedirect('{{ $service->actionLink($party->ClientID) }}')"
                    @elseif($party instanceof \App\Models\Lead)
                        onclick="scheduleRedirect('{{ $service->actionLink($party->LeadID) }}')"
                    @endif
                    @else
                        disabled
                    @endif
                    type="button"><i
                    class="fas fa-calendar"></i> start meeting
            </button>
        </div>
    </div>
</div>
<div class="modal fade" id="callActionsModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="false"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel {{ $schedule->Title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="onboarding-content text-center">
                    <h4 class="text-danger">
                        Cancel Schedule <b>{{ $schedule->Title }}</b> ?
                    </h4>
                    <div class="mt-2 mb-2">
                        You are about to cancel this schedule, confirm below ?
                    </div>
                    <hr>
                    <form id="deleteCallScheduleForm"
                          @if($party instanceof \App\Models\BR\Client)
                              action="{{ $service->cancelLink($party->ClientID) }}"
                          @elseif($party instanceof \App\Models\Lead)
                              action="{{ $service->cancelLink($party->LeadID) }}"
                          @else
                              class="d-none"
                          @endif
                          method="post"> @csrf
                        <div class="mt-4">@method('delete')
                            <button type="button" class="btn btn-success float-start"
                                    data-bs-dismiss="modal">
                                no, keep
                            </button>
                            <button class="btn btn-danger float-end" id="deleteCallScheduleBtn" type="submit"><i
                                    class="fas fa-trash"></i> yes, cancel call
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
        window.setTimeout(function () {
            window.location.replace(url);
        }, 1000);
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
                    if (event !== null) {
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
