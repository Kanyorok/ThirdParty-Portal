<div class="d-flex flex-column">
    <h3 class="text-center text-decoration-underline">
        {{ \Illuminate\Support\Str::upper($activity->PlannerActivityID) }}
    </h3>

    <ul class="list-group list-group-flush">
        <li class="list-group-item">Name: <span class="float-end">{{ $activity->Name }}</span></li>
        <li class="list-group-item">Location: <span class="float-end">{{ $activity->Location }}</span></li>
        <!--<li class="list-group-item">Branch: <span class="float-end">{{ $activity->branch?->BranchName }}</span></li>-->
        <li class="list-group-item">Start: <span
                class="float-end">{{ $activity->StartOn?->format('M d, Y H:i') }}</span></li>
        <li class="list-group-item">End: <span class="float-end">{{ $activity->EndOn?->format('M d, Y H:i') }}</span>
        </li>
        <li class="list-group-item">Budget: <span class="float-end">{{ number_format($activity->Budget, 2) }}</span>
        </li>
    </ul>

    <p class="mt-3"><strong>Materials:</strong> {{ $activity->Materials }}</p>

    <hr class="m-0">
    <p class="mb-1 h4">Users</p>

    @if($users->count() > 0)
        <div id="users-wrapper">
            <div class="row g-2">
                @foreach($users as $user)
                    <div class="col-4 text-center">
                        {!! $user?->getImage('class="img-fluid rounded-circle mb-2" style="height: 70px; width: 70px;"') !!}
                        <h6 class="text-truncate" title="{{ $user->Name }}">{{ $user->UserID }}</h6>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <h6 class="text-info my-3">No Users Found</h6>
    @endif

    <p class="mt-3"><strong>Notes:</strong> {{ $activity->Notes }}</p>

    <div class="mt-3">
        @include('snippets.behind_scenes', ['model' => $activity])
    </div>
</div>

@push('styles')
    <style>
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endpush
