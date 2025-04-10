@php use App\Models\BR\Client, App\Models\Lead; @endphp
<div class="d-flex flex-column" style="height: 85%">
    <div class="mb-3">
        <b>To</b>
        <button type="button" class="btn btn-pill btn-sm btn-secondary">{{ $sms->Phone}}</button>
    </div>

    <div class="justify-content-around">
        <p>Content: </p>
        <p class="text-black">{!! $sms->Content !!}</p>
    </div>
    <hr>
    <ul class="list-group list-group-flush mb-3">
        <li class="list-group-item">
            @if($party instanceof Client)
                @include('snippets.client_summary', ['client'=>$party,'show_summary'=>true])
            @elseif($party instanceof Lead)
                @include('snippets.lead_summary', ['lead'=>$party,'show_summary'=>true])
            @else
                <h3>No Party Set</h3>
            @endif
        </li>
        <li class="list-group-item">Type : <span
                class="float-end">{{$sms->Type->name}}</span></li>
        <li class="list-group-item">Status : <span
                class="float-end">{{$sms->Status->name}}</span></li>
        <li class="list-group-item">Dated : <span class="float-end">{{$sms->Dated?->format('d M Y, h:i A')}}</span></li>
        <li class="list-group-item">Source : <span
                class="float-end">{!!  (new \App\Services\SMSService($sms))->source() !!}</span></li>

    </ul>
</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$sms])
</div>
