@php
    use Carbon\Carbon;
    use App\Enums\Core\ApprovalEnum;
@endphp
@extends('layouts.app')
@section('title', 'Lease Approval Queue')

@section('content')

<div class="card p-4 shadow-sm rounded-4">

    <style>
        /* Tabs & Table Styles */
        .font-size th {
            font-size: 0.85rem !important;
        }
        .nav-tabs .nav-link {
            color: #495057;
            border: 1px solid transparent;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
        }
        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: #89CFF0;
        }
        .lease-table tbody tr:hover {
            background-color: #f1f5f9;
        }
        .status-badge, .badge {
            font-size: 0.8rem;
            padding: 0.35em 0.6em;
        }

        /* Modal Styles */
        .modal-header {
            background-color: #89CFF0;
            color: white;
            border-bottom: none;
            font-weight: 500;
        }
        .modal-title {
            font-size: 1.25rem;
        }
        .modal-body {
            font-family: 'Segoe UI', sans-serif;
        }
        .reject-area {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #fff3f3;
            border: 1px solid #f5c2c7;
            border-radius: 0.5rem;
            transition: all 0.3s ease-in-out;
        }
        .modal-footer {
            justify-content: flex-end;
            gap: 0.5rem;
        }
        .btn-approve {
            background-color: #198754;
            border-color: #198754;
        }
        .btn-reject {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .btn-reject:hover, .btn-approve:hover {
            opacity: 0.9;
        }
        .section-title {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .info-row {
            margin-bottom: 0.5rem;
        }
        .info-row div {
            margin-bottom: 0.25rem;
        }
    </style>

    <h4 class="mb-4">🏢 Approval Queue</h4>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="approvalTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="newlease-tab" data-bs-toggle="tab" data-bs-target="#newlease" type="button" role="tab" aria-controls="newlease" aria-selected="true">
                New Lease <span class="badge bg-info ms-2">{{ count($approvals) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="renewal-tab" data-bs-toggle="tab" data-bs-target="#renewal"
                type="button" role="tab" aria-controls="renewal" aria-selected="false">
                Lease Renewal <span class="badge bg-primary ms-2">{{ count($renewalapprovals) }}</span>
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

        {{-- New Lease Tab --}}
        <div class="tab-pane fade show active" id="newlease" role="tabpanel" aria-labelledby="newlease-tab">
            <div class="table-responsive lease-table-wrapper">
                <table id="lease-approval" class="table table-hover table-bordered lease-table align-middle text-center">
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
                            <td>{{ $lease->tenant->thirdParty->ThirdPartyName ?? 'N/A' }}</td>
                            <td>{{ $lease->property->PropertyName ?? 'N/A' }}</td>
                            <td>{{ $lease->unit->UnitCode ?? 'N/A' }}</td>
                            <td>{{ number_format($lease->MonthlyRent ?? 0, 2, '.', ',') }}</td>
                            <td>{{ $lease->StartDate ? Carbon::parse($lease->StartDate)->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $lease->EndDate ? Carbon::parse($lease->EndDate)->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                @php
                                    $statusEnum = is_object($lease->Status) ? $lease->Status : ApprovalEnum::tryFrom($lease->Status);
                                @endphp
                                <span class="status-badge badge bg-{{ $statusEnum?->badgeColor() ?? 'secondary' }}">
                                    {{ $statusEnum?->label() ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $approvalEnum = is_object($lease->ApprovalStatus) ? $lease->ApprovalStatus : ApprovalEnum::tryFrom($lease->ApprovalStatus);
                                @endphp
                                <span class="badge bg-{{ $approvalEnum?->badgeColor() ?? 'secondary' }}">
                                    {{ $approvalEnum?->label() ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#leaseModal-{{ $lease->Id }}">
                                    View & Approve
                                </button>

                                {{-- Modal --}}
                                <div class="modal fade" id="leaseModal-{{ $lease->Id }}" tabindex="-1" aria-labelledby="leaseModalLabel-{{ $lease->Id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content border-0 shadow-sm">
                                            <div class="modal-header bg-info text-white">
                                                <div>
                                                    <h5 class="modal-title">Lease Offer Approval</h5>
                                                    <small>Lease No: {{ $lease->LeaseNumber ?? 'N/A' }}</small>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-4">
                                                {{-- Tenant & Property Info --}}
                                                <div class="mb-3">
                                                    <div class="section-title">Tenant & Property Details</div>
                                                    <div class="info-row row">
                                                        <div class="col-md-6"><strong>Tenant:</strong> {{ $lease->tenant->thirdParty->ThirdPartyName ?? 'N/A' }}</div>
                                                        <div class="col-md-6"><strong>Property:</strong> {{ $lease->property->PropertyName ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="info-row row">
                                                        <div class="col-md-6"><strong>Unit:</strong> {{ $lease->unit->UnitCode ?? 'N/A' }}</div>
                                                        <div class="col-md-6"><strong>Block / Floor:</strong> {{ $lease->block->BlockName ?? 'N/A' }} / {{ $lease->floor->FloorName ?? 'N/A' }}</div>
                                                    </div>
                                                </div>

                                                {{-- Lease Terms --}}
                                                <div class="mb-3">
                                                    <div class="section-title">Lease Terms</div>
                                                    <div class="info-row row">
                                                        <div class="col-md-6"><strong>Start Date:</strong> {{ $lease->StartDate ? Carbon::parse($lease->StartDate)->format('d/m/Y') : 'N/A' }}</div>
                                                        <div class="col-md-6"><strong>End Date:</strong> {{ $lease->EndDate ? Carbon::parse($lease->EndDate)->format('d/m/Y') : 'N/A' }}</div>
                                                    </div>
                                                    <div class="info-row row">
                                                        <div class="col-md-6"><strong>Rent Amount:</strong> KES {{ number_format($lease->MonthlyRent ?? 0, 2, '.', ',') }}</div>
                                                        <div class="col-md-6"><strong>Due Day:</strong> {{ $lease->DueDay ?? 'N/A' }}</div>
                                                    </div>
                                                </div>

                                                {{-- Financial Details --}}
                                                <div class="mb-3 p-3 bg-light border rounded">
                                                    <div class="section-title">Financial Details</div>
                                                    <div class="info-row row">
                                                        <div class="col-md-4"><strong>Deposit:</strong> KES {{ number_format($lease->Deposit ?? 0, 2, '.', ',') }}</div>
                                                        <div class="col-md-4"><strong>Service Charge:</strong> KES {{ number_format($lease->ServiceCharge ?? 0, 2, '.', ',') }}</div>
                                                        <div class="col-md-4"><strong>Parking Fee:</strong> KES {{ number_format($lease->ParkingFee ?? 0, 2, '.', ',') }}</div>
                                                    </div>
                                                    <div class="info-row row mt-2">
                                                        <div class="col-md-6"><strong>Other Charges:</strong> KES {{ number_format($lease->OtherCharges ?? 0, 2, '.', ',') }}</div>
                                                        <div class="col-md-6"><strong>Special Terms:</strong> {{ $lease->SpecialTerms ?? 'N/A' }}</div>
                                                    </div>
                                                </div>

                                                {{-- Approval Status --}}
                                                <div class="mb-3">
                                                    <strong>Approval Status:</strong>
                                                    <span class="badge bg-{{ $approvalEnum?->badgeColor() ?? 'secondary' }}">
                                                        {{ $approvalEnum?->label() ?? 'N/A' }}
                                                    </span>
                                                </div>

                                                {{-- Reject Area --}}
                                                <div id="reject-area-{{ $lease->Id }}" class="reject-area" style="display:none;">
                                                    <form method="POST" action="{{ route('propertyapproval.reject', $lease->Id) }}">
                                                        @csrf
                                                        <label for="reason-{{ $lease->Id }}" class="form-label">Reason for Rejection</label>
                                                        <textarea name="reason" id="reason-{{ $lease->Id }}" class="form-control mb-2" rows="3" required></textarea>
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button type="submit" class="btn btn-reject">Confirm Reject</button>
                                                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('reject-area-{{ $lease->Id }}').style.display='none'">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>

                                            </div>

                                            {{-- Footer --}}
                                            <div class="modal-footer">
                                                <form method="POST" action="{{ route('propertyapproval.approve', $lease->Id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-approve">Approve</button>
                                                </form>
                                                <button type="button" class="btn btn-outline-danger btn-reject" onclick="document.getElementById('reject-area-{{ $lease->Id }}').style.display='block'">Reject</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="11">No leases found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Lease Renewal --}}
        <div class="tab-pane fade" id="renewal" role="tabpanel" aria-labelledby="renewal-tab">
            <div class="table-responsive lease-table-wrapper">
                <table id="renewal-approval" class="table table-hover table-bordered lease-table align-middle text-center">
                    <thead class="table-light">
                        <tr class="font-size">
                            <th>#</th>
                            <th>Lease No.</th>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Unit</th>
                            <th>Old End Date</th>
                            <th>New Start</th>
                            <th>New End</th>
                            <th>New Rent</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($renewalapprovals as $renewal)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $renewal->lease->LeaseNumber }}</td>
                            <td>{{ $renewal->lease->tenant->thirdParty->ThirdPartyName }}</td>
                            <td>{{ $renewal->lease->property->PropertyName }}</td>
                            <td>{{ $renewal->lease->unit->UnitCode }}</td>
                            <td>{{ \Carbon\Carbon::parse($renewal->EndDateCurrentLease)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($renewal->NewStartDate)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($renewal->NewEndDate)->format('d/m/Y') }}</td>
                            <td>{{ number_format($renewal->NewMonthlyRent, 2) }}</td>

                            <td>
                                @php $status = ApprovalEnum::tryFrom($renewal->Status); @endphp
                                <span class="badge bg-{{ $status?->badgeColor() }}">{{ $status?->label() }}</span>
                            </td>

                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#renewalModal-{{ $renewal->Id }}">
                                    View & Approve
                                </button>

                                {{-- Modal --}}
                                <div class="modal fade" id="renewalModal-{{ $renewal->Id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">

                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title">Lease Renewal Approval</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <h6 class="mb-2">Tenant & Property Info</h6>
                                                <p><strong>Tenant:</strong> {{ $renewal->lease->tenant->thirdParty->ThirdPartyName }}</p>
                                                <p><strong>Property:</strong> {{ $renewal->lease->property->PropertyName }}</p>
                                                <p><strong>Unit:</strong> {{ $renewal->lease->unit->UnitCode }}</p>

                                                <h6 class="mt-3">Lease Period</h6>
                                                <p><strong>Previous End:</strong> {{ $renewal->EndDateCurrentLease }}</p>
                                                <p><strong>New Start:</strong> {{ $renewal->NewStartDate }}</p>
                                                <p><strong>New End:</strong> {{ $renewal->NewEndDate }}</p>

                                                <h6 class="mt-3">New Charges</h6>
                                                <p><strong>Rent:</strong> KES {{ number_format($renewal->NewMonthlyRent) }}</p>
                                                <p><strong>Service Charge:</strong> KES {{ number_format($renewal->ServiceCharge) }}</p>
                                                <p><strong>Parking Fee:</strong> KES {{ number_format($renewal->ParkingFee) }}</p>

                                                {{-- Reject area --}}
                                                <div id="renewal-reject-{{ $renewal->Id }}" class="reject-area" style="display:none;">
                                                    <form method="POST" action="{{ route('propertyapproval.rejectRenewal', $renewal->Id) }}">
                                                        @csrf
                                                        <label>Reason for Rejection</label>
                                                        <textarea name="reason" class="form-control" required></textarea>

                                                        <div class="mt-3 text-end">
                                                            <button type="submit" class="btn btn-danger">Confirm Reject</button>
                                                            <button type="button" class="btn btn-secondary"
                                                                onclick="document.getElementById('renewal-reject-{{ $renewal->Id }}').style.display='none'">
                                                                Cancel
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <form method="POST" action="{{ route('propertyapproval.approveRenewal', $renewal->Id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                </form>

                                                <button class="btn btn-outline-danger"
                                                    onclick="document.getElementById('renewal-reject-{{ $renewal->Id }}').style.display='block'">
                                                    Reject
                                                </button>

                                                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="11">No lease renewals pending approval.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Lease Termination Tab --}}
        <div class="tab-pane fade" id="termination" role="tabpanel" aria-labelledby="termination-tab">
            <div class="table-responsive lease-table-wrapper">
                <table id="termination-approval" class="table table-hover table-bordered lease-table align-middle text-center">
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
                            <td>{{ $termination->lease->tenant->thirdParty->ThirdPartyName ?? 'N/A' }}</td>
                            <td>{{ $termination->lease->property->PropertyName ?? 'N/A' }}</td>
                            <td>{{ $termination->lease->unit->UnitCode ?? 'N/A' }}</td>
                            <td>{{ $termination->TerminationDate ? Carbon::parse($termination->TerminationDate)->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $termination->Reason ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $statusEnum = is_object($termination->Status) ? $termination->Status : ApprovalEnum::tryFrom($termination->Status);
                                @endphp
                                <span class="badge bg-{{ $statusEnum?->badgeColor() ?? 'secondary' }}">{{ $statusEnum?->label() ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#terminationModal-{{ $termination->Id }}">
                                    View & Approve
                                </button>

                                {{-- Modal --}}
                                <div class="modal fade" id="terminationModal-{{ $termination->Id }}" tabindex="-1" aria-labelledby="terminationModalLabel-{{ $termination->Id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content border-0 shadow-sm">
                                            <div class="modal-header bg-primary text-white">
                                                <div>
                                                    <h5 class="modal-title">Lease Termination Approval</h5>
                                                    <small>Lease No: {{ $termination->lease->LeaseNumber ?? 'N/A' }}</small>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <div class="section-title">Tenant & Property Details</div>
                                                    <div class="info-row row">
                                                        <div class="col-md-6"><strong>Tenant:</strong> {{ $termination->lease->tenant->thirdParty->ThirdPartyName ?? 'N/A' }}</div>
                                                        <div class="col-md-6"><strong>Property:</strong> {{ $termination->lease->property->PropertyName ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="info-row row">
                                                        <div class="col-md-6"><strong>Unit:</strong> {{ $termination->lease->unit->UnitCode ?? 'N/A' }}</div>
                                                        <div class="col-md-6"><strong>Lease No:</strong> {{ $termination->lease->LeaseNumber ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="info-row row mt-2">
                                                        <div class="col-md-6"><strong>Termination Date:</strong> {{ $termination->TerminationDate ? Carbon::parse($termination->TerminationDate)->format('d/m/Y') : 'N/A' }}</div>
                                                        <div class="col-md-6"><strong>Reason:</strong> {{ $termination->Reason ?? 'N/A' }}</div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <strong>Status:</strong>
                                                    <span class="badge bg-{{ $statusEnum?->badgeColor() ?? 'secondary' }}">{{ $statusEnum?->label() ?? 'N/A' }}</span>
                                                </div>

                                                {{-- Reject Area --}}
                                                <div id="reject-termination-area-{{ $termination->Id }}" class="reject-area" style="display:none;">
                                                    <form method="POST" action="{{ route('propertyapproval.rejectTermination', $termination->Id) }}">
                                                        @csrf
                                                        <label for="reason-termination-{{ $termination->Id }}" class="form-label">Reason for Rejection</label>
                                                        <textarea name="reason" id="reason-termination-{{ $termination->Id }}" class="form-control mb-2" rows="3" required></textarea>
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button type="submit" class="btn btn-reject">Confirm Reject</button>
                                                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('reject-termination-area-{{ $termination->Id }}').style.display='none'">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            {{-- Footer --}}
                                            <div class="modal-footer">
                                                <form method="POST" action="{{ route('propertyapproval.approveTermination', $termination->Id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-approve">Approve</button>
                                                </form>
                                                <button type="button" class="btn btn-outline-danger btn-reject" onclick="document.getElementById('reject-termination-area-{{ $termination->Id }}').style.display='block'">Reject</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9">No lease terminations found.</td></tr>
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
            $('#lease-approval, #renewal-approval, #termination-approval').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true,
                autoWidth: false,
                language: { emptyTable: "No data available" }
            });
        });
    </script>

</div>

@endsection
