@php @endphp
<div>
    @if($party instanceof \App\Models\BR\Client)
        @include('snippets.client_summary', ['client'=>$party])
    @elseif($party instanceof \App\Models\CRM\Lead)
        @include('snippets.lead_summary', ['lead'=>$party])
    @endif
    <hr class="m-0">
    <p class="mb-1 h4">Users</p>
    @if($users->count() > 1)
        <div class="row " {{ ($users->count()>3)?'style="max-height: 125px; overflow-y: scroll;"':'' }}>
            @foreach($users as $user)
                <div class="col-4 text-center">
                    {!! $user?->getImage('class="img-fluid rounded-circle mb-2" style="height: 70px;" width="70"') !!}
                    <h6>{{ $user->UserID }}</h6>
                </div>
            @endforeach
        </div>
    @elseif( $users->count() === 1 )
        @php $user = $users->first(); @endphp
        @include('snippets.user_summary', ['user'=>$user])
    @else
        <h6 class="text-info my-3">No Users Found</h6>
    @endif
    <hr class="mx-0 my-2">
    <p class="mb-1 h4">Discussion</p>
    <p class="justify-content-around">
        {{ $clientDiscussion->Discussion }}
    </p>
    <hr class="mx-0 my-2">
    @if($clientDiscussion->source instanceof \App\Models\Communication\Call)
        <p class="mb-1 h4"><b>Call</b> Source</p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">Start: <span
                    class="float-end">{{ $clientDiscussion->source->StartOn->format('M d, Y h:ia') }}</span></li>
            <li class="list-group-item">End: <span
                    class="float-end">{{ $clientDiscussion->source->EndOn->format('M d, Y h:ia') }}</span></li>
            <li class="list-group-item">Duration: <span
                    class="float-end">{{ $clientDiscussion->source->StartOn->diffForHumans($clientDiscussion->source->EndOn, \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true,parts: 2) }}</span>
            </li>
        </ul>
    @elseif($clientDiscussion->source instanceof \App\Models\CRM\Meeting)
        <p class="mb-1 h4"><b>Meeting</b> Source</p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">Start: <span
                        class="float-end">{{ $clientDiscussion->source->StartOn->format('M d, Y h:ia') }}</span></li>
            <li class="list-group-item">End: <span
                        class="float-end">{{ $clientDiscussion->source->EndOn->format('M d, Y h:ia') }}</span></li>
            <li class="list-group-item">Duration: <span
                        class="float-end">{{ $clientDiscussion->source->StartOn->diffForHumans($clientDiscussion->source->EndOn, \Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true,parts: 2) }}</span>
            </li>
        </ul>
    @endif
</div>
<script>

</script>
