@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('title', 'Requisition Approval')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3>Requisition Approval</h3>
            </div>

            <div class="card-body">
                <!-- Requisition Summary Section -->
                <div class="row mb-4"></div>
                    <div class="col-md-6">
                        <h5>Requisition Information</h5>
                        <p><strong>Requisition Number:</strong> {{ $requisitionInfo->RequisitionNo ?? 'N/A' }}</p>
                        <p>
                            <strong>Date:</strong> {{ isset($requisitionInfo->CreatedOn) ? Carbon::parse($requisitionInfo->CreatedOn)->format('d/m/Y') : '' }}
                        </p>
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
                            <th>Need ID</th>
                            <th>UOM</th>
                            <th>Quantity</th>
                            <th>Pricing</th>
                            <th>Urgency</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($requisitionlineInfo as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->Type ?? 'N/A' }}</td>
                                <td>{{ $item->ItemName ?? 'N/A' }}</td>
                                <td>{{ $item->NeedRef ?? 'N/A' }}</td>
                                <td>{{ $item->UOM }}</td>
                                <td>{{ $item->Quantity }}</td>
                                <td>{{ $item->ExpectedPrice }}</td>
                                <td>{{ ucfirst($item->Urgency) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="5">
                                <b> Total Amount</b>
                            </td>
                            <td colspan="2" style="text-align: right;">
                                <b>{{ $requisitionInfo->ExpectedPrice ?? 'N/A' }}</b>
                            </td>
                        </tr>
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
                    @php
                        $validTypes = ['ALL','ANY','MAJ','AMT'];
                        $approvalTypeValid = isset($approvalType) && in_array(strtoupper($approvalType), $validTypes);
                    @endphp
                    @unless($approvalTypeValid)
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Approval type is not configured for Purchase Requisitions. Please contact the administrator.
                        </div>
                    @endunless
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <form action="{{ route('requisition.approve', $requisitionInfo->Id) }}" method="POST"
                                  class="d-inline">
                                @csrf
                                {{--                                <input type="text" name="action" value="approve">--}}
                                {{--                                @csrf--}}

                                <input type="hidden" name="document_type" value="purchase_requisition">
                                <input type="hidden" name="order_total"
                                       value="{{ $requisitionInfo->ExpectedPrice ?? 'N/A' }}">
                                <input type="hidden" name="order_id" value="{{$requisitionInfo->Id}}">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-lg" {{ $approvalTypeValid ? '' : 'disabled' }}>
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger btn-lg ms-2" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject
                            </button>

                            <button type="button" class="btn btn-warning btn-lg ml-2" data-bs-toggle="modal"
                                    data-bs-target="#statusModal">
                                <i class="fas fa-info-circle"></i> Approval Status
                            </button>

                        </div>
                    </div>
                @else
                    <div
                        class="alert alert-{{ $requisitionInfo->Status == 'approved' || $requisitionInfo->Status == 'Approved' ? 'success' : 'danger' }}">
                        This requisition has already been {{ $requisitionInfo->Status }}.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approval Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
         aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-scrollable modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="statusModalLabel">Approval Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if(count($approvalStatus))
                        <ul class="list-group">
                            @foreach($approvalStatus as $user)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $user['name'] }}
                                    @if($user['approved'])
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No approval configuration found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel"
         aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Requisition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('requisition.approve', $requisitionInfo->Id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejection_reason">Reason for Rejection</label>
                            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3"
                                      required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <input type="hidden" name="document_type" value="purchase_requisition">
                        <input type="hidden" name="order_total" value="{{ $requisitionInfo->ExpectedPrice ?? '0' }}">
                        <input type="hidden" name="order_id" value="{{ $requisitionInfo->Id }}">
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

        .modal .list-group-item {
            font-size: 0.95rem;
        }
    </style>
@endsection
