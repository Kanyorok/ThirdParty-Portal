@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'RFQ Details')
@section('content')
<div class="container-fluid">
    <button type="button" class="btn btn-primary mb-3"
        data-bs-toggle="modal" data-bs-target="#createRFQModal"
        @if($rfq->rfqLines->where('RFQId', $rfq->Id)->count()) disabled @endif>
        + New RFQ Line
    </button>
    <div id="printSection">
        <div class="card mb-3">
            <div class="card-body">
                <p><strong>RFQ Number:</strong> {{ $rfq->RFQNumber }}</p>
                <p><strong>RFQ Comments:</strong> {{ $rfq->Comments }}</p>
                <p><strong>Requisition No:</strong> {{ $rfq->requisition->RequisitionNo }}</p>
                <p><strong>Status:</strong>
                    <span class="badge
                    @if(in_array($rfq->Status, ['Pending', 'pe'])) bg-warning
                    @elseif(in_array($rfq->Status, ['Approved', 'Ap', 'AP'])) bg-success
                    @elseif(in_array($rfq->Status, ['Rejected', 'Re', 'RE'])) bg-danger
                    @else bg-secondary @endif">
                        @if(in_array($rfq->Status, ['Pending', 'pe'])) Pending
                        @elseif(in_array($rfq->Status, ['Approved', 'Ap', 'AP'])) Approved
                        @elseif(in_array($rfq->Status, ['Rejected', 'Re', 'RE'])) Rejected
                        @else {{ $rfq->Status }} @endif
                    </span>
                </p>
                <p><strong>RFQ Reject Remarks:</strong> {{ $rfq->Remarks }}</p>

            </div>
        </div>

        <h5>Requisition Items Details:</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>RFQ Line No</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>UOM</th>
                    <th>Submission Deadline</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rfq->rfqLines as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->RFQLineNo }}</td>
                    <td>{{ $item->ItemName}}</td>
                    <td>{{ $item->Quantity }}</td>
                    <td>{{ $item->uom->Name}}</td>
                    <td>{{ Carbon::parse($rfq->SubmissionDeadline)->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center">
                        @if ($canApprove)
                        <form id="approveForm" action="{{ route('rfqs.approve', $rfq->Id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn btn-success btn-sm"
                                onclick="return confirm('Are you sure you want to approve this RFQ?');"
                                {{ $rfq->rfqLines->isEmpty() ? 'disabled title=Please add at least one RFQ line' : '' }}>
                                Approve
                            </button>
                        </form>
                        @endif

                    </td>

                    <td colspan="2" class="text-center">
                        @if ($canApprove)
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                            data-bs-target="#rejectModal"
                            {{ $rfq->rfqLines->isEmpty() ? 'disabled title=Please add at least one RFQ line' : '' }}>
                            Reject
                        </button>
                        @endif
                    </td>

                    {{-- <td class="text-center">--}}
                    {{-- @if ($rfq->Status === 'Approved')--}}
                    {{-- <button type="button" class="btn btn-primary btn-sm">Save</button>--}}
                    {{-- @endif--}}
                    {{-- </td>--}}


                </tr>
            </tfoot>

        </table>

        <div class="row mt-4">
            <div class="col-md-6">
                <h5>Pending Approvals</h5>
                @if(count($pendingApprovals) > 0)
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Stage</th>
                            <th>Approver</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingApprovals as $pending)
                        <tr>
                            <td>{{ $pending->stage_name }}</td>
                            <td>{{ $pending->user_name }}</td>
                            <td>Pending</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-muted">No pending approvals.</p>
                @endif
            </div>
            <div class="col-md-6">
                <h5>Workflow History</h5>
                @if($history->count() > 0)
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $record)
                        <tr>
                            <td>{{ $record->Action }}</td>
                            <td>{{ $record->UserName }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->ActionDate)->format('d/m/Y H:i') }}</td>
                            <td>{{ $record->Notes }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-muted">No history available.</p>
                @endif
            </div>
        </div>

        {{-- Supplier Quotations will be included in the printable section (only visible after approval) --}}
        @if(in_array($rfq->Status, ['Approved', 'Ap', 'AP']) && isset($rfqResponses) && $rfqResponses->isNotEmpty())
        <hr>
        <h4>Supplier Quotations</h4>
        <div id="supplierQuotationsPrint">
            @foreach($rfqResponses as $response)
            <section class="supplier-quotation mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-6">
                                <h5>
                                    Supplier: {{ optional($response->supplier->thirdParty)->TradingName ?? $response->SupplierName ?? 'N/A' }}</h5>
                                <p class="mb-0">Response No:
                                    <strong>{{ $response->RFQResponseNumber }}</strong>
                                </p>
                                <p class="mb-0">Submitted:
                                    <strong>{{ optional($response->CreatedOn)->format('d/m/Y') ?? '' }}</strong>
                                </p>
                            </div>
                            <div class="col-6 text-end">
                                <p class="mb-0">RFQ: <strong>{{ $rfq->RFQNumber }}</strong></p>
                                <p class="mb-0">Requisition:
                                    <strong>{{ optional($rfq->requisition)->RequisitionNo }}</strong>
                                </p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th>Qty</th>
                                        <th>UOM</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sum = 0; @endphp
                                    @foreach($response->items as $ri)
                                    @php $sum += floatval($ri->TotalPayable ?? ($ri->QuotedPrice * $ri->Quantity)); @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $ri->ItemName }}</td>
                                        <td>{{ $ri->Description ?? '' }}</td>
                                        <td class="text-end">{{ $ri->Quantity }}</td>
                                        <td>{{ optional($ri->uom)->Name ?? $ri->UOM }}</td>
                                        <td class="text-end">{{ number_format($ri->QuotedPrice, 2) }}</td>
                                        <td class="text-end">{{ number_format($ri->TotalPayable ?? ($ri->QuotedPrice * $ri->Quantity), 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Subtotal</strong></td>
                                        <td class="text-end"><strong>{{ number_format($sum, 2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end">Tax / VAT (if any)</td>
                                        <td class="text-end">{{ number_format($response->TaxAmount ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Total Payable</strong></td>
                                        <td class="text-end">
                                            <strong>{{ number_format($response->TotalPayable ?? $sum, 2) }}</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <p class="mb-1"><strong>Delivery Time
                                        (days):</strong> {{ $response->DurationDays ?? 'N/A' }}</p>
                                <p class="mb-1">
                                    <strong>Currency:</strong> {{ $response->Currency ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-6 text-end">
                                <p class="mb-1">Authorized Signature: ________________________</p>
                                <p class="mb-1">Name: ________________________</p>
                                <p class="mb-1">Date: ________________________</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endforeach
        </div>
        @endif
    </div>
    <div class="text-end">
        @if (in_array($rfq->Status, ['Approved', 'Ap', 'AP', 'a', 'A']))
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#publishModal">Publish to Suppliers</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="printRFQ()">Print</button>

        @endif
    </div>
    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('rfqs.reject', $rfq->Id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject RFQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="RejectionReason" class="form-label">Reason for Rejection</label>
                            <textarea name="RejectionReason" id="RejectionReason" class="form-control" rows="3"
                                required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Publish Modal -->
    <div class="modal fade" id="publishModal" tabindex="-1" aria-labelledby="publishModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="supplierSelectionForm" action="{{ route('rfqs.publish', $rfq->Id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="publishModalLabel">Select Suppliers to Publish To</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="suppliers" class="form-label">Suppliers</label>
                            <select name="suppliers[]" id="suppliers" class="form-control" multiple>
                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->Id }}">{{ $supplier->SupplierName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Send Emails & Publish</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createRFQModal" tabindex="-1" aria-labelledby="createRFQModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('linecategories.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createRFQModalLabel">Create RFQ Line</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Requisition No (readonly) -->
                <div class="mb-3">
                    <label for="requisitionNo">Requisition No</label>
                    <input type="text" class="form-control" id="requisitionNo"
                        value="{{ $rfq->requisition->RequisitionNo ?? 'N/A' }}" readonly>
                </div>

                <!-- Item Category Dropdown -->
                <div class="mb-3">
                    <label for="categoryDropdown">Item Category</label>
                    <select name="ItemCategoryId" id="categoryDropdown" class="form-control" required>
                        <option value="">-- Select Category --</option>
                    </select>
                </div>
            </div>

            <input type="hidden" name="RFQId" id="rfq-number" value="{{ $rfq->Id }}">
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save RFQ Line</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryDropdown = document.getElementById('categoryDropdown');
        const requisitionId = {
            {
                $rfq - > requisition - > Id
            }
        };

        categoryDropdown.innerHTML = '<option value="">-- Select Category --</option>';

        fetch(`/procurement/requisition/${requisitionId}/categories`)
            .then(response => response.json())
            .then(payload => {
                const categories = Array.isArray(payload) ?
                    payload :
                    (payload && Array.isArray(payload.categories) ? payload.categories : []);

                if (!categories.length) {
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "⚠️ No items available for the attached requisition.";
                    categoryDropdown.appendChild(option);
                } else {
                    categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.Id;
                        option.textContent = cat.Name;
                        categoryDropdown.appendChild(option);
                    });
                }
                categoryDropdown.disabled = false;
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                categoryDropdown.innerHTML = '<option value="">⚠️ Failed to load categories</option>';
                categoryDropdown.disabled = true;
            });
    });
</script>


<script>
    function printRFQ() {
        const printContainer = document.getElementById('supplierQuotationsPrint');
        if (!printContainer) {
            alert('No supplier quotations available to print. Please approve the RFQ and ensure suppliers have submitted quotations.');
            return;
        }
        const content = printContainer.innerHTML;
        const printWindow = window.open('', '_blank', 'height=800,width=1000');

        // Build printable HTML
        const html = `
                <!doctype html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width,initial-scale=1">
                    <title>Quotation - ${document.title}</title>
                    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
                    <style>
                        body { font-family: Arial, Helvetica, sans-serif; color: #000; padding: 20px; }
                        h1,h2,h3,h4 { margin: 0 0 10px 0 }
                        .company, .meta { display: inline-block; vertical-align: top }
                        .meta { float: right; text-align: right }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px }
                        table, th, td { border: 1px solid #333 }
                        th, td { padding: 8px; font-size: 12px }
                        .text-end { text-align: right }
                        .no-border { border: none }
                        @media print {
                            body { padding: 0 }
                            .page-break { page-break-after: always }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-quotation">
                        <header>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                <div class="company">
                                    <h2>{{ config('app.name') }}</h2>
                                    <div>Address: {{ config('app.address', '') }}</div>
                                </div>
                                <div class="meta">
                                    <div><strong>RFQ:</strong> ${escapeHtml('{{ $rfq->RFQNumber }}')}</div>
                                    <div><strong>Date:</strong> ${escapeHtml(new Date().toLocaleDateString())}</div>
                                </div>
                            </div>
                        </header>

                        <section>
                            ${content}
                        </section>
                    </div>
                </body>
                </html>
            `;

        // Write and wait for load
        printWindow.document.open();
        printWindow.document.write(html);
        printWindow.document.close();

        // Ensure the window finishes rendering before printing
        const tryPrint = () => {
            try {
                // Use onafterprint to close the window when print dialog finishes
                printWindow.onafterprint = function() {
                    try {
                        printWindow.close();
                    } catch (e) {
                        /* ignore */
                    }
                };

                // Focus then print
                printWindow.focus();
                // Some browsers require a short delay
                setTimeout(() => {
                    printWindow.print();
                }, 250);

                // As a safety, close after 20s in case onafterprint isn't fired
                setTimeout(() => {
                    try {
                        printWindow.close();
                    } catch (e) {
                        /* ignore */
                    }
                }, 20000);
            } catch (err) {
                console.error('Print error', err);
                try {
                    printWindow.close();
                } catch (e) {
                    /* ignore */
                }
            }
        };

        // Small helper to escape text when injecting into template literals
        function escapeHtml(text) {
            if (typeof text !== 'string') return text;
            return text.replace(/[&<>"']/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [c];
            });
        }

        // Wait until the new window document is ready
        const maxWait = 5000;
        const start = Date.now();
        const checkReady = () => {
            try {
                if (printWindow.document && printWindow.document.readyState === 'complete') {
                    tryPrint();
                    return;
                }
            } catch (e) {
                // Access denied if popup blocked
            }
            if (Date.now() - start < maxWait) {
                setTimeout(checkReady, 100);
            } else {
                // Fallback: attempt to print anyway
                tryPrint();
            }
        };
        checkReady();
    }
</script>
<script>
    // Prevent aria-hidden focus conflict when hiding modals (e.g., close button focused)
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('hide.bs.modal', function(event) {
            const modal = event.target;
            if (modal && modal.classList.contains('modal')) {
                const active = document.activeElement;
                if (active && modal.contains(active) && typeof active.blur === 'function') {
                    active.blur();
                }
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const approveForm = document.getElementById('supplierSelectionForm');
        const suppliersSelect = document.getElementById('suppliers');

        approveForm.addEventListener('submit', function(e) {
            const selected = Array.from(suppliersSelect.options).filter(option => option.selected);

            if (selected.length === 0) {
                e.preventDefault();
                alert('Please select at least one supplier before approving.');
            }
        });
    });
</script>

@endsection