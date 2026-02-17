@extends('layouts.app')
@section('title', 'Voucher Details')

@section('content')
    <div class="container mt-3">
        <div class="card shadow-sm p-4 rounded-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-muted">
                    <i class="fas fa-file-invoice-dollar text-info"></i> Voucher Details
                </h5>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>

            {{-- Voucher Info --}}
            <div class="row border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <strong>Voucher No:</strong>
                    <span class="text-primary">{{ $voucher->VoucherNo }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Invoice Ref No:</strong>
                    <span class="text-muted">{{ $voucher->invoice->InvoiceNumber ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Payment Method:</strong>
                    <span>{{ $voucher->PaymentMethod ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Payment Type:</strong>
                    <span class="badge bg-info text-dark">{{ ucfirst($voucher->PaymentType ?? 'N/A') }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Total Amount:</strong>
                    <span class="text-success">{{ number_format($voucher->TotalAmount ?? 0, 2) }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong>
                    <span class="badge {{ $statusClass }}">
                    {{ ucfirst($voucher->ApprovalStatus) }}
                </span>
                </div>
            </div>

            {{-- Supplier / Payee Info --}}
            <h6 class="text-muted mb-2">Payee Information</h6>
            <div class="row border-bottom pb-2 mb-3">
                <div class="col-md-6">
                    <strong>Supplier Name:</strong>
                    <span>{{ ($voucher->invoice->thirdParty->TradingName ?? $voucher->invoice->thirdParty->ThirdPartyName) ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Email:</strong>
                    <span>{{ $voucher->invoice->thirdParty->Email ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Phone:</strong>
                    <span>{{ $voucher->invoice->thirdParty->Phone ?? 'N/A' }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Address:</strong>
                    <span>{{ $voucher->invoice->thirdParty->PhysicalAddress ?? 'N/A' }}</span>
                </div>
            </div>

            {{-- Payment Details Table --}}
            <h6 class="text-muted mb-2">Payment Details</h6>
            <table class="table table-sm table-bordered align-middle w-75">
                <thead class="table-light">
                <tr>
                    <th class="text-start">Description</th>
                    <th class="text-end">Amount ({{ $voucher->invoice->currency->Code ?? '' }})</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Invoice Total</td>
                    <td class="text-end">{{ number_format($invoiceReferenceAmount ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Cumulative Amount Paid</td>
                    <td class="text-end">{{ number_format($amtPaidOnInvoice ?? 0, 2) }}</td>
                </tr>
                @if($voucher->PaymentType === 'Scheduled')
                    <tr>
                        <td>Scheduled Date</td>
                        <td class="text-end">
                            {{ $voucher->StartDate ? \Carbon\Carbon::parse($voucher->StartDate)->format('d M Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Frequency</td>
                        <td class="text-end">{{ $voucher->Frequency ?? 'N/A' }}</td>
                    </tr>
                @endif
                <tr class="fw-bold table-light">
                    <td>Balance</td>
                    <td class="text-end">
                        {{ number_format($invoiceBalance ?? 0, 2) }}
                    </td>
                </tr>
                </tbody>
            </table>

            @php
                $previewInvoice = $invoicePayload['invoice'] ?? [];
                $previewSourceType = strtoupper((string)($previewInvoice['source_type'] ?? 'PO'));
                $previewAttachments = $invoicePayload['attachments'] ?? [];
                $previewContract = $invoicePayload['contract'] ?? null;
                $previewPo = $invoicePayload['po'] ?? null;
                $previewCurrency = $previewInvoice['currency_symbol'] ?? ($voucher->invoice->currency->Code ?? 'KES');
            @endphp

            @if(!empty($previewInvoice))
                <h6 class="text-muted mt-4 mb-2">Invoice Context for Approval</h6>

                <div class="card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-header bg-light py-2">
                        <strong>[{{ $previewSourceType }}] {{ $previewInvoice['invoice_number'] ?? 'N/A' }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="small text-muted">Before Tax</div>
                                <div class="fw-semibold">{{ $previewCurrency }} {{ number_format((float)($previewInvoice['before_tax'] ?? 0), 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Tax ({{ number_format((float)($previewInvoice['tax_percentage'] ?? 0), 2) }}%)</div>
                                <div class="fw-semibold">{{ $previewCurrency }} {{ number_format((float)($previewInvoice['tax_amount'] ?? 0), 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Invoice Total</div>
                                <div class="fw-semibold">{{ $previewCurrency }} {{ number_format((float)($previewInvoice['total_amount'] ?? 0), 2) }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="small text-muted">Remaining Balance</div>
                                <div class="fw-semibold">{{ $previewCurrency }} {{ number_format((float)($previewInvoice['balance'] ?? 0), 2) }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Attachments</div>
                            @if(!empty($previewAttachments))
                                <ul class="mb-0 ps-3">
                                    @foreach($previewAttachments as $attachment)
                                        <li class="small">
                                            {{ $attachment['name'] ?? ($attachment['document_id'] ?? 'Attachment') }}
                                            @if(!empty($attachment['mime_type']))
                                                <span class="text-muted">({{ $attachment['mime_type'] }})</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted small">No attachments on this invoice.</span>
                            @endif
                        </div>

                        @if($previewSourceType === 'CONTRACT')
                            <div class="small text-muted mb-2">Milestones and Checklist</div>
                            <div class="small mb-2">
                                <span class="text-muted">Contract Ref:</span>
                                <strong>{{ $previewContract['reference'] ?? 'N/A' }}</strong>
                            </div>
                            @if(!empty($previewContract['milestones']))
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Milestone</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                            <th>Checklist</th>
                                            <th class="text-end">Billed</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($previewContract['milestones'] as $milestone)
                                            <tr>
                                                <td><strong>M{{ $milestone['milestone_no'] ?? 0 }} - {{ $milestone['title'] ?? 'Milestone' }}</strong></td>
                                                <td>{{ $milestone['status'] ?? 'N/A' }}</td>
                                                <td>{{ $milestone['due_date'] ?? 'N/A' }}</td>
                                                <td>{{ $milestone['required_checklist_fulfilled'] ?? 0 }}/{{ $milestone['required_checklist_total'] ?? 0 }}</td>
                                                <td class="text-end">{{ $previewCurrency }} {{ number_format((float)($milestone['billed_amount'] ?? 0), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="bg-light-subtle">
                                                    @if(!empty($milestone['checklist_items']))
                                                        <ul class="mb-0 ps-3">
                                                            @foreach($milestone['checklist_items'] as $item)
                                                                <li class="small mb-1">
                                                                    {{ $item['description'] ?? 'Checklist item' }}
                                                                    <span class="badge {{ !empty($item['fulfilled']) ? 'bg-success' : 'bg-warning text-dark' }}">
                                                                        {{ !empty($item['fulfilled']) ? 'Fulfilled' : 'Pending' }}
                                                                    </span>
                                                                    <span class="text-muted">[{{ !empty($item['required']) ? 'Required' : 'Optional' }}]</span>
                                                                    @if(!empty($item['notes']))
                                                                        <span class="text-muted">({{ $item['notes'] }})</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted small">No checklist items.</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <span class="text-muted small">No milestone allocations found for this contract invoice.</span>
                            @endif
                        @else
                            <div class="small text-muted mb-2">Matched PO and GRN</div>
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <div class="border rounded p-2 h-100">
                                        <div class="small mb-1"><span class="text-muted">PO Number:</span> <strong>{{ $previewPo['order']['order_no'] ?? 'N/A' }}</strong></div>
                                        <div class="small mb-1"><span class="text-muted">PO Date:</span> {{ $previewPo['order']['order_date'] ?? 'N/A' }}</div>
                                        <div class="small mb-1"><span class="text-muted">Before Tax:</span> {{ $previewCurrency }} {{ number_format((float)($previewPo['order']['before_tax'] ?? 0), 2) }}</div>
                                        <div class="small mb-1"><span class="text-muted">Tax %:</span> {{ number_format((float)($previewPo['order']['tax_percentage'] ?? 0), 2) }}%</div>
                                        <div class="small"><span class="text-muted">After Tax:</span> {{ $previewCurrency }} {{ number_format((float)($previewPo['order']['after_tax'] ?? 0), 2) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-2 h-100">
                                        <div class="small mb-1"><span class="text-muted">GRN:</span> <strong>{{ $previewPo['grn']['grn_id'] ?? 'N/A' }}</strong></div>
                                        <div class="small mb-1"><span class="text-muted">Received Date:</span> {{ $previewPo['grn']['received_date'] ?? 'N/A' }}</div>
                                        <div class="small mb-1"><span class="text-muted">Ordered Qty:</span> {{ number_format((float)($previewPo['grn']['ordered_qty_total'] ?? 0), 2) }}</div>
                                        <div class="small"><span class="text-muted">Received Qty:</span> {{ number_format((float)($previewPo['grn']['received_qty_total'] ?? 0), 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            @if(!empty($previewPo['grn']['items']))
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-end">PO Qty</th>
                                            <th class="text-end">Received Qty</th>
                                            <th class="text-center">Match</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($previewPo['grn']['items'] as $grnItem)
                                            @php
                                                $poQty = (float) ($grnItem['po_qty'] ?? 0);
                                                $receivedQty = (float) ($grnItem['received_qty'] ?? 0);
                                            @endphp
                                            <tr>
                                                <td>{{ $grnItem['item_name'] ?? 'Item' }}</td>
                                                <td class="text-end">{{ number_format($poQty, 2) }}</td>
                                                <td class="text-end">{{ number_format($receivedQty, 2) }}</td>
                                                <td class="text-center">
                                                    <span class="badge {{ abs($poQty - $receivedQty) <= 0.0001 ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ abs($poQty - $receivedQty) <= 0.0001 ? 'Matched' : 'Variance' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            {{-- Amount in Words --}}
            @if(isset($amountInWords) && $amountInWords)
                <div class="mt-3 p-3 bg-light rounded border">
                    <strong class="text-muted">Amount in Words:</strong>
                    <span class="text-uppercase fw-bold text-dark">{{ $amountInWords }} {{ $voucher->invoice->currency->Code ?? '' }} ONLY</span>
                </div>
            @endif

            {{-- Remarks Section --}}
            @if(!empty($voucher->Description))
                <div class="mt-4">
                    <h6 class="text-muted mb-2">Remarks:</h6>
                    <i> {{ $voucher->Description }}</i>
                </div>
            @endif

            {{-- Approve / Reject Buttons --}}
            @if($voucher->ApprovalStatus === 'draft')
                <div class="mt-4 d-flex gap-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fas fa-check-circle me-1"></i> Approve
                    </button>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times-circle me-1"></i> Reject
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('paymentvoucher.approve', $voucher->Id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Approval</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="approveReason" class="form-label">Reason for approval</label>
                        <textarea class="form-control" name="Reasons" rows="3"
                                  placeholder="Optional reason..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success" id="approveProceedBtn" type="submit"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                            <span class="default-label"><i class="fas fa-check-circle"></i> Proceed to Approve</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('paymentvoucher.reject', $voucher->Id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Rejection</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label for="rejectReason" class="form-label">Reason for rejection</label>
                        <textarea class="form-control" name="Reasons" rows="3" required
                                  placeholder="Required reason..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" id="rejectProceedBtn" type="submit"
                                onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                            <span class="default-label"><i class="fas fa-check-circle"></i> Proceed to Reject</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Print-optimized layout (hidden on screen, visible on print) -->
    <div id="printRootVoucher" class="print-only" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
            <div>
                <div style="font-size:20px; font-weight:700;">Payment Voucher</div>
                <div style="color:#666;">Voucher #: {{ $voucher->VoucherNo }}</div>
                <div style="color:#666;">Date: {{ optional($voucher->CreatedOn)->format('d M Y') }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:24px; font-weight:700;">{{ $voucher->invoice->currency->Symbol ?? '' }} {{ number_format($voucher->TotalAmount ?? 0, 2) }}</div>
                <div style="color:#666;">Invoice Ref: {{ $voucher->invoice->InvoiceNumber ?? 'N/A' }}</div>
                <div style="color:#666;">Status: {{ ucfirst($voucher->ApprovalStatus) }}</div>
            </div>
        </div>

        <div style="display:flex; gap:20px; margin-bottom:20px;">
            <div style="flex:1; border:1px solid #e9ecef; padding:15px;">
                <div style="font-weight:600; margin-bottom:8px; border-bottom:1px solid #eee; padding-bottom:5px;">Payee Details</div>
                <div style="font-weight:bold;">{{ ($voucher->invoice->thirdParty->TradingName ?? $voucher->invoice->thirdParty->ThirdPartyName) ?? 'N/A' }}</div>
                <div>{{ $voucher->invoice->thirdParty->Email ?? '' }}</div>
                <div>{{ $voucher->invoice->thirdParty->Phone ?? '' }}</div>
                <div>{{ $voucher->invoice->thirdParty->PhysicalAddress ?? '' }}</div>
            </div>
            <div style="flex:1; border:1px solid #e9ecef; padding:15px;">
                <div style="font-weight:600; margin-bottom:8px; border-bottom:1px solid #eee; padding-bottom:5px;">Payment Details</div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Method:</span>
                    <strong>{{ $voucher->PaymentMethod ?? 'N/A' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Type:</span>
                    <strong>{{ ucfirst($voucher->PaymentType ?? 'N/A') }}</strong>
                </div>
                @if($voucher->PaymentType === 'Scheduled')
                    <div style="display:flex; justify-content:space-between;">
                        <span>Scheduled:</span>
                        <strong>{{ $voucher->StartDate ? \Carbon\Carbon::parse($voucher->StartDate)->format('d M Y') : 'N/A' }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <div style="border:1px solid #e9ecef; margin-bottom:20px;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:10px; background:#f8f9fa; border-bottom:1px solid #e9ecef;">Description</th>
                        <th style="text-align:right; padding:10px; background:#f8f9fa; border-bottom:1px solid #e9ecef;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid #f1f3f5;">Invoice Total</td>
                        <td style="padding:10px; text-align:right; border-bottom:1px solid #f1f3f5;">{{ number_format($invoiceReferenceAmount ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid #f1f3f5;">Cumulative Amount Paid</td>
                        <td style="padding:10px; text-align:right; border-bottom:1px solid #f1f3f5;">{{ number_format($amtPaidOnInvoice ?? 0, 2) }}</td>
                    </tr>
                    <tr style="font-weight:bold; background-color:#f8f9fa;">
                        <td style="padding:10px; border-top:1px solid #e9ecef;">Balance</td>
                        <td style="padding:10px; text-align:right; border-top:1px solid #e9ecef;">{{ number_format($invoiceBalance ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if(isset($amountInWords) && $amountInWords)
            <div style="border:1px solid #e9ecef; padding:15px; margin-bottom:20px; background-color:#f8f9fa;">
                <div style="font-weight:600; margin-bottom:5px;">Amount in Words</div>
                <div style="text-transform:uppercase; font-weight:bold;">{{ $amountInWords }} {{ $voucher->invoice->currency->Code ?? '' }} ONLY</div>
            </div>
        @endif

        @if(!empty($voucher->Description))
            <div style="border:1px solid #e9ecef; padding:15px; margin-bottom:20px;">
                <div style="font-weight:600; margin-bottom:5px;">Remarks</div>
                <div>{{ $voucher->Description }}</div>
            </div>
        @endif

        <div style="margin-top:40px; display:flex; justify-content:space-between;">
            <div style="text-align:center; width:200px;">
                <div style="border-bottom:1px solid #000; height:30px;"></div>
                <div style="margin-top:5px;">Prepared By</div>
            </div>
            <div style="text-align:center; width:200px;">
                <div style="border-bottom:1px solid #000; height:30px;"></div>
                <div style="margin-top:5px;">Approved By</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Print styles
        const printCSS = `
        @page { size: A4 portrait; margin: 12mm; }
        @media print {
            html, body { font-size: 12px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .navbar, .btn, .modal, .lifecycle, .attachments-section, .breadcrumb { display: none !important; }
            .card, .shadow, .shadow-sm, .shadow-lg { box-shadow: none !important; border: 1px solid #e9ecef !important; }
            .rounded-top-4 { background: #ffffff !important; }
            .p-4, .p-md-5 { padding: 12px !important; }
            .mt-4, .mt-3 { margin-top: 10px !important; }
            h1, h5 { margin: 0 0 6px 0 !important; }
            .table-responsive { overflow: visible !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { padding: 6px 8px !important; }
            thead th { background: #f8f9fa !important; }
            .card, .table-responsive, table { page-break-inside: avoid; }
            a[href]:after { content: "" !important; }
        }
        `;
        const style = document.createElement('style');
        style.innerHTML = printCSS;
        document.head.appendChild(style);

        // Toggle print-only vs screen
        const printRoot = document.getElementById('printRootVoucher');
        const screenRootCards = document.querySelectorAll('.card');

        window.addEventListener('beforeprint', () => {
            printRoot && (printRoot.style.display = 'block');
            screenRootCards.forEach(c => c.classList.add('d-print-none'));
        });

        window.addEventListener('afterprint', () => {
            printRoot && (printRoot.style.display = 'none');
            screenRootCards.forEach(c => c.classList.remove('d-print-none'));
        });
    </script>
@endpush
