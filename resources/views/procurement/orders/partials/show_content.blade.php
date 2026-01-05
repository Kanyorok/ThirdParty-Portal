<!-- Order Details -->
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
    <div class="col-md-6"><strong>Priority:</strong> {{ $orderInfo->Priority ?? '--' }}</div>
</div>

<!-- Line Items Table -->
<div class="table-responsive mb-3">
    <table class="table table-sm table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Description</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Unit Price</th>
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
                <td class="text-end">{{ number_format((float)($line->LineTotal ?? 0), 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No line items</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Totals -->
<div class="row mb-3">
    <div class="col-md-4 offset-md-8">
        <div class="d-flex justify-content-between">
            <span>Exclusive Total</span><strong>{{ number_format((float)($orderInfo->ExclusiveTotal ?? 0), 2) }}</strong>
        </div>
        <div class="d-flex justify-content-between">
            <span>Tax Amount</span><strong>{{ number_format((float)($orderInfo->TaxAmount ?? 0), 2) }}</strong>
        </div>
        <div class="d-flex justify-content-between border-top pt-2">
            <span><strong>Inclusive Total</strong></span><strong>{{ number_format((float)($orderInfo->InclusiveTotal ?? 0), 2) }}</strong>
        </div>
    </div>
</div>

<!-- Workflow History Section -->
@if(isset($history) && $history->count() > 0)
<div class="card mb-3">
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
@else
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No workflow history available yet.
</div>
@endif

<!-- Current Workflow Status / Pending Approvals -->
@if(isset($workflowStatus) && isset($workflowStatus['pendingApprovals']))
<div class="card mb-3">
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
<div class="d-flex justify-content-end gap-2 mt-3">
    @if(isset($canApprove) && $canApprove)
    <a href="{{ route('purchaseOrder.show', $orderInfo->Id) }}" class="btn btn-success btn-sm" target="_blank">
        <i class="fas fa-check"></i> Approve
    </a>
    @endif
    <a href="{{ route('purchaseOrder.show', $orderInfo->Id) }}" class="btn btn-primary btn-sm" target="_blank">
        <i class="fas fa-external-link-alt"></i> Open Full View
    </a>
</div>
