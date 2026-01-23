@extends('layouts.app')
@section('title', 'Approve Purchase Order')
@section('content')

<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Approve Purchase Order</h5>
        <a href="{{ route('purchaseOrder.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            @if(isset($orderInfo))
                @php
                    $docType = 'purchase_order';
                    $poId = (int)($orderInfo->Id ?? 0);
                    $orderTotal = (float)($orderInfo->OrdTotIncl ?? 0);
                    $isRejected = false;
                    $isFullyApproved = false;
                    try {
                        $isRejected = \Illuminate\Support\Facades\DB::table('t_Approvals')
                            ->where('DocType', $docType)
                            ->where('DocumentId', $poId)
                            ->where('Status', 'rejected')
                            ->exists();
                        if (!$isRejected && $poId > 0) {
                            $isFullyApproved = app(\App\Services\Core\ApprovalService::class)
                                ->isFullyApproved($docType, $poId, $orderTotal);
                        }
                    } catch (\Throwable $e) {
                        $isRejected = false; $isFullyApproved = false;
                    }
                @endphp
                <div class="row mb-3">
                    <div class="col-md-4"><strong>LPO No:</strong> {{ $orderInfo->ExtOrdNum ?? '--' }}</div>
                    <div class="col-md-4"><strong>Order No:</strong> {{ $orderInfo->OrderNo ?? '--' }}</div>
                    <div class="col-md-4"><strong>Date:</strong> {{ isset($orderInfo->OrderDate) ? \Carbon\Carbon::parse($orderInfo->OrderDate)->format('d/m/Y') : '--' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Supplier:</strong> {{ $orderInfo->SupplierName ?? $orderInfo->TradingName ?? ('Supplier #' . ($orderInfo->SupplierId ?? '')) }}</div>
                    <div class="col-md-6"><strong>Payment Terms:</strong> {{ $paymentTerms ?? $orderInfo->TermsDescription ?? '--' }}</div>
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
                        @foreach(($lineInfo ?? []) as $i => $line)
                            @php
                                // Raw fields from OrderService.fetchOrderLineDetails
                                $qtyRaw = $line->Quantity ?? null;
                                $discRaw = $line->Discount ?? null; 
                                $unitExcl = $line->UnitPrice ?? null; 
                                $taxRate = $line->Tax ?? null; 
                                $lineTotal = $line->LineTotal ?? null; 

                                // Derive display quantity
                                $qty = is_numeric($qtyRaw) ? (float)$qtyRaw : 0.0;

                                // Derive unit price (exclusive). If missing, attempt back calculation from line total minus discount & tax when possible
                                $unitPrice = is_numeric($unitExcl) ? (float)$unitExcl : null;

                                // Determine discount percent: if fLineDiscount present and qty & unitPrice known
                                $discountPercent = 0.0;
                                if (is_numeric($discRaw) && $qty > 0 && $unitPrice !== null && $unitPrice > 0) {
                                    // Assume discount raw is total discount amount across line
                                    $discountPercent = ((float)$discRaw) / ($qty * $unitPrice) * 100.0;
                                }

                                // If unit price unknown, attempt to compute from (lineTotal + discount) / qty when tax known (approx exclusive)
                                if ($unitPrice === null && $qty > 0 && is_numeric($lineTotal)) {
                                    $grossBeforeDiscount = (float)$lineTotal + (is_numeric($discRaw) ? (float)$discRaw : 0.0);
                                    if (is_numeric($taxRate) && $taxRate > 0) {
                                        $exclusiveTotal = $grossBeforeDiscount / (1 + ($taxRate/100));
                                        $unitPrice = $exclusiveTotal / $qty;
                                    } else {
                                        $unitPrice = $grossBeforeDiscount / $qty;
                                    }
                                }

                                // Normalize values for display
                                $displayQty = number_format($qty, 2);
                                $displayUnit = number_format($unitPrice ?? 0, 2);
                                $displayTax = number_format(is_numeric($taxRate) ? (float)$taxRate : 0.0, 2);
                                $displayDisc = number_format($discountPercent, 2);
                                $displayLineTotal = number_format(is_numeric($lineTotal) ? (float)$lineTotal : 0.0, 2);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $line->ItemName ?? ('#'.$line->ItemID) }}</td>
                                <td>{{ $line->Description ?? '' }}</td>
                                <td class="text-end" title="Raw: {{ $qtyRaw }}">{{ $displayQty }}</td>
                                <td class="text-end" title="Raw excl: {{ $unitExcl }}">{{ $displayUnit }}</td>
                                <td class="text-end" title="Stored Tax Rate: {{ $taxRate }}%">{{ $displayTax }}</td>
                                <td class="text-end" title="Computed from discount amount {{ $discRaw }}">{{ $displayDisc }}</td>
                                <td class="text-end" title="Raw total: {{ $lineTotal }}">{{ $displayLineTotal }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($isFullyApproved)
        <div class="d-flex justify-content-end gap-2">
            <span class="btn btn-success disabled" aria-disabled="true" title="This PO is fully approved">
                <i class="fas fa-check"></i> Approved
            </span>
            <span class="btn btn-outline-secondary disabled" aria-disabled="true" title="This PO is fully approved">Reject</span>
        </div>
    @elseif($isRejected)
        <div class="d-flex justify-content-end gap-2">
            <span class="btn btn-outline-secondary disabled" aria-disabled="true" title="This PO was rejected">Approve</span>
            <span class="btn btn-danger disabled" aria-disabled="true"><i class="fas fa-times"></i> Rejected</span>
        </div>
    @else
        <div class="d-flex justify-content-end gap-2">
            <form action="{{ route('purchaseOrder.approve', $orderInfo->Id ?? 0) }}" method="POST">
                @csrf
                <input type="hidden" name="document_type" value="purchase_order">
                <input type="hidden" name="order_total" value="{{ $orderInfo->OrdTotIncl ?? 0 }}">
                <input type="hidden" name="order_id" value="{{ $orderInfo->Id ?? 0 }}">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
            </form>

            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="fas fa-times"></i> Reject
            </button>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Purchase Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('purchaseOrder.approve', $orderInfo->Id ?? 0) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="document_type" value="purchase_order">
                            <input type="hidden" name="order_total" value="{{ $orderInfo->OrdTotIncl ?? 0 }}">
                            <input type="hidden" name="order_id" value="{{ $orderInfo->Id ?? 0 }}">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="rollback_to_previous" value="1">
                            <div class="mb-3">
                                <label for="rejection_reason" class="form-label">Reason for rejection</label>
                                <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Provide a brief reason" required></textarea>
                            </div>
                            <div class="alert alert-warning">
                                This will send the P.O back to the previous workflow step.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Confirm Reject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
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
