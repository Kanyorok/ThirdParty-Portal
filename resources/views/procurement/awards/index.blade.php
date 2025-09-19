@extends('layouts.app')
@section('title', 'Tender & RFQ Awards Overview')
@section('content')

    <div class="container mt-4">
        <h4 class="mb-3">🏆 Tender & RFQ Awards Overview</h4>

        <!-- Filters -->
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-3">
                <select name="status_filter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Pending" {{ ($filters['status_filter'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ ($filters['status_filter'] ?? '') === 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ ($filters['status_filter'] ?? '') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="Cancelled" {{ ($filters['status_filter'] ?? '') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search Tender Ref / Title" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Awards Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">

                        <tr>
                            <th>#</th>
                            <th>Ref No.</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Winning Bidder</th>
                            <th>Date Awarded</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($awards as $index => $award)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $award->tender->TenderNo ?? 'N/A' }}</td>
                                <td>{{ $award->tender->Title ?? 'N/A' }}</td>
                                <td>
                                    @if($award->tender->TenderType === 'Open' || $award->tender->TenderType === 'Restricted')
                                        <span class="badge bg-info">Tender</span>
                                    @else
                                        <span class="badge bg-secondary">RFQ</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $award->status_badge['class'] }}">
                                        {{ $award->status_badge['text'] }}
                                    </span>
                                </td>
                                <td>{{ $award->winningSupplier->SupplierName ?? '--' }}</td>
                                <td>{{ $award->AwardDate ? $award->AwardDate->format('Y-m-d') : '--' }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('awards.unified', $award->tender->Id) }}" 
                                           class="btn btn-sm btn-outline-info" title="View Award Details">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        
                                        @if($award->is_pending)
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="approveAward({{ $award->Id }})" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="rejectAward({{ $award->Id }})" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @elseif($award->is_approved)
                                            <a href="{{ route('contracts.createFromAward', $award->Id) }}" 
                                               class="btn btn-sm btn-primary" title="Create Contract">
                                                <i class="fas fa-file-contract"></i> Contract
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No awards found</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        @if($awards->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $awards->links() }}
            </div>
        @endif
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Award</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Approval Remarks (Optional)</label>
                            <textarea name="approval_remarks" class="form-control" rows="3" 
                                      placeholder="Enter any additional remarks for this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Award</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Award</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required
                                      placeholder="Please provide a reason for rejecting this award..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Award</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function approveAward(awardId) {
            const form = document.getElementById('approveForm');
            form.action = `{{ route('awards.approve', ':id') }}`.replace(':id', awardId);
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }
        
        function rejectAward(awardId) {
            const form = document.getElementById('rejectForm');
            form.action = `{{ route('awards.reject', ':id') }}`.replace(':id', awardId);
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
    </script>

@endsection

