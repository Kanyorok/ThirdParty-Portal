@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>Requisition Approval</h3>
            </div>

            <div class="card-body">
                <!-- Requisition Summary Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Requisition Information</h5>
                        <p><strong>Requisition Number:</strong> {{ $requisitionInfo->RequisitionNo ?? 'N/A' }}</p>
                        <p><strong>Date:</strong> {{ isset($requisitionInfo->CreatedOn) ? \Carbon\Carbon::parse($requisitionInfo->CreatedOn)->format('Y-m-d') : '' }}</p>
                        <p><strong>Branch:</strong> {{ $requisitionInfo->BranchID ?? 'N/A' }}</p>
                        <p><strong>Department:</strong> {{ $requisitionInfo->DepartmentID ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-6">
                        <h5>Additional Details</h5>
                        <p><strong>Procurement Plan:</strong> {{ $requisitionInfo->PlanTitle ?? 'N/A' }}</p>
                        <p><strong>Status:</strong> {{$requisitionInfo->Status}}</p>
                        <p><strong>Requested By:</strong> {{ $requisitionInfo->CreatedBy ?? 'N/A' }}</p>
                        <p><strong>Remarks:</strong> {{ $requisitionInfo->Remarks ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Item Type</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>UOM</th>
                            <th>Urgency</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($requisitionlineInfo as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->Type ?? 'N/A' }}</td>
                                <td>{{ $item->ItemName ?? 'N/A' }}</td>
                                <td>{{ $item->Quantity }}</td>
                                <td>{{ $item->UOM }}</td>
                                <td>{{ ucfirst($item->Urgency) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Notes Section -->
                @if($requisitionInfo->Remarks)
                    <div class="mb-4">
                        <h5>Additional Notes</h5>
                        <div class="alert alert-info">
                            {{ $requisitionInfo->Remarks }}
                        </div>
                    </div>
                @endif

                <!-- Approval Actions -->
                @if($requisitionInfo->Status == 'pending' || $requisitionInfo->Status == 'Pending')
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <form action="{{ route('requisition.approve', $requisitionInfo->Id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger btn-lg ml-2" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject
                            </button>

                        </div>
                    </div>
                @else
                    <div class="alert alert-{{ $requisitionInfo->Status == 'approved' || $requisitionInfo->Status == 'Approved' ? 'success' : 'danger' }}">
                        This requisition has already been {{ $requisitionInfo->Status }}.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Requisition</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('requisition.approve', $requisitionInfo->Id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejection_reason">Reason for Rejection</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-danger">Submit Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .table th {
            white-space: nowrap;
        }
        .badge {
            font-size: 0.9em;
        }
    </style>
@endsection
