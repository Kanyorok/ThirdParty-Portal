@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-dashboard"></i> GRN Processing Dashboard
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('goods-receipt.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Create GRN
                            </a>
                            <a href="{{ route('goods-receipt.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-list"></i> All GRNs
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-2">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h3 class="mb-0">{{ $stats['pending_grns'] }}</h3>
                                                <p class="mb-0 small">Pending GRNs</p>
                                            </div>
                                            <div>
                                                <i class="fas fa-clock fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h3 class="mb-0">{{ $stats['pending_quality'] }}</h3>
                                                <p class="mb-0 small">Pending Quality</p>
                                            </div>
                                            <div>
                                                <i class="fas fa-search fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h3 class="mb-0">{{ $stats['pending_processing'] }}</h3>
                                                <p class="mb-0 small">Pending Processing</p>
                                            </div>
                                            <div>
                                                <i class="fas fa-hourglass-half fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h3 class="mb-0">{{ $stats['processed_today'] }}</h3>
                                                <p class="mb-0 small">Processed Today</p>
                                            </div>
                                            <div>
                                                <i class="fas fa-check fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card bg-dark text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h3 class="mb-0">
                                                    KES {{ number_format($stats['total_value_pending'], 2) }}</h3>
                                                <p class="mb-0 small">Total Value Pending</p>
                                            </div>
                                            <div>
                                                <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Recent GRNs -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-history"></i> Recent GRNs</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                <tr>
                                                    <th>GRN ID</th>
                                                    <th>Item</th>
                                                    <th>Type</th>
                                                    <th>Supplier</th>
                                                    <th>Value</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @forelse($recentGRNs as $grn)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $grn->GRNID }}</strong>
                                                            <br><small class="text-muted">{{ $grn->POID }}</small>
                                                        </td>
                                                        <td>
                                                            <strong>{{ $grn->item->ItemName ?? 'Unknown' }}</strong>
                                                            <br><small
                                                                class="text-muted">Qty: {{ $grn->ReceivedQTY }}</small>
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge bg-{{ $grn->ItemType == 'stock' ? 'success' : ($grn->ItemType == 'asset' ? 'warning' : 'info') }}">
                                                                {{ $grn->item_type_display }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $grn->supplier->thirdParty->TradingName ?? 'N/A' }}</td>
                                                        <td>
                                                            <strong>KES {{ number_format($grn->TotalValue ?? 0, 2) }}</strong>
                                                        </td>
                                                        <td>
                                                            @php $badge = $grn->processing_status_badge ?? ['text' => 'Unknown', 'class' => 'secondary'] @endphp
                                                            <span
                                                                class="badge bg-{{ $badge['class'] }}">{{ $badge['text'] }}</span>
                                                        </td>
                                                        <td>{{ $grn->CreatedOn ? $grn->CreatedOn->format('d M Y') : 'N/A' }}</td>
                                                        <td>
                                                            <a href="{{ route('goods-receipt.show', ['grnId' => $grn->GRNID, 'poId' => $grn->POID]) }}"
                                                               class="btn btn-outline-primary btn-sm"
                                                               title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted py-3">
                                                            No recent GRNs found
                                                        </td>
                                                    </tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Processing Errors -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Processing Errors
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @forelse($processingErrors as $error)
                                            <div class="alert alert-danger alert-dismissible fade show mb-2"
                                                 role="alert">
                                                <h6 class="alert-heading mb-1">{{ $error->GRNID }}</h6>
                                                <p class="mb-1">
                                                    <strong>Item:</strong> {{ $error->item->ItemName ?? 'Unknown' }}</p>
                                                <p class="mb-1 small">{{ $error->ProcessingErrors }}</p>
                                                <hr class="my-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small
                                                        class="text-muted">{{ $error->ModifiedOn ? $error->ModifiedOn->format('d M Y H:i') : 'N/A' }}</small>
                                                    <div>
                                                        <a href="{{ route('goods-receipt.show', ['grnId' => $error->GRNID, 'poId' => $error->POID]) }}"
                                                           class="btn btn-outline-light btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-light btn-sm"
                                                                onclick="retryProcessing({{ $error->id }})">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close"
                                                        data-bs-dismiss="alert"></button>
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-3">
                                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                                <p class="mb-0">No processing errors!</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="d-grid">
                                                    <a href="{{ route('goods-receipt.index') }}?processing_status=pending"
                                                       class="btn btn-outline-warning">
                                                        <i class="fas fa-clock"></i><br>
                                                        <small>Pending Processing</small>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-grid">
                                                    <a href="{{ route('goods-receipt.index') }}?item_type=stock"
                                                       class="btn btn-outline-success">
                                                        <i class="fas fa-boxes"></i><br>
                                                        <small>Stock Items</small>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-grid">
                                                    <a href="{{ route('goods-receipt.index') }}?item_type=asset"
                                                       class="btn btn-outline-warning">
                                                        <i class="fas fa-desktop"></i><br>
                                                        <small>Asset Items</small>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-grid">
                                                    <a href="{{ route('goods-receipt.index') }}?item_type=service"
                                                       class="btn btn-outline-info">
                                                        <i class="fas fa-hand-holding-usd"></i><br>
                                                        <small>Service Items</small>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <div class="d-grid">
                                                    <button type="button" class="btn btn-primary"
                                                            onclick="processAllPending()">
                                                        <i class="fas fa-play"></i> Process All Pending
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-grid">
                                                    <button type="button" class="btn btn-success"
                                                            onclick="bulkQualityPass()">
                                                        <i class="fas fa-check-double"></i> Bulk Quality Pass
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="d-grid">
                                                    <a href="{{ route('goods-receipt.reports.processing-status') }}"
                                                       class="btn btn-outline-dark">
                                                        <i class="fas fa-chart-bar"></i> Processing Report
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Item Type Analysis -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Item Type Distribution (Last
                                            30 Days)</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="itemTypeChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> Processing Trend (Last 7
                                            Days)</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="processingTrendChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Item Type Distribution Chart
        const itemTypeCtx = document.getElementById('itemTypeChart').getContext('2d');
        new Chart(itemTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Stock Items', 'Asset Items', 'Service Items'],
                datasets: [{
                    data: [60, 25, 15], // Sample data - should come from controller
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Processing Trend Chart
        const trendCtx = document.getElementById('processingTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Processed GRNs',
                    data: [12, 19, 15, 5, 22, 8, 3], // Sample data
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function processAllPending() {
            if (!confirm('Process all pending GRNs? This may take some time for large batches.')) {
                return;
            }

            // Show loading state
            const btn = event.target;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            fetch('{{ url('/procurement/goods-receipt/api/process-all-pending') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing GRNs.');
                })
                .finally(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                });
        }

        function bulkQualityPass() {
            if (!confirm('Mark all pending quality checks as passed? Only use this for trusted suppliers.')) {
                return;
            }

            fetch('{{ url('/procurement/goods-receipt/api/bulk-quality-pass') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating quality statuses.');
                });
        }

        function retryProcessing(lineId) {
            if (!confirm('Retry processing for this line item?')) {
                return;
            }

            fetch(`{{ url('/procurement/goods-receipt/api/retry-processing') }}/${lineId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Processing retry completed successfully.');
                        location.reload();
                    } else {
                        alert('Failed to retry processing: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while retrying processing.');
                });
        }

        // Auto-refresh dashboard every 2 minutes
        setInterval(function () {
            location.reload();
        }, 120000);
    </script>
@endsection
