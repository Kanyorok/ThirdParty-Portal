<div class="d-flex flex-column" style="height: 87%">
    <h4 class="h2 text-center"><a href="{{ route('tickets.show',[$ticket->TicketID]) }}">{{ $ticket->TicketID }}</a>
    </h4>
    <h4 class="h4 text-center">{{ $ticket->Title }}</h4>
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Status: <b class="float-end">{{ $ticket->Status->name }}</b></li>
        <li class="list-group-item">Priority: <b class="float-end">{{ $ticket->Priority->name }}</b></li>
        <li class="list-group-item">Source: <b class="float-end">{!! $service->source() !!}</b></li>
        <li class="list-group-item">Comments: <b class="float-end">{{ number_format($ticket->comments()->count()) }}</b>
        </li>
        <li class="list-group-item">Start: <span
                class="float-end">{{ $ticket->StartDate?->format('M d, Y h:ia') }}</span></li>
        <li class="list-group-item">EndDate: <span
                class="float-end">{{ $ticket->EndDate?->format('M d, Y h:ia') }}</span></li>
        @if($ticket->assignee instanceof \App\Models\Auth\User)
            @if(\App\Helpers\SystemHelper::isSystem($ticket->assignee))
                <li class="list-group-item">Assignee: <span class="float-end">None - Unassigned</span></li>
            @else
                <li class="list-group-item">Assignee:
                    <details class="float-end">
                        <summary>{{  $ticket->assignee->UserID }} (User)</summary>
                        <p>{{  $ticket->assignee->Name }}</p>
                    </details>
                </li>
            @endif
        @elseif($ticket->assignee instanceof \App\Models\Auth\Team)
            <li class="list-group-item">Assignee: <span class="float-end">{{ $ticket->assignee->Name }} (Team)</span>
            </li>
        @else
            <li class="list-group-item">Assignee: <span class="float-end">Unknown ?</span></li>
        @endif
        {{-- <li class="list-group-item">Duration: <span class="float-end">{{ $ticket->StartDate?->diffForHumans($ticket->EndDate, \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true,parts: 2) }}</span></li>--}}
    </ul>
    <hr class="mt-0 mb-3">
    @if($party instanceof \App\Models\BR\Client)
        @include('snippets.client_summary', ['client'=>$party])
    @elseif($party instanceof \App\Models\CRM\Lead)
        @include('snippets.lead_summary', ['lead'=>$party])
    @elseif($party instanceof \App\Models\Auth\User)
        @include('snippets.user_summary', ['user'=>$party])
    @else
        <h3>Unknown party</h3>
    @endif

</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$ticket])
</div>
