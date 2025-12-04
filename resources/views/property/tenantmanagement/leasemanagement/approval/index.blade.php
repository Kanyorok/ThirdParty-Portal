@php
    use Carbon\Carbon;
    use App\Enums\Core\ApprovalEnum;
@endphp
@extends('layouts.app')
@section('title', 'Lease Approval Queue')

@section('content')

<div class="card p-4 shadow rounded-4">

    <style>
        .font-size th {
            font-size: 9.5px !important;
        }
        .nav-tabs .nav-link {
            color: #495057;
            border: 1px solid transparent;
        }
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #f8f9fa;
            border-color: #dee2e6 #dee2e6 #fff;
        }
    </style>

    <h4 class="mb-4">🏢 Approval Queue</h4>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="approvalTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="newlease-tab" data-bs-toggle="tab" data-bs-target="#newlease" type="button" role="tab" aria-controls="newlease" aria-selected="true">
                New Lease <span class="badge bg-info ms-2">{{ count($approvals) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="termination-tab" data-bs-toggle="tab" data-bs-target="#termination" type="button" role="tab" aria-controls="termination" aria-selected="false">
                Lease Termination <span class="badge bg-warning ms-2">{{ count($terminationapprovals) }}</span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="approvalTabsContent">
        <!-- New Lease Tab -->
        <div class="tab-pane fade show active" id="newlease" role="tabpanel" aria-labelledby="newlease-tab">
            <div class="lease-table-wrapper table-responsive">
                <table id="lease-approval" class="table table-hover table-bordered lease-table">
                    <thead class="table-light">
                        <tr class="font-size">
                            <th>#</th>
                            <th>Lease No.</th>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Unit</th>
                            <th>Rent</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Approval</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($approvals as $lease)
                
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $lease->LeaseNumber ?? 'N/A' }}</td>

                    <td>{{ $lease->tenant->TenantName ?? 'N/A' }}</td>

                    <td>{{ $lease->property->PropertyName ?? 'N/A' }}</td>

                    <td>{{ $lease->unit->UnitName ?? 'N/A' }}</td>

                    <td>
                        {{ number_format($lease->MonthlyRent ?? 0, 2, '.', ',') }}
                    </td>

                    <td>
                        {{ $lease->StartDate ? Carbon::parse($lease->StartDate)->format('d/m/Y') : 'N/A' }}
                    </td>

                    <td>
                        {{ $lease->EndDate ? Carbon::parse($lease->EndDate)->format('d/m/Y') : 'N/A' }}
                    </td>

                    <td>
                        @php
                            $statusEnum = null;
                            if (isset($lease->Status)) {
                                if (is_object($lease->Status) && method_exists($lease->Status, 'label')) {
                                    $statusEnum = $lease->Status;
                                } elseif (is_string($lease->Status) || is_int($lease->Status)) {
                                    $statusEnum = ApprovalEnum::tryFrom($lease->Status);
                                }
                            }
                        @endphp

                        <span class="status-badge badge bg-{{ $statusEnum?->badgeColor() ?? 'secondary' }}">
                            {{ $statusEnum?->label() ?? 'N/A' }}
                        </span>
                    </td>

                    @php
                        $approvalEnum = null;
                        if (isset($lease->ApprovalStatus)) {
                            if (is_object($lease->ApprovalStatus) && method_exists($lease->ApprovalStatus, 'label')) {
                                $approvalEnum = $lease->ApprovalStatus;
                            } elseif (is_string($lease->ApprovalStatus) || is_int($lease->ApprovalStatus)) {
                                $approvalEnum = ApprovalEnum::tryFrom($lease->ApprovalStatus);
                            }
                        }
                    @endphp
                    <td>
                        <strong class="badge bg-{{ $approvalEnum?->badgeColor() ?? 'secondary' }}">
                            {{ $approvalEnum?->label() ?? 'N/A' }}
                        </strong>
                    </td>

                    <td>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#leaseModal-{{ $lease->Id }}">
                            View & Approve
                        </button>

                        <!-- Lease Detail Modal -->
                        <div class="modal fade" id="leaseModal-{{ $lease->Id }}" tabindex="-1" aria-labelledby="leaseModalLabel-{{ $lease->Id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="leaseModalLabel-{{ $lease->Id }}">Lease {{ $lease->LeaseNumber ?? 'N/A' }} Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Tenant</th>
                                                    <td>{{ $lease->tenant->TenantName ?? 'N/A' }}</td>
                                                    <th>Property</th>
                                                    <td>{{ $lease->property->PropertyName ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Unit</th>
                                                    <td>{{ $lease->unit->UnitName ?? 'N/A' }}</td>
                                                    <th>Block / Floor</th>
                                                    <td>{{ $lease->block->BlockName ?? 'N/A' }} / {{ $lease->floor->FloorName ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Lease No.</th>
                                                    <td>{{ $lease->LeaseNumber ?? 'N/A' }}</td>
                                                    <th>Rent</th>
                                                    <td>{{ number_format($lease->MonthlyRent ?? 0, 2, '.', ',') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Start Date</th>
                                                    <td>{{ $lease->StartDate ? \Carbon\Carbon::parse($lease->StartDate)->format('d/m/Y') : 'N/A' }}</td>
                                                    <th>End Date</th>
                                                    <td>{{ $lease->EndDate ? \Carbon\Carbon::parse($lease->EndDate)->format('d/m/Y') : 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Deposit</th>
                                                    <td>{{ number_format($lease->Deposit ?? 0, 2, '.', ',') }}</td>
                                                    <th>Service Charge</th>
                                                    <td>{{ number_format($lease->ServiceCharge ?? 0, 2, '.', ',') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Parking Fee</th>
                                                    <td>{{ number_format($lease->ParkingFee ?? 0, 2, '.', ',') }}</td>
                                                    <th>Other Charges</th>
                                                    <td>{{ number_format($lease->OtherCharges ?? 0, 2, '.', ',') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Due Day</th>
                                                    <td>{{ $lease->DueDay ?? 'N/A' }}</td>
                                                    <th>Special Terms</th>
                                                    <td>{{ $lease->SpecialTerms ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Approval Status</th>
                                                    <td colspan="3">
                                                        @php
                                                            $approvalEnum = null;
                                                            if (isset($lease->ApprovalStatus)) {
                                                                if (is_object($lease->ApprovalStatus) && method_exists($lease->ApprovalStatus, 'label')) {
                                                                    $approvalEnum = $lease->ApprovalStatus;
                                                                } elseif (is_string($lease->ApprovalStatus) || is_int($lease->ApprovalStatus)) {
                                                                    $approvalEnum = \App\Enums\Core\ApprovalEnum::tryFrom($lease->ApprovalStatus);
                                                                }
                                                            }
                                                        @endphp
                                                        <span class="badge bg-{{ $approvalEnum?->badgeColor() ?? 'secondary' }}">{{ $approvalEnum?->label() ?? 'N/A' }}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div id="reject-area-{{ $lease->Id }}" style="display:none;">
                                            <form method="POST" action="{{ route('propertyapproval.reject', $lease->Id) }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="reason-{{ $lease->Id }}" class="form-label">Rejection reason</label>
                                                    <textarea name="reason" id="reason-{{ $lease->Id }}" class="form-control" rows="4" required></textarea>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-danger">Confirm Reject</button>
                                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('reject-area-{{ $lease->Id }}').style.display='none'">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <form method="POST" action="{{ route('propertyapproval.approve', $lease->Id) }}" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Approve</button>
                                        </form>

                                        <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('reject-area-{{ $lease->Id }}').style.display='block'">Reject</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lease Termination Tab -->
        <div class="tab-pane fade" id="termination" role="tabpanel" aria-labelledby="termination-tab">
            <div class="lease-table-wrapper table-responsive">
                <table id="termination-approval" class="table table-hover table-bordered lease-table">
                    <thead class="table-light">
                        <tr class="font-size">
                            <th>#</th>
                            <th>Lease No.</th>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Unit</th>
                            <th>Termination Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($terminationapprovals as $termination)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $termination->lease->LeaseNumber ?? 'N/A' }}</td>
                            <td>{{ $termination->lease->tenant->TenantName ?? 'N/A' }}</td>
                            <td>{{ $termination->lease->property->PropertyName ?? 'N/A' }}</td>
                            <td>{{ $termination->lease->unit->UnitName ?? 'N/A' }}</td>
                            <td>
                                {{ $termination->TerminationDate ? Carbon::parse($termination->TerminationDate)->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td>{{ $termination->Reason ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $statusEnum = null;
                                    if (isset($termination->Status)) {
                                        if (is_object($termination->Status) && method_exists($termination->Status, 'label')) {
                                            $statusEnum = $termination->Status;
                                        } elseif (is_string($termination->Status) || is_int($termination->Status)) {
                                            $statusEnum = ApprovalEnum::tryFrom($termination->Status);
                                        }
                                    }
                                @endphp
                                <span class="status-badge badge bg-{{ $statusEnum?->badgeColor() ?? 'secondary' }}">
                                    {{ $statusEnum?->label() ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#terminationModal-{{ $termination->Id }}">
                                    View & Approve
                                </button>

                                <!-- Termination Detail Modal -->
                                <div class="modal fade" id="terminationModal-{{ $termination->Id }}" tabindex="-1" aria-labelledby="terminationModalLabel-{{ $termination->Id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="terminationModalLabel-{{ $termination->Id }}">Lease Termination {{ $termination->lease->LeaseNumber ?? 'N/A' }} Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <table class="table table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <th>Tenant</th>
                                                            <td>{{ $termination->lease->tenant->TenantName ?? 'N/A' }}</td>
                                                            <th>Property</th>
                                                            <td>{{ $termination->lease->property->PropertyName ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Unit</th>
                                                            <td>{{ $termination->lease->unit->UnitName ?? 'N/A' }}</td>
                                                            <th>Lease No.</th>
                                                            <td>{{ $termination->lease->LeaseNumber ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Termination Date</th>
                                                            <td>{{ $termination->TerminationDate ? Carbon::parse($termination->TerminationDate)->format('d/m/Y') : 'N/A' }}</td>
                                                            <th>Reason</th>
                                                            <td>{{ $termination->Reason ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Status</th>
                                                            <td colspan="3">
                                                                @php
                                                                    $statusEnum = null;
                                                                    if (isset($termination->Status)) {
                                                                        if (is_object($termination->Status) && method_exists($termination->Status, 'label')) {
                                                                            $statusEnum = $termination->Status;
                                                                        } elseif (is_string($termination->Status) || is_int($termination->Status)) {
                                                                            $statusEnum = \App\Enums\Core\ApprovalEnum::tryFrom($termination->Status);
                                                                        }
                                                                    }
                                                                @endphp
                                                                <span class="badge bg-{{ $statusEnum?->badgeColor() ?? 'secondary' }}">{{ $statusEnum?->label() ?? 'N/A' }}</span>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <div id="reject-termination-area-{{ $termination->Id }}" style="display:none;">
                                                    <form method="POST" action="{{ route('propertyapproval.rejectTermination', $termination->Id) }}">
                                                        @csrf
                                                        <div class="mb-3">
                                                            <label for="reason-termination-{{ $termination->Id }}" class="form-label">Rejection reason</label>
                                                            <textarea name="reason" id="reason-termination-{{ $termination->Id }}" class="form-control" rows="4" required></textarea>
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            <button type="submit" class="btn btn-danger">Confirm Reject</button>
                                                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('reject-termination-area-{{ $termination->Id }}').style.display='none'">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <form method="POST" action="{{ route('propertyapproval.approveTermination', $termination->Id) }}" class="m-0">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                </form>

                                                <button type="button" class="btn btn-outline-danger" onclick="document.getElementById('reject-termination-area-{{ $termination->Id }}').style.display='block'">Reject</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

{{-- DataTables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#lease-approval').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            autoWidth: false,
            language: {
                emptyTable: "No lease approvals found."
            }
        });

        $('#termination-approval').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true,
            autoWidth: false,
            language: {
                emptyTable: "No termination approvals found."
            }
        });
    });
</script>

@endsection
