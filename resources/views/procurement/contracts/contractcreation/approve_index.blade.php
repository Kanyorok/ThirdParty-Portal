@extends('layouts.app')
@section('title', 'Contract Approval Queue')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">✅ Contract Approval Queue</h4>
                        <p class="text-muted mb-0">Review and approve contracts awaiting sign-off</p>
                    </div>
                    <div>
                        <a href="{{ route('contracts.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list"></i> All Contracts
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

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h5>{{ isset($pendingCount) ? $pendingCount : 0 }}</h5>
                                <small>Pending Approval</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h5>{{ isset($approvedCount) ? $approvedCount : 0 }}</h5>
                                <small>Approved</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-file-contract fa-2x mb-2"></i>
                                <h5>{{ isset($totalCount) ? $totalCount : 0 }}</h5>
                                <small>Total in Queue</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status_filter" class="form-select">
                                    <option value="">All Status</option>
                                    <option
                                        value="Draft Created" {{ request('status_filter') === 'Draft Created' ? 'selected' : '' }}>
                                        Pending Review
                                    </option>
                                    <option
                                        value="Under Review" {{ request('status_filter') === 'Under Review' ? 'selected' : '' }}>
                                        Under Review
                                    </option>
                                    <option
                                        value="Approved" {{ request('status_filter') === 'Approved' ? 'selected' : '' }}>
                                        Approved
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search by contract ref, title, or supplier..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contracts Approval Table -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Contract Info</th>
                                    <th>Tender Details</th>
                                    <th>Supplier</th>
                                    <th>Value</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($contracts ?? [] as $index => $contract)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div>
                                                <strong
                                                    class="text-primary">{{ $contract->ContractRef ?? 'PENDING' }}</strong>
                                                @if($contract->CreatedOn)
                                                    <div class="text-muted small">
                                                        Created: {{ $contract->CreatedOn->format('d M Y') }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($contract->tender)
                                                <div>
                                                    <strong>{{ $contract->tender->TenderNo }}</strong>
                                                    <div class="text-muted small">
                                                        {{ Str::limit($contract->tender->Title, 40) }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($contract->winningSupplier && $contract->winningSupplier->thirdParty)
                                                <div>
                                                    <strong>{{ $contract->winningSupplier->thirdParty->TradingName ?? $contract->winningSupplier->thirdParty->Name }}</strong>
                                                    @if($contract->winningSupplier->thirdParty->ContactPerson)
                                                        <div class="text-muted small">
                                                            {{ $contract->winningSupplier->thirdParty->ContactPerson }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($contract->ContractValue)
                                                <strong class="text-success">
                                                    {{ number_format($contract->ContractValue, 2) }}
                                                </strong>
                                                <div class="text-muted small">
                                                    {{ is_object($contract->tender->Currency) ? $contract->tender->Currency->Code : ($contract->tender->Currency ?? 'KES') }}
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                                <span class="badge {{ $contract->contract_status_badge['class'] }}">
                                                    {{ $contract->contract_status_badge['text'] }}
                                                </span>
                                        </td>
                                        <td>
                                            @if($contract->ContractStartDate && $contract->ContractEndDate)
                                                <div class="text-success small">
                                                    <strong>Start:</strong> {{ $contract->ContractStartDate->format('d M Y') }}
                                                </div>
                                                <div class="text-danger small">
                                                    <strong>End:</strong> {{ $contract->ContractEndDate->format('d M Y') }}
                                                </div>
                                            @else
                                                <span class="text-muted">Duration not set</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('contracts.show', $contract->Id) }}"
                                                   class="btn btn-sm btn-outline-info" title="View Contract">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if($contract->ContractStatus === 'Under Review')
                                                    <button type="button" class="btn btn-sm btn-success"
                                                            onclick="approveContract({{ $contract->Id }})"
                                                            title="Approve Contract">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="rejectContract({{ $contract->Id }})"
                                                            title="Reject Contract">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                @elseif($contract->ContractStatus === 'Draft Created')
                                                    <span class="badge bg-secondary">Awaiting Review</span>
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="reviewContract({{ $contract->Id }})"
                                                            title="View Details">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                @elseif($contract->ContractStatus === 'Approved')
                                                    <span class="badge bg-success">
                                                            <i class="fas fa-check-circle"></i> Approved
                                                        </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h6 class="text-muted">No Contracts in Approval Queue</h6>
                                            <p class="text-muted">No contracts pending approval at this time.</p>
                                            <a href="{{ route('contracts.index') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create New Contract
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(isset($contracts) && $contracts->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $contracts->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Contract</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Approval Remarks</label>
                            <textarea name="approval_remarks" class="form-control" rows="3"
                                      placeholder="Enter approval remarks and any conditions..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Contract</button>
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
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Reject Contract</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This will return the contract to draft status for revision.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="4" required
                                      placeholder="Please explain why this contract is being rejected and what changes are needed..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Reject Contract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function approveContract(contractId) {
            const form = document.getElementById('approveForm');
            form.action = `{{ url('/procurement/contracts') }}/${contractId}/approve`;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }

        function rejectContract(contractId) {
            const form = document.getElementById('rejectForm');
            form.action = `{{ url('/procurement/contracts') }}/${contractId}/reject`;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        function reviewContract(contractId) {
            window.location.href = `{{ url('/procurement/contracts') }}/${contractId}`;
        }
    </script>
@endsection
