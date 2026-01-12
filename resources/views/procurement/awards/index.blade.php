@extends('layouts.app')
@section('title', 'Tender & RFQ Awards Overview')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')

    <div class="container mt-4">
        <h4 class="mb-3">🏆 Tender & RFQ Awards Overview</h4>

        <!-- Filters -->
        <form method="GET" class="row g-3 mb-3">
            <div class="col-md-3">
                <select name="status_filter" class="form-select">
                    <option value="">All Status</option>
                    <option value="Pending" {{ ($filters['status_filter'] ?? '') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Awarded" {{ ($filters['status_filter'] ?? '') === 'Awarded' ? 'selected' : '' }}>Awarded</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search Ref / Title / Supplier" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Awards Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="awardsTable" class="table table-bordered align-middle">
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
                        @forelse($items as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($row['type'] === 'rfq' && !empty($row['t_RFQ']['RefNo']))
                                        {{ $row['t_RFQ']['RefNo'] }}
                                    @else
                                        @php
                                            $ref = $row['ref_no'] ?? '';
                                            // If this looks like a numeric Id and it's a tender, try to resolve to TenderNo - Title
                                            if(($row['type'] ?? '') === 'tender' && is_numeric($ref)) {
                                                try {
                                                    $t = \App\Models\Procurement\Tender::find((int)$ref);
                                                    if($t) {
                                                        $ref = trim((($t->TenderNo ?? '') . ' - ' . ($t->Title ?? '')));
                                                    }
                                                } catch (\Throwable $e) {
                                                    // swallow errors and keep original ref
                                                }
                                            }
                                        @endphp
                                        {{ $ref }}
                                    @endif
                                </td>
                                <td>
                                    @if($row['type'] === 'rfq' && !empty($row['t_RFQ']['Comments']))
                                        {{ $row['t_RFQ']['Comments'] }}
                                    @else
                                        {{ $row['title'] }}
                                    @endif
                                </td>
                                <td>
                                    @if($row['type'] === 'tender')
                                        <span class="badge bg-primary">Tender</span>
                                    @else
                                        <span class="badge bg-success">RFQ</span>
                                    @endif
                                </td>
                                <td><span class="badge {{ $row['status_class'] }}">{{ $row['status'] }}</span></td>
                                <td>{{ $row['winning_bidder'] ?? '--' }}</td>
                                <td>{{ $row['award_date'] ?? '--' }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">

                                        @if($row['type'] === 'tender' && !empty($row['tender_id']))
                                            <a href="{{ route('awards.tender', $row['tender_id']) }}"
                                               class="btn btn-sm btn-outline-info" title="View Award Details">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        @elseif($row['type'] === 'rfq' && !empty($row['rfq_id']))
                                            <a href="{{ route('awards.rfq', $row['rfq_id']) }}"
                                               class="btn btn-sm btn-outline-info" title="View Award Details">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        @else
                                            <span class="btn btn-sm btn-outline-secondary disabled" title="Missing reference id">View</span>
                                        @endif

                                        {{-- Workflow Action Buttons --}}
                                        @if($row['type'] === 'tender' && !empty($row['award_id']))
                                            @if(in_array($row['status'], ['Draft', 'Pending']))
                                                {{-- Submit for Approval Button --}}
                                                <form action="{{ route('awards.submit-approval', $row['award_id']) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary" title="Submit for Approval">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @elseif(in_array($row['status'], ['Submitted for Approval', 'Under Review']))
                                                {{-- Approve/Reject Buttons (only if user can approve) --}}
                                                @if($row['can_approve'] ?? false)
                                                    <button type="button" class="btn btn-sm btn-success"
                                                            onclick="approveAward('tender', {{ $row['award_id'] }})" title="Approve Award">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            onclick="rejectAward({{ $row['award_id'] }})" title="Reject Award">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @else
                                                    <span class="badge bg-info text-white">Awaiting Approval</span>
                                                @endif
                                            @elseif($row['status'] === 'Approved')
                                                @php
                                                    $award = \App\Models\Procurement\TenderAward::find($row['award_id']);
                                                    $hasContract = $award && $award->hasContract();
                                                @endphp
                                                @if($hasContract)
                                                    <a href="{{ route('contracts.show', $row['award_id']) }}"
                                                       class="btn btn-sm btn-info" title="View Contract">
                                                        <i class="fas fa-file-contract"></i> Contract
                                                    </a>
                                                @else
                                                    <a href="{{ route('contracts.createFromAward', $row['award_id']) }}"
                                                       class="btn btn-sm btn-primary" title="Create Contract">
                                                        <i class="fas fa-file-contract"></i> Create Contract
                                                    </a>
                                                @endif
                                            @elseif($row['status'] === 'Rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        @elseif($row['type'] === 'rfq')
                                            {{-- RFQ awards - keep existing logic for now --}}
                                            @if($row['status'] === 'Pending')
                                                <button type="button" class="btn btn-sm btn-success"
                                                        onclick="approveAward('rfq', {{ $row['rfq_id'] ?? $row['id'] }})" title="Approve Award">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
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

        <!-- Client-side pagination handled by DataTables -->
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="approveForm" method="POST">
                    @csrf
                    <input type="hidden" name="award_id" id="approvalAwardId" value="">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Award</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Approval Remarks <span class="text-danger">*</span></label>
                            <textarea name="remarks" class="form-control" rows="3" required
                                      placeholder="Enter remarks for this approval..."></textarea>
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
                    <input type="hidden" name="award_id" id="rejectionAwardId" value="">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Award</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="remarks" class="form-control" rows="3" required
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
        function approveAward(type, id) {
            const form = document.getElementById('approveForm');
            const awardIdInput = document.getElementById('approvalAwardId');
            
            // Set the award_id in the hidden field
            awardIdInput.value = id;
            
            if (type === 'rfq') {
                form.action = `{{ route('awards.rfq.approve', ':id') }}`.replace(':id', id);
            } else {
                form.action = `{{ route('awards.approve', ':id') }}`.replace(':id', id);
            }
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }
        function rejectAward(awardId) {
            const form = document.getElementById('rejectForm');
            const awardIdInput = document.getElementById('rejectionAwardId');
            
            // Set the award_id in the hidden field
            awardIdInput.value = awardId;
            
            form.action = `{{ route('awards.reject', ':id') }}`.replace(':id', awardId);
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
    </script>

@endsection
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Check if table has data rows (excluding the empty state row)
            const hasData = $('#awardsTable tbody tr').length > 0 && 
                           !$('#awardsTable tbody tr td[colspan]').length;
            
            if (hasData) {
                $('#awardsTable').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    ordering: true,
                    searching: false,
                    info: true,
                    autoWidth: false,
                    language: { 
                        emptyTable: "No awards found" 
                    },
                    columnDefs: [
                        { orderable: false, targets: [7] } // Disable sorting on Actions column
                    ]
                });
            }
        });
    </script>
@endpush

