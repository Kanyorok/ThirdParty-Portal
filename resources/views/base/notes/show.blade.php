<div class="d-flex flex-column" style="height: 85%">
    @if($party instanceof \App\Models\BR\Client)
        @include('snippets.client_summary', ['client'=>$party])
    @elseif($party instanceof \App\Models\Lead)
        @include('snippets.lead_summary', ['lead'=>$party])
    @endif
    <hr class="mx-0 my-2">
    <p class="mb-1 h4">Note</p>
    <p class="justify-content-around">
        {{ $note->Notes }}
    </p>
</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$note])
</div>
