<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Pending Approvals</h6>
        <a href="{{ route('approvalinbox.index') }}" class="btn btn-sm btn-outline-secondary">Inbox</a>
    </div>
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            @forelse(($stats['pending_approvals'] ?? []) as $item)
                <li class="d-flex justify-content-between border-bottom py-1">
                    <span class="text-truncate" style="max-width: 70%">{{ $item['title'] }}</span>
                    <span class="badge bg-warning text-dark">{{ $item['count'] }}</span>
                </li>
            @empty
                <li class="text-muted">No pending approvals</li>
            @endforelse
        </ul>
    </div>
</div>
