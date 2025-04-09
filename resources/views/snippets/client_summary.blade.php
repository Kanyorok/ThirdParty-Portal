<div class="row">
    <div class="col-5 col-sm-5 col-md-12 col-lg-12 col-xl-12 col-xxl-5 align-content-center text-center">
        <div>
            {!! $client->getImage('class="img-fluid rounded-circle mb-2" width="128" height="128"') !!}
        </div>
    </div>
    <div class="col-7 col-sm-7 col-md-12 col-lg-12 col-xl-12 col-xxl-7 align-content-center">
        <ul class="list-group list-group-flush">
            <li class="list-group-item p-1"><small>Mbr No.</small>:
                @if(isset($show_summary))
                    <a href="#" data-click_url="{{ route('clients.summary',$client->ClientID)  }}"
                       data-summary_title="member summary"
                       class="click-summary-data h4 text-primary text-uppercase text-decoration-underline">{{ $client->ClientID }}</a>
                @else
                    <a href="{{ route('clients.show',[$client->ClientID]) }}"
                       class="h4 text-primary text-uppercase text-decoration-underline">{{ $client->ClientID }}</a>
                @endif
            </li>
            <li class="list-group-item p-1">{{ $client->Name }}</li>
            <li class="list-group-item p-1">{{ $client->type->Description }} <b class="ml-2">(Member)</b></li>
        </ul>
    </div>
</div>
