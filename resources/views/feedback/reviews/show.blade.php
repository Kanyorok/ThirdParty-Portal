<div class="d-flex flex-column" style="height: 85%">
    <div class="text-center">
        {!! (new \App\Services\Feedback\ReviewService($review))->getRate(' width="100" height="100" class="rounded" alt="' . $review->Rating . ' star"') !!}
    </div>
    <hr>
    <p class="justify-content-around">
        <b class="mr-2">Review : </b> {!! $review->Content !!}
    </p>
    <hr>
    @if($party instanceof \App\Models\BR\Client)
        @include('snippets.client_summary', ['client'=>$party])
    @elseif($party instanceof \App\Models\Lead)
        @include('snippets.lead_summary', ['lead'=>$party])
    @else
        <div class="row">
            <div class="col-5 col-sm-5 col-md-12 col-lg-12 col-xl-12 col-xxl-5 align-content-center text-center">
                <div>
                    {!! (new \App\Models\BR\Client)->getImage('class="img-fluid rounded-circle mb-2" width="128" height="128"') !!}
                </div>
            </div>
            <div class="col-7 col-sm-7 col-md-12 col-lg-12 col-xl-12 col-xxl-7 align-content-center">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item p-1">
                        <h4>{{ $review->Party }}</h4>
                    </li>
                    <li class="list-group-item p-1">Anonymous</li>
                </ul>

            </div>
        </div>
    @endif
    <hr class="mx-0 my-2">
    <ul class="list-group list-group-flush">
        <li class="list-group-item">Sentiment: <span class="float-end">{{  $review->Tonality->name  }}</span></li>
        <li class="list-group-item">Rate : <span class="float-end">{{  $review->Rating  }} / 5</span></li>
        <li class="list-group-item">Source: <span class="float-end">{{ $review->Source }}</span></li>
    </ul>
</div>
<div class="m-auto">
    @include('snippets.behind_scenes',['model'=>$review])
</div>
