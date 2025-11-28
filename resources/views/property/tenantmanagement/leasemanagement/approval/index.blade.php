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

    </style>

    <h4 class="mb-4">🏢 Approval Queue</h4>

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
                                } else {
                                    $statusEnum = ApprovalEnum::tryFrom($lease->Status) ?? ApprovalEnum::tryFrom($lease->Status->value ?? null);
                                }
                            }
                        @endphp

                        <span class="status-badge badge bg-{{ $statusEnum?->badgeColor() ?? 'secondary' }}">
                            {{ $statusEnum?->label() ?? ($lease->Status->value ?? $lease->Status ?? 'N/A') }}
                        </span>
                    </td>

                    @php
                        $approvalEnum = null;
                        if (isset($lease->ApprovalStatus)) {
                            $approvalEnum = ApprovalEnum::tryFrom($lease->ApprovalStatus) ?? ApprovalEnum::tryFrom($lease->ApprovalStatus->value ?? null);
                        }
                    @endphp
                    <td>
                        <strong class="badge bg-{{ $approvalEnum?->badgeColor() ?? 'secondary' }}">
                            {{ $approvalEnum?->label() ?? ($lease->ApprovalStatus ?? 'N/A') }}
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
                                                                $approvalEnum = \App\Enums\Core\ApprovalEnum::tryFrom($lease->ApprovalStatus) ?? \App\Enums\Core\ApprovalEnum::tryFrom($lease->ApprovalStatus->value ?? null);
                                                            }
                                                        @endphp
                                                        <span class="badge bg-{{ $approvalEnum?->badgeColor() ?? 'secondary' }}">{{ $approvalEnum?->label() ?? ($lease->ApprovalStatus ?? 'N/A') }}</span>
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
                <tr>
                    <td colspan="12" class="text-center p-3">No pending approvals found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
    });
</script>

@endsection
