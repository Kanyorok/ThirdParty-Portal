@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-truck-loading"></i> Goods Receipt Notes
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('goods-receipt.dashboard') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-dashboard"></i> Dashboard
                        </a>
                        <a href="{{ route('goods-receipt.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create GRN
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @isset($serviceGlConfigured)
                        @if(!$serviceGlConfigured)
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="ti ti-alert-circle me-2"></i>
                                <div>
                                    Finance mapping for service receipts is not configured (missing transaction code 'GRN-SERVICE').
                                    Please add it in Finance Transaction Types and link GL mapping to enable posting.
                                </div>
                            </div>
                        @endif
                    @endisset
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body py-3">
                                    <form method="GET" action="{{ route('goods-receipt.index') }}">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-2">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="">All Status</option>
                                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Processing</label>
                                                <select name="processing_status" class="form-select form-select-sm">
                                                    <option value="">All Processing</option>
                                                    <option value="pending" {{ request('processing_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="processed" {{ request('processing_status') == 'processed' ? 'selected' : '' }}>Processed</option>
                                                    <option value="error" {{ request('processing_status') == 'error' ? 'selected' : '' }}>Error</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Item Type</label>
                                                <select name="item_type" class="form-select form-select-sm">
                                                    <option value="">All Types</option>
                                                    <option value="stock" {{ request('item_type') == 'stock' ? 'selected' : '' }}>Stock</option>
                                                    <option value="asset" {{ request('item_type') == 'asset' ? 'selected' : '' }}>Asset</option>
                                                    <option value="service" {{ request('item_type') == 'service' ? 'selected' : '' }}>Service</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Date From</label>
                                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Date To</label>
                                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-search"></i> Filter
                                                </button>
                                                <a href="{{ route('goods-receipt.index') }}" class="btn btn-outline-secondary btn-sm">
                                                    Clear
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GRN List -->
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>GRN ID</th>
                                    <th>PO Number</th>
                                    <th>Supplier</th>
                                    <th>Received Date</th>
                                    <th>Lines</th>
                                    <th>Total Value</th>
                                    <th>Status</th>
                                    <th>Processing</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($goodsReceipts as $receipt)
                                    @php
                                        $grnLines = \App\Models\Procurement\EnhancedGoodsReceipt::byGRN($receipt->GRNID)
                                                                                                   ->byPO($receipt->POID)
                                                                                                   ->get();
                                        $totalValue = $grnLines->sum('TotalValue');
                                        $lineCount = $grnLines->count();
                                        $processingStatus = $grnLines->every->isProcessed() ? 'processed' : 
                                                          ($grnLines->some->isProcessed() ? 'partial' : 
                                                          ($grnLines->some->hasError() ? 'error' : 'pending'));
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $receipt->GRNID }}</strong>
                                        </td>
                                        <td>
                                            {{ $receipt->order->OrderNo ?? 'N/A' }}
                                            <br>
                                            <small class="text-muted">{{ $receipt->POID }}</small>
                                        </td>
                                        <td>
                                            {{ $receipt->supplier->thirdParty->TradingName ?? $receipt->supplier->thirdParty->ThirdPartyName ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $receipt->ReceivedDate ? $receipt->ReceivedDate->format('d M Y') : 'N/A' }}
                                            <br>
                                            <small class="text-muted">by {{ $receipt->receiver->name ?? 'System' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $lineCount }} lines</span>
                                        </td>
                                        <td>
                                            <strong>KES {{ number_format($totalValue, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($receipt->InspectionStatus->value === 'draft')
                                                <span class="badge bg-warning">Draft</span>
                                            @else
                                                <span class="badge bg-success">Posted</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($processingStatus)
                                                @case('processed')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Processed
                                                    </span>
                                                    @break
                                                @case('partial')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Partial
                                                    </span>
                                                    @break
                                                @case('error')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Error
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-hourglass"></i> Pending
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('goods-receipt.show', ['grnId' => $receipt->GRNID, 'poId' => $receipt->POID]) }}" 
                                                   class="btn btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($receipt->InspectionStatus->value === 'draft')
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="processGRN('{{ $receipt->GRNID }}', '{{ $receipt->POID }}')" 
                                                            title="Process GRN">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-outline-info" 
                                                        onclick="showGRNSummary('{{ $receipt->GRNID }}', '{{ $receipt->POID }}')" 
                                                        title="Quick Summary">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <br>
                                            No Goods Receipt Notes found.
                                            <br>
                                            <a href="{{ route('goods-receipt.create') }}" class="btn btn-primary btn-sm mt-2">
                                                Create First GRN
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($goodsReceipts->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $goodsReceipts->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRN Summary Modal -->
<div class="modal fade" id="grnSummaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">GRN Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="grnSummaryContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function processGRN(grnId, poId) {
    if (!confirm('Are you sure you want to process this GRN? This action cannot be undone.')) {
        return;
    }

    // Show loading state
    const button = event.target.closest('button');
    const originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    fetch(`{{ url('procurement/goods-receipt') }}/process/${grnId}/${poId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('GRN processed successfully: ' + data.message);
            location.reload();
        } else {
            alert('Failed to process GRN: ' + data.message);
            button.innerHTML = originalHtml;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the GRN.');
        button.innerHTML = originalHtml;
        button.disabled = false;
    });
}

function showGRNSummary(grnId, poId) {
    // Load GRN summary via AJAX
    fetch(`{{ url('procurement/goods-receipt') }}/summary/${grnId}/${poId}`)
    .then(response => response.text())
    .then(html => {
        document.getElementById('grnSummaryContent').innerHTML = html;
        new bootstrap.Modal(document.getElementById('grnSummaryModal')).show();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load GRN summary.');
    });
}
</script>
@endsection
