@php use App\Models\BR\Client;use App\Models\CRM\Lead; @endphp
<div class="d-flex flex-column" style="height: 85%">
    <h3 class="text-center">{{ $crmEmail->Subject }}</h3>
    <div class="mb-3">
        <b>From</b>
        <button class="btn btn-pill btn-sm btn-secondary">{{ $crmEmail->From }}</button>
    </div>
    <div class="mb-3">
        <b>To</b>
        @foreach( $crmEmail->To[0] as  $name => $email)
            <button class="btn btn-pill btn-sm btn-secondary">{{$name }}{{ $email}}</button>
        @endforeach
    </div>
    @if(!empty($crmEmail->CC))
        <div class="mb-3">
            <b>CC</b>
            @foreach( $crmEmail->CC as  $address)
                <button class="btn btn-pill btn-sm btn-secondary">{{ array_keys($address)[0] }}
                    : {{ reset($address)}}</button>
            @endforeach
        </div>
    @endif
    <hr>
    <div class="justify-content-around">
        {!! $crmEmail->Body !!}
    </div>
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
        <li class="list-group-item">Priority : <span class="float-end">{{$crmEmail->Priority->name}}</span></li>
        <li class="list-group-item">Status : <span class="float-end">{{$crmEmail->Status->name}}</span></li>
        <li class="list-group-item">Dated : <span
                class="float-end">{{$crmEmail->Dated?->format('d M Y, h:i A')}}</span></li>
    </ul>
</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$crmEmail])
</div>
