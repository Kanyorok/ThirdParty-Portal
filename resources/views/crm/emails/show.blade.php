@php use App\Models\BR\Client;use App\Models\CRM\Lead; @endphp
@php @endphp
<div class="card">
    <div class="card-header">
        <div class="card-actions float-end">
            <a href="javascript:void(0)" onclick="closeEmailDetails()">
                <i class="fas fa-close"></i>
            </a>
        </div>
        <h5 class="card-title mb-0">{{ $crmEmail->Subject }}</h5>
    </div>
    <div class="card-body row">
        <div class="col-md-9 col-12">
            <div class="mb-3">
                <b>To</b>
                @foreach( $crmEmail->To[0] as  $name => $email)
                    <button class="btn btn-pill btn-sm btn-secondary">{{$name }}{{ $email}}</button>
                @endforeach
            </div>
            <div class="mb-3">
                <b>From</b>
                <button class="btn btn-pill btn-sm btn-secondary">{{ $crmEmail->From }}</button>
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
            <div class="justify-content-around">
                {!! $crmEmail->Body !!}
            </div>
        </div>
        <div class="col-md-3 col-12 border border-end">

            <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item">
                    @if($crmEmail->party instanceof Client)
                        @include('snippets.client_summary', ['client'=>$party,'show_summary'=>true])
                        {{--<div class="row">
                            <div class="col-6">
                                <button class="btn btn-primary w-100 btn-sm"><i
                                        class="align-middle fas fa-address-book"></i> ReAssign Lead
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-primary w-100 btn-sm"><i class="align-middle fas fa-users"></i>
                                    ReAssign Member
                                </button>
                            </div>
                        </div>--}}
                    @elseif($crmEmail->party instanceof Lead)
                        @include('snippets.lead_summary', ['lead'=>$party,'show_summary'=>true])
                        {{--<div class="row">
                            <div class="col-6">
                                <button class="btn btn-primary w-100 btn-sm"><i
                                        class="align-middle fas fa-address-book"></i> ReAssign Lead
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-primary w-100 btn-sm"><i class="align-middle fas fa-users"></i>
                                    ReAssign Member
                                </button>
                            </div>
                        </div>--}}
                    @else
                        <h3>No Party Set</h3>
                        <div class="row">
                            <div class="col-6">
                                <button class="btn btn-primary w-100 btn-sm"><i
                                        class="align-middle fas fa-address-book"></i> Assign Lead
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-primary w-100 btn-sm"><i class="align-middle fas fa-users"></i>
                                    Assign Member
                                </button>
                            </div>
                        </div>
                    @endif
                </li>
                <li class="list-group-item">Priority : <span class="float-end">{{$crmEmail->Priority->name}}</span></li>
                <li class="list-group-item">Dated : <span
                        class="float-end">{{$crmEmail->Dated?->format('d M Y, h:i A')}}</span></li>

            </ul>
            @include('snippets.behind_scenes',['model'=>$crmEmail])
        </div>

    </div>
</div>
<script>
    $(function () {

    });
</script>
