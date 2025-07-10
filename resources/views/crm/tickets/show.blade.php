<div class="d-flex flex-column">
    <h3 class="text-center text-decoration-underline">
        {{ \Illuminate\Support\Str::upper($activity->PlannerActivityID) }}
    </h3>

    <ul class="list-group list-group-flush">
        <li class="list-group-item">Name: <span class="float-end">{{ $activity->Name }}</span></li>
        <li class="list-group-item">Location: <span class="float-end">{{ $activity->Location }}</span></li>
        <li class="list-group-item">Branch: <span class="float-end">{{ $activity->branch?->BranchName }}</span></li>
        <li class="list-group-item">Start: <span class="float-end">{{ $activity->StartOn?->format('M d, Y H:i') }}</span></li>
        <li class="list-group-item">End: <span class="float-end">{{ $activity->EndOn?->format('M d, Y H:i') }}</span></li>
        <li class="list-group-item">Budget: <span class="float-end">{{ number_format($activity->Budget, 2) }}</span></li>
    </ul>

    <p class="mt-3"><strong>Materials:</strong> {{ $activity->Materials }}</p>

    <hr class="m-0">
    <p class="mb-1 h4">Users</p>

    @if($users->count() > 1)
        <div id="users-wrapper">
            <div class="row g-2" id="users-container">
                @foreach($users->take(3) as $user)
                    @include('snippets.user_card', ['user' => $user])
                @endforeach
            </div>

            @if($users->count() > 3)
                <div class="text-center mt-2">
                    <button type="button"
                            class="btn btn-link p-0"
                            id="showMoreBtn"
                            data-activity-id="{{ $activity->PlannerActivityID }}"
                            data-offset="3">
                        Show More
                    </button>

                    <button type="button"
                            class="btn btn-link p-0 d-none"
                            id="showLessBtn">
                        Show Less
                    </button>
                </div>
            @endif
        </div>
    @elseif($users->count() === 1)
        @include('snippets.user_summary', ['user' => $users->first()])
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

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("users-container");
            const showMoreBtn = document.getElementById("showMoreBtn");
            const showLessBtn = document.getElementById("showLessBtn");

            if (!showMoreBtn || !showLessBtn) return;

            showMoreBtn.addEventListener("click", function () {
                const activityId = this.dataset.activityId;
                let offset = parseInt(this.dataset.offset || 3);

                fetch(`/planner-activities/${activityId}/users?offset=${offset}&limit=6`)
                    .then(response => response.json())
                    .then(data => {
                        container.insertAdjacentHTML("beforeend", data.html);
                        offset += 6;
                        this.dataset.offset = offset;

                        if (!data.hasMore) {
                            showMoreBtn.classList.add('d-none');
                        }

                        showLessBtn.classList.remove('d-none');
                    }).catch(() => alert("Error loading users"));
            });

            showLessBtn.addEventListener("click", function () {
                const userCards = container.querySelectorAll(".user-card");
                userCards.forEach((card, index) => {
                    if (index >= 3) card.remove();
                });

                showMoreBtn.classList.remove('d-none');
                showMoreBtn.dataset.offset = 3;
                showLessBtn.classList.add('d-none');
            });
        });
    </script>
@endpush
