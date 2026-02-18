@php
    use App\Services\DMS\DocumentService;
@endphp

@extends('layouts.app')
{{-- @section('title', 'Invoice • ' . ($invoice->InvoiceNumber ?? 'View')) --}}

@section('content')
    <style>
        /* Hide top navigation only on this page */
        .navbar, nav.navbar, .pc-header, header.pc-header, .pc-h-item[data-pc-toggle="sidebar"], .mobile-menu { display: none !important; }
        body { padding-top: 0 !important; }
    </style>
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow rounded-4 border-0">
        <!-- Header -->
        <div class="p-4 p-md-5 border-bottom bg-light rounded-top-4" style="background: linear-gradient(135deg, #f8fafc, #eef2ff);">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge rounded-pill text-bg-info px-3 py-2">Payables Invoice</span>
                        @if($currencyCode)
                            <span class="badge rounded-pill text-bg-secondary px-3 py-2">{{ $currencyCode }}</span>
                        @endif
                        @if(!empty($invoice->ApprovalStatus))
                            <span class="badge rounded-pill text-bg-{{ $invoice->ApprovalStatus === 'posted' ? 'success' : ($invoice->ApprovalStatus === 'rejected' ? 'danger' : 'warning') }} px-3 py-2">
                            {{ ucfirst($invoice->ApprovalStatus) }}
                        </span>
                        @endif
                    </div>
                    <h1 class="h3 mt-3 mb-1 d-flex align-items-center gap-2">
                        <i class="fas fa-file-invoice"></i> Invoice: <span class="fw-bold">{{ $invNo }}</span>
                        <button class="btn btn-sm btn-outline-secondary" id="copyInvBtn" title="Copy Invoice Number">
                            <i data-feather="copy"></i>
                        </button>
                    </h1>
                    <div class="text-muted">Dated <strong>{{ $invDate }}</strong></div>
                </div>

                <div class="text-md-end">
                    <div class="display-6 fw-semibold mb-1">
                        <span class="opacity-75">{{ $currencySymbol }} </span>{{ $amount }}
                    </div>
                    <div class="small text-muted">
                        Exchange Rate: <strong>{{ number_format($exRate, 4) }}</strong>
                    </div>
                    <div class="mt-3 d-flex gap-2 justify-content-md-end">
                        <a href="{{ route('invoiceentry.index') }}" class="btn btn-outline-secondary">
                            <i data-feather="arrow-left"></i> Back
                        </a>
{{--                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#poItemsModal" @disabled(!$invoice->order)>--}}
{{--                            <i data-feather="file-text"></i> View PO Items--}}
{{--                        </button>--}}
                        <button type="button" class="btn btn-success" onclick="window.print()">
                            <i data-feather="printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="p-4 p-md-5">
            <!-- Top info row -->
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="text-uppercase text-muted mb-0">Vendor</h6>
                                <i data-feather="user" class="opacity-50"></i>
                            </div>
                            <div class="fs-5 fw-semibold mt-2">{{ $vendorName }}</div>
                            @if(!empty($isContractInvoice))
                                <div class="mt-3 small">
                                    <div class="text-muted">Contract Reference</div>
                                    <div class="fw-medium">{{ $contractReference ?? 'N/A' }}</div>
                                </div>
                            @else
                                <div class="mt-3 small">
                                    <div class="text-muted">PO Reference</div>
                                    <div class="fw-medium">{{ $poNo }}</div>
                                </div>
                                <div class="mt-2 small">
                                    <div class="text-muted">GRN Reference</div>
                                    <div class="fw-medium">{{ $grnNo ?? 'N/A' }}</div>
                                </div>
                            @endif
                            @if(!empty($invoice->Description))
                                <div class="mt-3 small">
                                    <div class="text-muted mb-1">Description</div>
                                    <div class="border rounded-3 p-2 bg-light">{{ $invoice->Description }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="text-uppercase text-muted mb-0">Amounts</h6>
                                <i data-feather="pie-chart" class="opacity-50"></i>
                            </div>
                            <div class="table-responsive mt-3">
                                <table class="table align-middle mb-0">
                                    <tbody>
                                    <tr>
                                        <td class="text-muted">Invoice Amount (After Tax)</td>
                                        <td class="text-end">
                                            <strong>{{ $currencySymbol }} {{ number_format((float)($invoiceAfterTax ?? 0), 2) }}</strong>
                                        </td>
                                    </tr>
                                    @if(!empty($isContractInvoice))
                                        <tr>
                                            <td class="text-muted">Invoice Tax ({{ number_format((float)($invoiceTaxPct ?? 0), 1) }}%)</td>
                                            <td class="text-end">{{ $currencySymbol }} {{ number_format((float)($invoiceTaxAmount ?? 0), 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Invoice Before Tax</td>
                                            <td class="text-end">{{ $currencySymbol }} {{ number_format((float)($invoiceBeforeTax ?? 0), 2) }}</td>
                                        </tr>
                                    @elseif(!empty($invoice->order))
                                        @php
                                            $poExcl = (float)($invoice->order->OrdTotExcl ?? 0);
                                            $poDisc = (float)($invoice->order->OrdDiscAmnt ?? 0);
                                            // Calculate tax based on order percentage
                                            $taxPct = (float)($invoice->order->TaxPercentage ?? 0);
                                            $poTax = $poExcl * ($taxPct / 100);
                                            $poIncl = $poExcl + $poTax;
                                        @endphp
                                        @if($poDisc > 0)
                                            <tr>
                                                <td class="text-muted">PO Discount</td>
                                                <td class="text-end">- {{ $currencySymbol }} {{ number_format($poDisc, 2) }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="text-muted">PO Tax ({{ number_format($taxPct, 1) }}%)</td>
                                            <td class="text-end">{{ $currencySymbol }} {{ number_format($poTax, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">PO Before Tax</td>
                                            <td class="text-end">{{ $currencySymbol }} {{ number_format($poExcl, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">PO After Tax</td>
                                            <td class="text-end"><strong>{{ $currencySymbol }} {{ number_format($poIncl, 2) }}</strong></td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="text-muted">PO Subtotal (Items)</td>
                                            <td class="text-end">
                                                <span>{{ $currencySymbol }} {{ number_format($poSub, 2) }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    {{-- @if(property_exists($invoice, 'TaxAmount') || isset($invoice->TaxAmount))
                                        <tr>
                                            <td class="text-muted">Tax Amount</td>
                                            <td class="text-end">
                                                <span>{{ $currencySymbol }} {{ number_format((float)($invoice->TaxAmount ?? 0), 2) }}</span>
                                            </td>
                                        </tr>
                                    @endif --}}
                                    @if(property_exists($invoice, 'WithholdingTax') || isset($invoice->WithholdingTax))
                                        <tr>
                                            <td class="text-muted">Withholding</td>
                                            <td class="text-end">
                                                <span>- {{ $currencySymbol }} {{ number_format((float)($invoice->WithholdingTax ?? 0), 2) }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                    @php
                                        $netPayable = (float)($invoice->InvoiceAmount ?? 0)
                                                      - (float)($invoice->WithholdingTax ?? 0);
                                    @endphp
                                    {{-- <tr class="table-light">
                                        <td class="fw-semibold">Net Payable</td>
                                        <td class="text-end fw-semibold">
                                            {{ $currencySymbol }} {{ number_format($netPayable, 2) }}
                                        </td>
                                    </tr> --}}
                                    </tbody>
                                </table>
                            </div>
                            @if(!empty($invoice->CurrencyID) && (float)$exRate !== 1.0)
                                <div class="small text-muted mt-2">
                                    *Converted using rate {{ number_format($exRate, 4) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 lifecycle">
                    <div class="card h-100 border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="text-uppercase text-muted mb-0">Lifecycle</h6>
                                <i data-feather="activity" class="opacity-50"></i>
                            </div>

                            <ul class="list-unstyled mt-3 small">
                                <li class="d-flex gap-2 align-items-start">
                                    <i data-feather="clock" class="mt-1 opacity-50" width="16" height="16"></i>
                                    <div>
                                        <div class="text-muted">Created</div>
                                        <div class="fw-medium">
                                            {{ optional($invoice->CreatedOn)->format('d M Y, H:i') ?? '—' }}
                                        </div>
                                    </div>
                                </li>
                                <li class="d-flex gap-2 align-items-start mt-2">
                                    <i data-feather="edit-3" class="mt-1 opacity-50" width="16" height="16"></i>
                                    <div>
                                        <div class="text-muted">Last Updated</div>
                                        <div class="fw-medium">
                                            {{ optional($invoice->ModifiedOn)->format('d M Y, H:i') ?? '—' }}
                                        </div>
                                    </div>
                                </li>
                                @if(!empty($invoice->ApprovedOn))
                                    <li class="d-flex gap-2 align-items-start mt-2">
                                        <i data-feather="check-circle" class="mt-1 opacity-50" width="16" height="16"></i>
                                        <div>
                                            <div class="text-muted">Approved</div>
                                            <div class="fw-medium">
                                                {{ \Carbon\Carbon::parse($invoice->ApprovedOn)->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            </ul>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments Section -->
            <div class="card mt-4 border-0 shadow-sm rounded-4 attachments-section">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="text-uppercase text-muted mb-0">
                            <i data-feather="paperclip" class="me-2" width="16" height="16"></i>
                            Attachments
                        </h6>
                    </div>
                    <div class="mt-3" id="invoiceAttachments">
                        @php
                            $documents = $invoice->documents()
                                ->get(['t_Documents.Id','t_Documents.DocumentId','MimeType','Name']);
                        @endphp

                        @forelse($documents as $document)
                            @php
                                // Avoid any morph relation lookups during render
                                $document->setRelations([]);
                            @endphp
                            {!! (new DocumentService($document))->summaryList() !!}
                        @empty
                            <span class="text-muted">No attachments uploaded</span>
                        @endforelse
                    </div>
                </div>
            </div>

            @if(!empty($isContractInvoice))
                <div class="card mt-4 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Milestones and Checklist</h5>
                            <div class="text-muted small">Contract: <strong>{{ $contractReference ?? 'N/A' }}</strong></div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Milestone</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Checklist</th>
                                    <th class="text-end">Billed Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse(($contractMilestoneDetails ?? collect()) as $milestone)
                                    <tr>
                                        <td class="fw-semibold">M{{ $milestone->MilestoneNo }} - {{ $milestone->Title }}</td>
                                        <td>
                                            <span class="badge bg-{{ $milestone->Status === 'Accepted' ? 'success' : ($milestone->Status === 'Waived' ? 'secondary' : ($milestone->Status === 'Submitted' ? 'info' : ($milestone->Status === 'Rejected' ? 'danger' : 'warning'))) }}">
                                                {{ $milestone->Status ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ !empty($milestone->PlannedDueDate) ? \Carbon\Carbon::parse($milestone->PlannedDueDate)->format('d M Y') : 'N/A' }}</td>
                                        <td>{{ (int) ($milestone->RequiredChecklistFulfilled ?? 0) }}/{{ (int) ($milestone->RequiredChecklistTotal ?? 0) }}</td>
                                        <td class="text-end">{{ $currencySymbol }} {{ number_format((float) ($milestone->BilledAmount ?? 0), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="bg-light-subtle">
                                            @if(!empty($milestone->ChecklistItems) && count($milestone->ChecklistItems) > 0)
                                                <div class="small text-muted mb-2">Checklist items</div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 55%">Item</th>
                                                            <th style="width: 15%">Required</th>
                                                            <th style="width: 15%">Fulfilled</th>
                                                            <th style="width: 15%">Notes</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($milestone->ChecklistItems as $checkItem)
                                                            <tr>
                                                                <td>{{ $checkItem->ItemDescription }}</td>
                                                                <td>
                                                                    <span class="badge bg-{{ !empty($checkItem->Required) ? 'primary' : 'secondary' }}">
                                                                        {{ !empty($checkItem->Required) ? 'Yes' : 'No' }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-{{ !empty($checkItem->IsFulfilled) ? 'success' : 'warning text-dark' }}">
                                                                        {{ !empty($checkItem->IsFulfilled) ? 'Yes' : 'No' }}
                                                                    </span>
                                                                </td>
                                                                <td class="small">{{ $checkItem->Notes ?: '—' }}</td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <span class="text-muted small">No checklist maintained for this milestone.</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No milestone allocations found for this contract invoice.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card mt-4 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">Matched PO and GRN</h5>
                            <div class="text-muted small">3-way reference used for this invoice</div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-muted text-uppercase mb-2">Matched Purchase Order</h6>
                                    <div class="small mb-1"><span class="text-muted">PO Number:</span> <strong>{{ $matchedPoData->OrderNo ?? $poNo ?? 'N/A' }}</strong></div>
                                    <div class="small mb-1"><span class="text-muted">PO Date:</span> {{ !empty($matchedPoData->OrderDate) ? \Carbon\Carbon::parse($matchedPoData->OrderDate)->format('d M Y') : 'N/A' }}</div>
                                    <div class="small mb-1"><span class="text-muted">Before Tax:</span> {{ $currencySymbol }} {{ number_format((float)($matchedPoData->OrdTotExcl ?? 0), 2) }}</div>
                                    <div class="small mb-1"><span class="text-muted">Tax %:</span> {{ number_format((float)($matchedPoData->TaxPercentage ?? 0), 2) }}%</div>
                                    <div class="small"><span class="text-muted">After Tax:</span> <strong>{{ $currencySymbol }} {{ number_format((float)($matchedPoData->OrdTotIncl ?? 0), 2) }}</strong></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-muted text-uppercase mb-2">Matched Goods Receipt</h6>
                                    <div class="small mb-1"><span class="text-muted">GRN Number:</span> <strong>{{ $matchedGrnData->GRNID ?? $grnNo ?? 'N/A' }}</strong></div>
                                    <div class="small mb-1"><span class="text-muted">PO Ref in GRN:</span> {{ $matchedGrnData->POID ?? 'N/A' }}</div>
                                    <div class="small mb-1"><span class="text-muted">Received Date:</span> {{ !empty($matchedGrnData->ReceivedDate) ? \Carbon\Carbon::parse($matchedGrnData->ReceivedDate)->format('d M Y') : 'N/A' }}</div>
                                    <div class="small mb-1"><span class="text-muted">Ordered Qty:</span> {{ number_format((float)(($matchedGrnTotals['ordered_qty'] ?? 0)), 2) }}</div>
                                    <div class="small"><span class="text-muted">Received Qty:</span> <strong>{{ number_format((float)(($matchedGrnTotals['received_qty'] ?? 0)), 2) }}</strong></div>
                                </div>
                            </div>
                        </div>

                        @if(!empty($matchedGrnItems) && count($matchedGrnItems) > 0)
                            <div class="table-responsive mt-3">
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
                                    @foreach($matchedGrnItems as $grnItem)
                                        <tr>
                                            <td>{{ $grnItem->ItemName }}</td>
                                            <td class="text-end">{{ number_format((float)($grnItem->POQTY ?? 0), 2) }}</td>
                                            <td class="text-end">{{ number_format((float)($grnItem->ReceivedQTY ?? 0), 2) }}</td>
                                            <td class="text-center">
                                                @php
                                                    $qtyDiff = abs((float)($grnItem->POQTY ?? 0) - (float)($grnItem->ReceivedQTY ?? 0));
                                                @endphp
                                                @if($qtyDiff <= 0.0001)
                                                    <span class="badge bg-success">Matched</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Variance</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Items preview (inline table) -->
            <div class="card mt-4 border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Items Breakdown</h5>
                        <div class="text-muted small">{{ $sourceReferenceLabel ?? ('From PO: ' . ($poNo ?? '—')) }}</div>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th style="width: 40%">Item</th>
                                <th class="text-end" style="width: 12%">Qty</th>
                                <th class="text-end" style="width: 18%">Unit Cost</th>
                                <th class="text-end" style="width: 18%">Line Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($poItems as $row)
                                @php
                                    $lineTotal = ((float)$row->UnitCost) * ((float)$row->Quantity);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $row->ItemName }}</div>
                                        @if(!empty($row->Description))
                                            <div class="text-muted small">{{ $row->Description }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format((float)$row->Quantity, 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol }} {{ number_format((float)$row->UnitCost, 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol }} {{ number_format($lineTotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        {{ !empty($isContractInvoice) ? 'No billed milestones found.' : 'No PO items found.' }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                            @if(count($poItems) > 0)
                                @if(!empty($isContractInvoice))
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end">Tax ({{ number_format((float)($invoiceTaxPct ?? 0), 1) }}%)</th>
                                            <th class="text-end">{{ $currencySymbol }} {{ number_format((float)($invoiceTaxAmount ?? 0), 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Total Before Tax</th>
                                            <th class="text-end">{{ $currencySymbol }} {{ number_format((float)($invoiceBeforeTax ?? 0), 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Total After Tax</th>
                                            <th class="text-end">{{ $currencySymbol }} {{ number_format((float)($invoiceAfterTax ?? 0), 2) }}</th>
                                        </tr>
                                    </tfoot>
                                @elseif(!empty($invoice->order))
                                    @php
                                        $poExcl = (float)($invoice->order->OrdTotExcl ?? 0);
                                        $poDisc = (float)($invoice->order->OrdDiscAmnt ?? 0);
                                        $taxPct = (float)($invoice->order->TaxPercentage ?? 0);
                                        $poTax = $poExcl * ($taxPct / 100);
                                        $poIncl = $poExcl + $poTax;
                                    @endphp
                                    <tfoot class="table-light">
                                        @if($poDisc > 0)
                                            <tr>
                                                <th colspan="3" class="text-end">Discount</th>
                                                <th class="text-end">- {{ $currencySymbol }} {{ number_format($poDisc, 2) }}</th>
                                            </tr>
                                        @endif
                                        <tr>
                                            <th colspan="3" class="text-end">Tax ({{ number_format($taxPct, 1) }}%)</th>
                                            <th class="text-end">{{ $currencySymbol }} {{ number_format($poTax, 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Total Before Tax</th>
                                            <th class="text-end">{{ $currencySymbol }} {{ number_format($poExcl, 2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Total After Tax</th>
                                            <th class="text-end">{{ $currencySymbol }} {{ number_format($poIncl, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                @else
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end">PO Subtotal</th>
                                            <th class="text-end">{{ $currencySymbol }} {{ number_format($poSub, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <!-- Meta chips -->
            <div class="mt-4 d-flex flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-light border">
                <i data-feather="hash"></i> Invoice ID: {{ $invoice->Id ?? $invoice->id ?? '—' }}
            </span>
                @if(!empty($invoice->CreatedBy))
                    <span class="badge rounded-pill text-bg-light border">
                <i data-feather="user-check"></i> Created By: <b> {{ $invoice->createdBy->Name }}</b>
            </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Print-optimized layout (hidden on screen, visible on print) -->
    <div id="printRootInvoice" class="print-only" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
            <div>
                <div style="font-size:16px; font-weight:700;">Payables Invoice</div>
                <div style="color:#666;">Invoice: {{ $invNo }}</div>
                <div style="color:#666;">Date: {{ $invDate }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:18px; font-weight:700;">{{ $currencySymbol }} {{ $amount }}</div>
                @if(!empty($isContractInvoice))
                    <div style="color:#666;">Contract: {{ $contractReference ?? 'N/A' }}</div>
                @else
                    <div style="color:#666;">PO: {{ $poNo }}</div>
                    <div style="color:#666;">GRN: {{ $grnNo ?? 'N/A' }}</div>
                @endif
            </div>
        </div>

        <div style="display:flex; gap:12px; margin-bottom:10px;">
            <div style="flex:1; border:1px solid #e9ecef; padding:8px;">
                <div style="font-weight:600; margin-bottom:4px;">Vendor / Supplier</div>
                <div>{{ $vendorName }}</div>
            </div>
            <div style="flex:1; border:1px solid #e9ecef; padding:8px;">
                <div style="font-weight:600; margin-bottom:4px;">References</div>
                @if(!empty($isContractInvoice))
                    <div>Contract: {{ $contractReference ?? 'N/A' }}</div>
                @else
                    <div>PO: {{ $poNo }}</div>
                    <div>GRN: {{ $grnNo ?? 'N/A' }}</div>
                @endif
            </div>
            <div style="flex:1; border:1px solid #e9ecef; padding:8px;">
                <div style="font-weight:600; margin-bottom:4px;">Summary</div>
                @if(!empty($isContractInvoice))
                    <div>Invoice Before Tax: {{ $currencySymbol }} {{ number_format((float)($invoiceBeforeTax ?? 0), 2) }}</div>
                    <div>Tax ({{ number_format((float)($invoiceTaxPct ?? 0), 1) }}%): {{ $currencySymbol }} {{ number_format((float)($invoiceTaxAmount ?? 0), 2) }}</div>
                    <div>Invoice After Tax: {{ $currencySymbol }} {{ number_format((float)($invoiceAfterTax ?? 0), 2) }}</div>
                @else
                    <div>Invoice Amount: {{ $currencySymbol }} {{ number_format((float)($invoice->TotalAmount ?? 0), 2) }}</div>
                    <div>PO Subtotal: {{ $currencySymbol }} {{ number_format($poSub, 2) }}</div>
                @endif
            </div>
        </div>

        <div style="border:1px solid #e9ecef;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding:6px 8px; background:#f8f9fa; border-bottom:1px solid #e9ecef;">Item</th>
                        <th style="text-align:right; padding:6px 8px; background:#f8f9fa; border-bottom:1px solid #e9ecef;">Qty</th>
                        <th style="text-align:right; padding:6px 8px; background:#f8f9fa; border-bottom:1px solid #e9ecef;">Unit Cost</th>
                        <th style="text-align:right; padding:6px 8px; background:#f8f9fa; border-bottom:1px solid #e9ecef;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($poItems as $row)
                    @php $lineTotal = ((float)($row->UnitPrice ?? $row->UnitCost ?? 0)) * ((float)($row->Quantity ?? 0)); @endphp
                    <tr>
                        <td style="padding:6px 8px; border-bottom:1px solid #f1f3f5;">
                            <div style="font-weight:600;">{{ $row->ItemName }}</div>
                            @if(!empty($row->Description))
                                <div style="color:#666; font-size:12px;">{{ $row->Description }}</div>
                            @endif
                        </td>
                        <td style="padding:6px 8px; text-align:right; border-bottom:1px solid #f1f3f5;">{{ number_format((float)($row->Quantity ?? 0), 2) }}</td>
                        <td style="padding:6px 8px; text-align:right; border-bottom:1px solid #f1f3f5;">{{ $currencySymbol }} {{ number_format((float)($row->UnitPrice ?? $row->UnitCost ?? 0), 2) }}</td>
                        <td style="padding:6px 8px; text-align:right; border-bottom:1px solid #f1f3f5;">{{ $currencySymbol }} {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    @if(!empty($isContractInvoice))
                        <tr>
                            <th colspan="3" style="text-align:right; padding:6px 8px; border-top:1px solid #e9ecef;">Tax ({{ number_format((float)($invoiceTaxPct ?? 0), 1) }}%)</th>
                            <th style="text-align:right; padding:6px 8px; border-top:1px solid #e9ecef;">{{ $currencySymbol }} {{ number_format((float)($invoiceTaxAmount ?? 0), 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align:right; padding:6px 8px; border-top:1px solid #e9ecef;">Total Before Tax</th>
                            <th style="text-align:right; padding:6px 8px; border-top:1px solid #e9ecef;">{{ $currencySymbol }} {{ number_format((float)($invoiceBeforeTax ?? 0), 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="3" style="text-align:right; padding:6px 8px; border-top:1px solid #e9ecef;">Total After Tax</th>
                            <th style="text-align:right; padding:6px 8px; border-top:1px solid #e9ecef;">{{ $currencySymbol }} {{ number_format((float)($invoiceAfterTax ?? 0), 2) }}</th>
                        </tr>
                    @else
                        <tr>
                            <th colspan="3" style="text-align:right; padding:6px 8px; border-top:1px solid #e9ecef;">PO Subtotal</th>
                            <th style="text-align:right; padding:6px 8px; border-top:1px solid #e9ecef;">{{ $currencySymbol }} {{ number_format($poSub, 2) }}</th>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>

    @if($invoice->ApprovalStatus==='draft')
        <div class="mb-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal" @disabled(!empty($invoice->Status) && $invoice->Status === 'Approved')>
                <i data-feather="thumbs-up"></i> Approve
            </button>

            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" @disabled(!empty($invoice->Status) && $invoice->Status === 'Rejected')}>
                <i data-feather="thumbs-down"></i> Reject
            </button>
        </div>
    @endif

    <!-- PO Items Modal (detailed view) -->
    <div class="modal fade" id="poItemsModal" tabindex="-1" aria-labelledby="poItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="poItemsModalLabel">Purchase Order {{ $poNo }} — Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php $i=1; @endphp
                            @forelse($poItems as $row)
                                @php $lt = ((float)$row->UnitCost) * ((float)$row->Quantity); @endphp
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $row->ItemName }}</td>
                                    <td class="small text-muted">{{ $row->Description ?? '—' }}</td>
                                    <td class="text-end">{{ number_format((float)$row->Quantity, 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol }}{{ number_format((float)$row->UnitCost, 2) }}</td>
                                    <td class="text-end">{{ $currencySymbol }}{{ number_format($lt, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No items available.</td>
                                </tr>
                            @endforelse
                            </tbody>
                            @if(count($poItems) > 0)
                                <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">PO Subtotal</th>
                                    <th class="text-end">{{ $currencySymbol }}{{ number_format($poSub, 2) }}</th>
                                </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if($invoice->ApprovalStatus==='draft')
        @php
            // Calculate inclusive amount for display in modals
            $displayAmount = (float)($invoiceAfterTax ?? ($invoice->TotalAmount ?? 0));
            if (empty($isContractInvoice) && !empty($invoice->order)) {
                $poExcl = (float)($invoice->order->OrdTotExcl ?? 0);
                $taxPct = (float)($invoice->order->TaxPercentage ?? 0);
                $poTax = $poExcl * ($taxPct / 100);
                $displayAmount = $poExcl + $poTax;
            }
        @endphp
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Approve Invoice {{ $invoice->InvoiceNumber ?? ($invoice->Id ?? $invoice->id) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('ap.invoice.approve', $invoice->Id ?? $invoice->id) }}" method="POST" id="approveForm">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="alert alert-info small">
                                You’re about to approve this invoice.
                                <div class="mt-1"><strong>Amount:</strong> {{ ($invoice->currency->Symbol ?? '') . number_format($displayAmount, 2) }}</div>
                                @if(!empty($invoice->order?->OrderNo))
                                    <div><strong>PO:</strong> {{ $invoice->order->OrderNo }}</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason / Comment <span class="text-danger">*</span></label>
                                <textarea name="Reason" class="form-control" rows="3" placeholder="Add an approval note for audit trail" required></textarea>
                                <div class="form-text">This will be stored in the approval history.</div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-success" id="approveProceedBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                                <span class="default-label"><i class="fas fa-check-circle"></i> Proceed to Approve</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-md modal-dialog-centered">
                <div class="modal-content rounded-4">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Invoice {{ $invoice->InvoiceNumber ?? ($invoice->Id ?? $invoice->id) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form action="{{ route('ap.invoice.reject', $invoice->Id ?? $invoice->id) }}" method="POST" id="rejectForm">
                        @csrf
                        @method('POST')
                        <div class="modal-body">
                            <div class="alert alert-warning small">
                                You’re about to reject this invoice.
                                <div class="mt-1"><strong>Amount:</strong> {{ ($invoice->currency->Symbol ?? '') . number_format($displayAmount, 2) }}</div>
                                @if(!empty($invoice->order?->OrderNo))
                                    <div><strong>PO:</strong> {{ $invoice->order->OrderNo }}</div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                <textarea name="Reason" class="form-control" rows="3" placeholder="Provide a clear reason for rejection" required></textarea>
                                <div class="form-text">This will be shared with the originator and stored in the history.</div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" id="rejectProceedBtn" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Processing...'; this.form.submit();}">
                                <span class="default-label"><i class="fas fa-check-circle"></i> Proceed to Reject</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

@endsection

@push('scripts')
    <script>
        // Feather icons
        if (typeof feather !== 'undefined') { feather.replace(); }

        // Copy invoice number
        document.getElementById('copyInvBtn')?.addEventListener('click', function(){
            const txt = @json($invNo);
            navigator.clipboard.writeText(txt).then(() => {
                const btn = this;
                const original = btn.innerHTML;
                btn.innerHTML = '<i data-feather="check"></i>';
                feather.replace();
                setTimeout(() => { btn.innerHTML = original; feather.replace(); }, 1200);
            });
        });

        // Nice print styles (hide nav/buttons on print)
        const printCSS = `
        /* Keep attachment chips nice on screen */
        #invoiceAttachments .modal-preview-document{ display:inline-flex; align-items:center; gap:.375rem; padding:.25rem .6rem; font-size:.85rem; background:#f8f9fa; border:1px solid #dee2e6; border-radius:.375rem; text-decoration:none; color:#495057; margin:.125rem .25rem .125rem 0; }
        #invoiceAttachments .modal-preview-document:hover{ filter:brightness(0.97); text-decoration:none; color:#495057; }

        @page { size: A4 portrait; margin: 12mm; }
        @media print {
            /* Base */
            html, body { font-size: 12px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            /* Hide UI/controls */
            .navbar, .btn, .modal, .lifecycle, .attachments-section, .breadcrumb { display: none !important; }

            /* Flatten cards */
            .card, .shadow, .shadow-sm, .shadow-lg { box-shadow: none !important; border: 1px solid #e9ecef !important; }
            .rounded-top-4 { background: #ffffff !important; }

            /* Compact spacing */
            .p-4, .p-md-5 { padding: 12px !important; }
            .mt-4, .mt-3 { margin-top: 10px !important; }
            h1, h5 { margin: 0 0 6px 0 !important; }

            /* Tables fit nicely */
            .table-responsive { overflow: visible !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { padding: 6px 8px !important; }
            thead th { background: #f8f9fa !important; }

            /* Avoid breaking important blocks */
            .card, .table-responsive, table { page-break-inside: avoid; }

            /* Remove link hints */
            a[href]:after { content: "" !important; }
        }
    `;
        const style = document.createElement('style');
        style.innerHTML = printCSS;
        document.head.appendChild(style);

        // Toggle print-only vs screen
        const printRoot = document.getElementById('printRootInvoice');
        const screenRootCards = document.querySelectorAll('.card');

        window.addEventListener('beforeprint', () => {
            // Hide screen layout, show print layout
            printRoot && (printRoot.style.display = 'block');
            screenRootCards.forEach(c => c.classList.add('d-print-none'));
        });

        window.addEventListener('afterprint', () => {
            // Restore screen layout
            printRoot && (printRoot.style.display = 'none');
            screenRootCards.forEach(c => c.classList.remove('d-print-none'));
        });
    </script>
@endpush

@push('scripts')
    @includeIf('snippets.actions.preview-files')
@endpush
