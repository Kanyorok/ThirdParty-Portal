<div class="d-flex flex-column" style="height: 80%">
    @if($party instanceof \App\Models\BR\Client)
        @include('snippets.client_summary', ['client'=>$party])
    @elseif($party instanceof \App\Models\CRM\Lead)
        @include('snippets.lead_summary', ['lead'=>$party])
    @endif

    @switch($schedule->ScheduleStatusID->value)
        @case(\App\Enums\ScheduleStatusEnum::Success->value)
        @case(\App\Enums\ScheduleStatusEnum::PartialSuccess->value)
            @if(($call instanceof \App\Models\Communication\Call))
                <h4 class="mt-1 mb-0" style="color:{{$service->colour()}};"><b>call</b> details</h4>
                <hr class="mt-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Status: <span
                            class="float-end">{{ $schedule->ScheduleStatusID->description() }}</span></li>
                    <li class="list-group-item">Start: <span
                            class="float-end">{{ $call->StartOn->format('M d, Y h:ia') }}</span></li>
                    <li class="list-group-item">End: <span
                            class="float-end">{{ $call->EndOn->format('M d, Y h:ia') }}</span></li>
                    <li class="list-group-item">Duration: <span
                            class="float-end">{{ $call->StartOn->diffForHumans($call->EndOn, \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true,parts: 2) }}</span>
                    </li>
                </ul>
            @else
                <div class="alert alert-warning" role="alert">
                    <div class="alert-icon">
                        <i class="far fa-fw fa-bell"></i>
                    </div>
                    <div class="alert-message">
                        <strong>Warning </strong> could not fetch call details!
                    </div>
                </div>
            @endif
            @break
        @case(\App\Enums\ScheduleStatusEnum::Canceled->value)
            <h4 class="mb-0">canceled call details</h4>
            <hr class="mt-0">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Status: <span
                        class="float-end">{{ $schedule->ScheduleStatusID->description() }}</span></li>
                <li class="list-group-item">Start: <span
                        class="float-end">{{ $schedule->StartOn->format('M d, Y h:ia') }}</span></li>
                <li class="list-group-item">End: <span
                        class="float-end">{{ $schedule->EndOn->format('M d, Y h:ia') }}</span></li>
                <li class="list-group-item">Duration: <span
                        class="float-end">{{ $schedule->StartOn->diffForHumans($schedule->EndOn, \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true,parts: 2) }}</span>
                </li>
            </ul>
            @break
        @case(\App\Enums\ScheduleStatusEnum::Scheduled->value)
            @if(now()->today()->startOfDay()->gte($schedule->EndOn))
                <h4 class="mb-0" style="color:{{$service->colour()}};">schedule details</h4>
                <hr class="mt-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Status: <span class="float-end">Scheduled Unused</span></li>
                    <li class="list-group-item">Start: <span
                            class="float-end">{{ $schedule->StartOn->format('M d, Y h:ia') }}</span></li>
                    <li class="list-group-item">End: <span
                            class="float-end">{{ $schedule->EndOn->format('M d, Y h:ia') }}</span></li>
                    <li class="list-group-item">Duration: <span
                            class="float-end">{{ $schedule->StartOn->diffForHumans($schedule->EndOn, \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true,parts: 2) }}</span>
                    </li>
                </ul>
            @else
                <h4 class="mb-0">schedule details</h4>
                <hr class="mt-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Status: <span
                            class="float-end">{{ $schedule->ScheduleStatusID->description() }}</span></li>
                    <li class="list-group-item">Start: <span
                            class="float-end">{{ $schedule->StartOn->format('M d, Y h:ia') }}</span></li>
                    <li class="list-group-item">End: <span
                            class="float-end">{{ $schedule->EndOn->format('M d, Y h:ia') }}</span></li>
                    <li class="list-group-item">Duration: <span
                            class="float-end">{{ $schedule->StartOn->diffForHumans($schedule->EndOn, \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true,parts: 2) }}</span>
                    </li>
                </ul>
            @endif
            @break
    @endswitch

    <p class="mb-3">Schedule Notes: <i class="align-middle" data-feather="info"></i> <br>
        <span style="text-align: justify">{{ $schedule->Notes }}</span>
    </p>


</div>
<div class="m-auto">
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
                            onclick="callRedirect('{{ $service->actionLink($party->ClientID) }}')"
                    @elseif($party instanceof \App\Models\CRM\Lead)
                        onclick="callRedirect('{{ $service->actionLink($party->LeadID) }}')"
                    @endif
                    @else
                        disabled
                    @endif
                    type="button"><i
                    class="fas fa-phone-alt"></i> start call
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
                          @elseif($party instanceof \App\Models\CRM\Lead)
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
    function callRedirect(url) {
        nSuccess('redirecting to call.');
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
