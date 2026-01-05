@extends('layouts.app')
@section('title', 'View Purchase Order')
@section('content')

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Purchase Order</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-dark" onclick="window.print()"><i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('purchaseOrder.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i>
                Back</a>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @if(isset($orderInfo))
            <div class="row mb-3">
                <div class="col-md-4"><strong>LPO No:</strong> {{ $orderInfo->ExtOrdNum ?? '--' }}</div>
                <div class="col-md-4"><strong>Order No:</strong> {{ $orderInfo->OrderNo ?? '--' }}</div>
                <div class="col-md-4">
                    <strong>Date:</strong> {{ isset($orderInfo->OrderDate) ? \Carbon\Carbon::parse($orderInfo->OrderDate)->format('d/m/Y') : '--' }}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Supplier:</strong> {{ $orderInfo->SupplierName ?? $orderInfo->TradingName ?? ('Supplier #' . ($orderInfo->SupplierId ?? '')) }}
                </div>
                <div class="col-md-6"><strong>Address:</strong> {{ $orderInfo->SupplierAddress ?? '' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4"><strong>Priority:</strong> {{ $orderInfo->Priority ?? '--' }}</div>
                <div class="col-md-4"><strong>Payment Terms:</strong> {{ $orderInfo->TermsDescription ?? '--' }}
                </div>
                <div class="col-md-4">
                    <strong>Branch:</strong> {{ $orderInfo->BranchName ?? $orderInfo->BranchID ?? '--' }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Tax %</th>
                            <th class="text-end">Discount %</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lineInfo ?? [] as $i => $line)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $line->ItemName ?? ('#'.$line->ItemId) }}</td>
                            <td>{{ $line->ItemDescription ?? '' }}</td>
                            <td class="text-end">{{ number_format((float)($line->Quantity ?? 0), 2) }}</td>
                            <td class="text-end">{{ number_format((float)($line->UnitPrice ?? 0), 2) }}</td>
                            <td class="text-end">{{ number_format((float)($line->Tax ?? 0), 2) }}</td>
                            <td class="text-end">{{ number_format((float)($line->Discount ?? 0), 2) }}</td>
                            <td class="text-end">{{ number_format((float)($line->LineTotal ?? 0), 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No line items</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-4 offset-md-8">
                    <div class="d-flex justify-content-between">
                        <span>Exclusive Total</span><strong>{{ number_format((float)($orderInfo->ExclusiveTotal ?? 0), 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tax Amount</span><strong>{{ number_format((float)($orderInfo->TaxAmount ?? 0), 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Inclusive Total</span><strong>{{ number_format((float)($orderInfo->InclusiveTotal ?? 0), 2) }}</strong>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-warning">Order details not available.</div>
            @endif
        </div>
    </div>

    <!-- Workflow History Section -->
    @if(isset($history) && $history->count() > 0)
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-history"></i> Approval Workflow History</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Stage</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $record)
                    <tr>
                        <td>{{ $record->CreatedOn ? \Carbon\Carbon::parse($record->CreatedOn)->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $record->stage->StageName ?? 'N/A' }}</td>
                        <td>{{ $record->creator->Name ?? 'System' }}</td>
                        <td>
                            @php
                                $statusValue = $record->StatusId ?? '';
                                $badgeClass = match($statusValue) {
                                    'A' => 'bg-success',
                                    'R' => 'bg-danger',
                                    'S' => 'bg-warning',
                                    'P' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ $record->status->Description ?? $statusValue }}
                            </span>
                        </td>
                        <td>{{ $record->Notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Current Workflow Status -->
    @if(isset($workflowStatus) && isset($workflowStatus['pendingApprovals']))
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-clock"></i> Pending Approvals</h6>
        </div>
        <div class="card-body">
            @if(count($workflowStatus['pendingApprovals']) > 0)
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Stage</th>
                        <th>Approver</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workflowStatus['pendingApprovals'] as $pending)
                    <tr>
                        <td>{{ $pending['stage'] ?? 'N/A' }}</td>
                        <td>{{ $pending['approver'] ?? 'N/A' }}</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="mb-0 text-success"><i class="fas fa-check-circle"></i> All approvals completed</p>
            @endif
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mt-4 no-print">
        @if(isset($canApprove) && $canApprove)
        <form action="{{ route('purchaseOrder.approve', $orderInfo->Id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this order?')">
                <i class="fas fa-check"></i> Approve
            </button>
        </form>
        <form action="{{ route('purchaseOrder.reject', $orderInfo->Id) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this order?')">
                <i class="fas fa-times"></i> Reject
            </button>
        </form>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
    @media print {

        nav,
        .btn,
        .breadcrumb,
        .navbar,
        .footer {
            display: none !important;
        }

        .card {
            border: none;
        }
    }
</style>
@endpush