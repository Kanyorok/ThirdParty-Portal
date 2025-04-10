<div class="row">
    <div class="col-5 col-sm-5 col-md-12 col-lg-12 col-xl-12 col-xxl-5 align-content-center text-center">
        <div>
            {!! $lead->getImage('class="img-fluid img-thumbnail  mb-2" width="128" height="128"') !!}
        </div>
    </div>
    <div class="col-7 col-sm-7 col-md-12 col-lg-12 col-xl-12 col-xxl-7 align-content-center">
        <ul class="list-group list-group-flush">
            <li class="list-group-item p-1">
                @if(isset($show_summary))
                    <a href="#" data-click_url="{{ route('leads.summary', $lead->LeadID)  }}"
                       data-summary_title="lead summary"
                       class="click-summary-data h4 text-primary text-uppercase text-decoration-underline">{{ \Illuminate\Support\Str::padLeft($lead->LeadID,5,'0') }}</a>
                @else
                    <a href="{{ route('leads.show',[$lead->LeadID]) }}"
                       class="h4 text-primary text-uppercase text-decoration-underline">{{ \Illuminate\Support\Str::padLeft($lead->LeadID,5,'0') }}</a>
                @endif
                <span class="float-end mr-2" title="{{ $lead->Status->name }}">{{ $lead->Status->getIcon() }} </span>
            </li>
            <li class="list-group-item p-1">{{ $lead->Name }} {{ ($lead->OtherNames)??'' }}</li>
            <li class="list-group-item p-1">{{ $lead->Type->name }} <b class="mr-2">(Lead)</b></li>
        </ul>
    </div>
</div>
