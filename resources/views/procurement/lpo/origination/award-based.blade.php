@extends('layouts.app')
@section('title', 'Award-Based LPO Creation')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">🏆 Award-Based LPO Creation</h4>
                        <p class="text-muted mb-0">Create Local Purchase Orders from approved tender/RFQ awards</p>
                    </div>
                    <div>
                        <a href="{{ route('lpo.origination.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <a href="{{ route('awards.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-trophy"></i> Manage Awards
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Available Awards for LPO Creation -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-trophy"></i> Approved Awards Available for LPO Creation
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Award Details</th>
                                        <th>Tender/RFQ Information</th>
                                        <th>Winning Supplier</th>
                                        <th>Award Value</th>
                                        <th>Approval Status</th>
                                        <th>Award Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($availableAwards as $award)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong class="text-warning">Award #{{ $award->Id }}</strong>
                                                    @if($award->AwardNotes)
                                                        <div class="text-muted small">
                                                            <i class="fas fa-sticky-note me-1"></i>
                                                            {{ Str::limit($award->AwardNotes, 40) }}
                                                        </div>
                                                    @endif
                                                    @if($award->ApprovalRemarks)
                                                        <div class="text-success small">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            {{ Str::limit($award->ApprovalRemarks, 30) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($award->tender)
                                                    <div>
                                                        <strong>{{ $award->tender->TenderNo }}</strong>
                                                        <div class="text-muted small">
                                                            {{ Str::limit($award->tender->Title, 50) }}
                                                        </div>
                                                        <div class="text-muted small">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            Opened: {{ $award->tender->OpeningDate?->format('d/m/Y') }}
                                                        </div>
                                                        <div class="text-muted small">
                                                            <i class="fas fa-tag me-1"></i>
                                                            {{ $award->tender->TenderType }} • {{ $award->tender->TenderCategory }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($award->winningSupplier)
                                                    <div>
                                                        <strong>{{ $award->winningSupplier->SupplierName }}</strong>
                                                        @if($award->winningSupplier->ContactPerson)
                                                            <div class="text-muted small">
                                                                <i class="fas fa-user me-1"></i>
                                                                {{ $award->winningSupplier->ContactPerson }}
                                                            </div>
                                                        @endif
                                                        @if($award->winningSupplier->PhoneNumber)
                                                            <div class="text-muted small">
                                                                <i class="fas fa-phone me-1"></i>
                                                                {{ $award->winningSupplier->PhoneNumber }}
                                                            </div>
                                                        @endif
                                                        @if($award->winningSupplier->Email)
                                                            <div class="text-muted small">
                                                                <i class="fas fa-envelope me-1"></i>
                                                                {{ $award->winningSupplier->Email }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($award->AwardAmount)
                                                    <strong class="text-success">
                                                        {{ number_format($award->AwardAmount, 2) }}
                                                    </strong>
                                                    <div class="text-muted small">
                                                        {{ is_object($award->tender->Currency) ? $award->tender->Currency->Code : ($award->tender->Currency ?? 'KES') }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($award->is_approved)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Approved
                                                    </span>
                                                    @if($award->ApprovedBy)
                                                        <div class="text-muted small">
                                                            By: User #{{ $award->ApprovedBy }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($award->ApprovedOn)
                                                    <div class="text-success small">
                                                        <i class="fas fa-calendar-check me-1"></i>
                                                        {{ $award->ApprovedOn->format('d/m/Y') }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        {{ $award->ApprovedOn->format('H:i') }}
                                                    </div>
                                                @elseif($award->CreatedOn)
                                                    <div class="text-muted small">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        {{ $award->CreatedOn->format('d/m/Y') }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    @if($award->is_approved)
                                                        <a href="{{ route('lpo.create.award', $award->Id) }}" 
                                                           class="btn btn-sm btn-warning text-dark" title="Create LPO from Award">
                                                            <i class="fas fa-plus-circle"></i> Create LPO
                                                        </a>
                                                    @else
                                                        <span class="btn btn-sm btn-outline-secondary disabled" title="Award not yet approved">
                                                            <i class="fas fa-clock"></i> Pending Approval
                                                        </span>
                                                    @endif
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle dropdown-toggle-split" 
                                                            data-bs-toggle="dropdown" title="More Actions">
                                                        <span class="visually-hidden">Toggle Dropdown</span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('awards.show', $award->Id) }}">
                                                                <i class="fas fa-eye me-2"></i>View Award Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item" 
                                                                    onclick="showAwardSummary({{ $award->Id }})">
                                                                <i class="fas fa-info-circle me-2"></i>Award Summary
                                                            </button>
                                                        </li>
                                                        @if($award->tender)
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('tender.show', $award->tender->Id) }}">
                                                                    <i class="fas fa-file-alt me-2"></i>View Tender/RFQ
                                                                </a>
                                                            </li>
                                                        @endif
                                                        @if($award->AwardNotes)
                                                            <li>
                                                                <button type="button" class="dropdown-item" 
                                                                        onclick="showAwardNotes('{{ addslashes($award->AwardNotes) }}')">
                                                                    <i class="fas fa-sticky-note me-2"></i>Award Notes
                                                                </button>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No Awards Available for LPO Creation</h6>
                                                <p class="text-muted">No approved awards that don't require contracts are available at this time.</p>
                                                <div class="mt-3">
                                                    <a href="{{ route('awards.index') }}" class="btn btn-primary me-2">
                                                        <i class="fas fa-trophy"></i> Manage Awards
                                                    </a>
                                                    <a href="{{ route('lpo.origination.contract-based') }}" class="btn btn-outline-success">
                                                        <i class="fas fa-file-contract"></i> Try Contract-Based LPO
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($availableAwards->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $availableAwards->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Information Panel -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-info-circle"></i> Award-Based LPO Guidelines
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-warning">✅ When to Use:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Award has been approved by procurement committee
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Award value is below contract threshold
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                One-time or infrequent procurement
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check text-success me-2"></i>
                                                Goods/services can be delivered immediately
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-warning">📋 Requirements:</h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Award must be in 'Approved' status
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Winning supplier must be confirmed
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Award should not already have a contract
                                            </li>
                                            <li class="mb-1">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                Supplier details must be complete
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-chart-pie"></i> Award Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-warning mb-0">{{ $availableAwards->total() }}</h4>
                                        <small class="text-muted">Total Awards</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-0">
                                            {{ $availableAwards->where('is_approved', true)->count() }}
                                        </h4>
                                        <small class="text-muted">Approved</small>
                                    </div>
                                </div>
                                <hr>
                                @if($availableAwards->count() > 0)
                                    <div class="small">
                                        <div class="d-flex justify-content-between">
                                            <span>Average Award Value:</span>
                                            <span class="text-success">
                                                {{ number_format($availableAwards->avg('AwardAmount'), 0) }}
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Total Award Value:</span>
                                            <span class="text-primary">
                                                {{ number_format($availableAwards->sum('AwardAmount'), 0) }}
                                            </span>
                                        </div>
                                    </div>
                                    <hr>
                                @endif
                                <div class="small text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    <strong>Tip:</strong> Award-based LPOs are ideal for smaller, immediate procurements below contract thresholds.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Award Summary Modal -->
    <div class="modal fade" id="awardSummaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Award Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="awardSummaryContent">
                    <!-- Award details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Award Notes Modal -->
    <div class="modal fade" id="awardNotesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Award Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="awardNotesContent">
                    <!-- Award notes will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAwardSummary(awardId) {
            const modal = new bootstrap.Modal(document.getElementById('awardSummaryModal'));
            document.getElementById('awardSummaryContent').innerHTML = `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();
            
            // TODO: Implement AJAX call to fetch award details
            setTimeout(() => {
                document.getElementById('awardSummaryContent').innerHTML = `
                    <p><strong>Award ID:</strong> ${awardId}</p>
                    <p>Additional award details can be loaded via AJAX here.</p>
                `;
            }, 1000);
        }
        
        function showAwardNotes(notes) {
            const modal = new bootstrap.Modal(document.getElementById('awardNotesModal'));
            document.getElementById('awardNotesContent').innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-sticky-note me-2"></i>
                    ${notes}
                </div>
            `;
            modal.show();
        }
    </script>
@endsection
