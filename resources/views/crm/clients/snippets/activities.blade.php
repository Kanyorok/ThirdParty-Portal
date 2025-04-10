@foreach($activities as $activity)
    <div class="d-flex align-items-start"><div class="flex-grow-1">
        <small class="float-end text-navy">{{ $activity->CreatedOn->diffForHumans(short: true) }}</small>
        {{$activity->Notes }}<br />
        <small class="text-muted"> {{  $activity->CreatedOn->format('F d, Y h:i a') }}</small><br />
    </div></div><hr />
@endforeach

@if($activity_more<10)
    <p class="my-3 text-center">No more activities</p>
@endif
<script>
    $(function() {
        window.ClientActivitiesOldest = parseInt('{{ (isset($activity))?$activity->ActivityID:0 }}');
        window.ClientActivitiesHasMore = (parseInt('{{ $activity_more }}') > 10);
        if (!window.ClientActivitiesHasMore){
            $("#loadMoreBtn").addClass('d-none');
        }else{
            $("#loadMoreBtn").removeClass('d-none');
        }
    });
</script>
