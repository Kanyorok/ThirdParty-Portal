@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-truck-loading"></i> GRN Details: {{ $grnSummary['grn_id'] }}
                        </h5>
                        <div class="d-flex gap-2">
                            @if($grnSummary['can_post'])
                                <button type="button" class="btn btn-success btn-sm" onclick="processGRN()">
                                    <i class="fas fa-play"></i> Process GRN
                                </button>
                            @endif
                            <a href="{{ route('goods-receipt.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to GRNs
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- GRN Summary -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <h6>GRN Information</h6>
                                                <p class="mb-1"><strong>GRN ID:</strong> {{ $grnSummary['grn_id'] }}</p>
                                                <p class="mb-1"><strong>PO
                                                        Number:</strong> {{ $grnSummary['order_no'] }}</p>
                                                <p class="mb-1"><strong>Received
                                                        Date:</strong> {{ $grnSummary['received_date']->format('d M Y') }}
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <h6>Supplier Information</h6>
                                                <p class="mb-1">
                                                    <strong>Name:</strong> {{ $grnSummary['supplier_name'] }}</p>
                                            </div>
                                            <div class="col-md-3">
                                                <h6>Summary</h6>
                                                <p class="mb-1"><strong>Total
                                                        Lines:</strong> {{ $grnSummary['total_lines'] }}</p>
                                                <p class="mb-1"><strong>Total Value:</strong>
                                                    KES {{ number_format($grnSummary['total_value'], 2) }}</p>
                                            </div>
                                            <div class="col-md-3">
                                                <h6>Status</h6>
                                                <p class="mb-1">
                                                    <strong>Processing:</strong>
                                                    <span
                                                        class="badge bg-{{ $grnSummary['processing_status'] == 'All Processed' ? 'success' : ($grnSummary['processing_status'] == 'Has Errors' ? 'danger' : 'warning') }}">
                                                    {{ $grnSummary['processing_status'] }}
                                                </span>
                                                </p>
                                                @if($grnSummary['can_post'])
                                                    <p class="mb-1"><span
                                                            class="badge bg-success">Ready for Processing</span></p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- GRN Line Items -->
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-dark">
                                <tr>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Ordered Qty</th>
                                    <th>Received Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total Value</th>
                                    <th>Quality Status</th>
                                    <th>Processing Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($grnLines as $line)
                                    <tr>
                                        <td>
                                            <strong>{{ $line->item->ItemName ?? 'Unknown Item' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $line->item->ItemDescription ?? '' }}</small>
                                            @if($line->BatchNumber)
                                                <br><small><strong>Batch:</strong> {{ $line->BatchNumber }}</small>
                                            @endif
                                            @if($line->ExpiryDate)
                                                <br>
                                                <small><strong>Expires:</strong> {{ $line->ExpiryDate->format('d M Y') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $line->ItemType == 'stock' ? 'success' : ($line->ItemType == 'asset' ? 'warning' : 'info') }}">
                                                {{ $line->item_type_display }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($line->POQTY, 2) }} {{ $line->item->uom->Name ?? '' }}</td>
                                        <td>
                                            <strong>{{ number_format($line->ReceivedQTY, 2) }}</strong> {{ $line->item->uom->Name ?? '' }}
                                        </td>
                                        <td>KES {{ number_format($line->UnitPrice, 2) }}</td>
                                        <td>
                                            @php
                                                $displayValue = $line->TotalValue;
                                                if (($displayValue == 0 || $displayValue == 0.00) && $line->ReceivedQTY > 0) {
                                                    $displayValue = $line->ReceivedQTY * $line->UnitPrice;
                                                }
                                            @endphp
                                            <strong>KES {{ number_format($displayValue, 2) }}</strong>
                                        </td>
                                        <td>
                                            @php $qualityBadge = $line->quality_status_badge @endphp
                                            <span class="badge bg-{{ $qualityBadge['class'] }}">
                                                {{ $qualityBadge['text'] }}
                                            </span>
                                            @if($line->QualityRemarks)
                                                <br><small
                                                    class="text-muted">{{ Str::limit($line->QualityRemarks, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @php $processingBadge = $line->processing_status_badge @endphp
                                            <span class="badge bg-{{ $processingBadge['class'] }}">
                                                {{ $processingBadge['text'] }}
                                            </span>
                                            @if($line->ProcessingErrors)
                                                <br><small
                                                    class="text-danger">{{ Str::limit($line->ProcessingErrors, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if($line->QualityStatus === 'pending')
                                                    <button type="button" class="btn btn-outline-success"
                                                            onclick="updateQualityStatus({{ $line->id }}, 'passed')"
                                                            title="Mark Quality Passed">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger"
                                                            onclick="updateQualityStatus({{ $line->id }}, 'failed')"
                                                            title="Mark Quality Failed">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif

                                                <button type="button" class="btn btn-outline-info"
                                                        onclick="showLineDetails({{ $line->id }})"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @if($line->hasError())
                                                    <button type="button" class="btn btn-outline-warning"
                                                            onclick="retryProcessing({{ $line->id }})"
                                                            title="Retry Processing">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Processing Log -->
                        @if($grnLines->some->isProcessed())
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-history"></i> Processing Log</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Type</th>
                                                <th>Action Taken</th>
                                                <th>Stock Updated</th>
                                                <th>Journal Entry</th>
                                                <th>Processed At</th>
                                                <th>Processed By</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($grnLines->where('ProcessingStatus', 'processed') as $line)
                                                <tr>
                                                    <td>{{ $line->item->ItemName ?? 'Unknown' }}</td>
                                                    <td>{{ $line->item_type_display }}</td>
                                                    <td>
                                                        @if($line->isStock())
                                                            <i class="fas fa-boxes text-success"></i> Added to Stock
                                                        @elseif($line->isAsset())
                                                            <i class="fas fa-desktop text-warning"></i> Asset Created
                                                        @else
                                                            <i class="fas fa-hand-holding-usd text-info"></i> Expensed
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($line->UpdatedStock)
                                                            <span class="badge bg-success">Yes</span>
                                                            @if($line->StockTransactionRef)
                                                                <br><small>Ref: {{ $line->StockTransactionRef }}</small>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-secondary">No</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($line->CreatedJournalEntry)
                                                            <span class="badge bg-success">Created</span>
                                                            @if($line->JournalEntryRef)
                                                                <br><small>{{ $line->JournalEntryRef }}</small>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-danger">Failed</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($line->PostedAt)
                                                            {{ $line->PostedAt->format('d M Y H:i') }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $line->poster->name ?? 'System' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Check Modal -->
    <div class="modal fade" id="qualityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quality Check</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="qualityForm">
                    <div class="modal-body">
                        <input type="hidden" id="quality_line_id">
                        <input type="hidden" id="quality_status">

                        <div class="form-group mb-3">
                            <label class="form-label">Quality Remarks</label>
                            <textarea class="form-control" id="quality_remarks" rows="3"
                                      placeholder="Add remarks for quality check..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Quality Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Line Details Modal -->
    <div class="modal fade" id="lineDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Line Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="lineDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function processGRN() {
            if (!confirm('Are you sure you want to process this GRN? This will create stock transactions and journal entries as appropriate.')) {
                return;
            }

            const grnId = '{{ $grnSummary["grn_id"] }}';
            const poId = '{{ $grnSummary["po_id"] }}';

            fetch(`{{ url('/procurement/goods-receipt/process') }}/${grnId}/${poId}`, {
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
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing the GRN.');
                });
        }

        function updateQualityStatus(lineId, status) {
            document.getElementById('quality_line_id').value = lineId;
            document.getElementById('quality_status').value = status;
            document.getElementById('quality_remarks').value = '';

            // Set modal title based on status
            const modal = new bootstrap.Modal(document.getElementById('qualityModal'));
            document.querySelector('#qualityModal .modal-title').textContent =
                status === 'passed' ? 'Mark Quality as Passed' : 'Mark Quality as Failed';

            modal.show();
        }

        document.getElementById('qualityForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const lineId = document.getElementById('quality_line_id').value;
            const status = document.getElementById('quality_status').value;
            const remarks = document.getElementById('quality_remarks').value;

            fetch(`{{ url('/procurement/goods-receipt/api/quality-check') }}/${lineId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    quality_status: status,
                    quality_remarks: remarks
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Quality status updated successfully.');
                        location.reload();
                    } else {
                        alert('Failed to update quality status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating quality status.');
                })
                .finally(() => {
                    bootstrap.Modal.getInstance(document.getElementById('qualityModal')).hide();
                });
        });

        function showLineDetails(lineId) {
            // Load line details via AJAX
            fetch(`{{ url('/procurement/goods-receipt/api/line-details') }}/${lineId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('lineDetailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('lineDetailsModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load line details.');
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
                        alert('Processing retry completed: ' + data.message);
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
    </script>
@endsection
