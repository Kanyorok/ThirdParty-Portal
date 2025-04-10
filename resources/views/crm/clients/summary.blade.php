<div class="d-flex flex-column" style="height: 85%">
    @include('snippets.client_summary', ['client'=>$client])
    <hr class="m-0">
    <p class="mb-1 h4">Contacts</p>
    <div class="text center">
        @if(!empty($client->Phone1))
            <span class="btn btn-lg btn-link me-1 my-1">{{ $client->Phone1 }}</span>
        @endif
        @if(!empty($client->Phone2))
            <span class="btn btn-lg btn-link me-1 my-1">{{ $client->Phone2 }}</span>
        @endif
        @if(!empty($client->Mobile))
            <span class="btn btn-lg btn-link me-1 my-1">{{ $client->Mobile }}</span>
        @endif
        @if(!empty($client->Email))
            <span class="btn btn-lg btn-link me-1 my-1">{{ $client->Email }}</span>
        @endif
    </div>

    @if(!empty($client->Notes))
        <p class="mb-1 h4">Note</p>
        <p class="justify-content-around">
            {{ $client->Notes }}
        </p>
    @endif
    <hr>
    @forelse ($activities as $activity)
        <div class="d-flex align-items-start">
            <div class="flex-grow-1">
                <small class="float-end text-navy">{{ $activity->CreatedOn->diffForHumans(short: true) }}</small>
                {{$activity->Notes }}<br/>
                <small class="text-muted"> {{  $activity->CreatedOn->format('F d, Y h:i a') }}</small><br/>
            </div>
        </div>
        <hr/>
    @empty
        <p class="text-center my-5 ">No activities found <i class="fas fa-sad-tear text-warning"></i></p>
    @endforelse

</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$client])
</div>
