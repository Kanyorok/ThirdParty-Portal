<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Procurement: Consolidated Needs</h6>
        <a href="{{ route('dashboard.index') }}" class="btn btn-sm btn-outline-primary">Open</a>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <div class="text-muted">Records</div>
                <div class="h4 mb-0">{{ $stats['needs_total'] ?? 0 }}</div>
            </div>
            <div>
                <div class="text-muted">Approved</div>
                <div class="h5 mb-0 text-success">{{ $stats['needs_approved'] ?? 0 }}</div>
            </div>
            <div>
                <div class="text-muted">Pending</div>
                <div class="h5 mb-0 text-warning">{{ $stats['needs_pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>
