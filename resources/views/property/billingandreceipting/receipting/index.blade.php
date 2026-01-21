@extends('layouts.app')

@section('title', 'Rent Invoice Receipts')

@section('content')
<div class="container-fluid mt-4">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1 fw-bold text-dark">
                <i class="bi bi-receipt-cutoff me-2"></i>Rent Invoice Receipts
            </h3>
            <p class="text-muted mb-0">View and manage all rent invoice receipts</p>
        </div>
        <button type="button" onclick="printPage()" class="btn btn-outline-primary">
            <i class="bi bi-printer me-2"></i>Print All
        </button>
    </div>

    {{-- ================= FILTERS ================= --}}
    <form method="GET" class="card shadow-sm mb-4 border-0">
        <div class="card-body p-4">
            <div class="row g-3">

                <div class="col-12 col-md-6 col-lg-5">
                    <label class="form-label fw-semibold text-secondary mb-2">
                        <i class="bi bi-file-earmark-text me-1"></i>Invoice Number
                    </label>
                    <input type="text"
                           name="invoice_number"
                           value="{{ request('invoice_number') }}"
                           class="form-control"
                           placeholder="Enter invoice number...">
                </div>

                <div class="col-12 col-md-6 col-lg-5">
                    <label class="form-label fw-semibold text-secondary mb-2">
                        <i class="bi bi-building me-1"></i>Lease
                    </label>
                    <input type="text"
                           name="lease"
                           value="{{ request('lease') }}"
                           class="form-control"
                           placeholder="Enter lease reference...">
                </div>

                <div class="col-12 col-lg-2 d-flex flex-column flex-sm-row gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary w-100 w-sm-auto flex-sm-fill">
                        <i class="bi bi-funnel me-1"></i><span class="d-none d-sm-inline">Filter</span><span class="d-sm-none">Apply Filters</span>
                    </button>

                    <a href="{{ route(request()->route()->getName()) }}"
                       class="btn btn-outline-secondary w-100 w-sm-auto"
                       title="Reset filters">
                        <i class="bi bi-arrow-clockwise me-1 d-sm-none"></i><span class="d-sm-none">Reset</span><i class="bi bi-arrow-clockwise d-none d-sm-inline"></i>
                    </a>
                </div>

            </div>
        </div>
    </form>

    {{-- ================= TABLE ================= --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h5 class="mb-0 text-white fw-semibold">
                <i class="bi bi-table me-2"></i>Receipt Records
            </h5>
        </div>

        <div class="card-body p-0">

            @php
                $groupedReceipts = $receipts->groupBy('InvoiceNumber');
            @endphp

            @if ($groupedReceipts->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">No Posted Receipts Found</h5>
                    <p class="text-secondary">Try adjusting your filters or check back later.</p>
                </div>
            @else
            <div class="table-responsive" id="print-area">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f8f9fa; position: sticky; top: 0;">
                        <tr>
                            <th class="border-0" style="width: 60px;"></th>
                            <th class="border-0 fw-semibold">Invoice Number</th>
                            <th class="border-0 fw-semibold">Lease</th>
                            <th class="border-0 fw-semibold text-end">Invoice Amount</th>
                            <th class="border-0 fw-semibold text-end">Total Received</th>
                            <th class="border-0 fw-semibold text-end">Balance</th>
                            <th class="border-0 text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach ($groupedReceipts as $invoiceNumber => $items)

                        @php
                            $invoiceAmount  = $items->first()->InvoiceAmount ?? 0;
                            $totalReceived = $items->sum('AmountReceived');
                            $balance       = $invoiceAmount - $totalReceived;
                            $collapseId    = 'inv_' . md5($invoiceNumber);
                            $isPaid        = $balance <= 0;
                        @endphp

                        {{-- ================= INVOICE ROW ================= --}}
                        <tr class="{{ $isPaid ? 'table-success' : 'table-warning' }}" style="border-left: 4px solid {{ $isPaid ? '#28a745' : '#ffc107' }};">
                            <td class="text-center">
                                <button class="btn btn-sm btn-light rounded-circle shadow-sm"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#{{ $collapseId }}"
                                        style="width: 32px; height: 32px;">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-file-earmark-text-fill text-primary me-2 fs-5"></i>
                                    <span class="fw-bold">{{ $invoiceNumber }}</span>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-secondary bg-opacity-25 text-dark px-3 py-2">
                                    {{ $items->first()->Lease }}
                                </span>
                            </td>

                            <td class="text-end fw-semibold">
                                <span class="text-muted">KES</span> {{ number_format($invoiceAmount, 2) }}
                            </td>

                            <td class="text-end">
                                <span class="badge bg-success px-3 py-2">
                                    <i class="bi bi-cash-coin me-1"></i>KES {{ number_format($totalReceived, 2) }}
                                </span>
                            </td>

                            <td class="text-end fw-bold {{ $isPaid ? 'text-success' : 'text-danger' }}">
                                KES {{ number_format($balance, 2) }}
                            </td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="printInvoice('{{ $collapseId }}')"
                                        title="Print receipts">
                                    <i class="bi bi-printer"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- ================= RECEIPTS ================= --}}
                        <tr class="collapse" id="{{ $collapseId }}">
                            <td colspan="7" class="p-0 bg-light">
                                <div class="p-3">
                                    <h6 class="text-uppercase text-muted mb-3 fw-semibold">
                                        <i class="bi bi-receipt me-2"></i>Receipt Details
                                    </h6>
                                    <table class="table table-sm table-bordered bg-white shadow-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>Receipt Number</th>
                                                <th class="text-end">Amount Received</th>
                                                <th class="text-center" style="width: 120px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $i => $receipt)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                                                    <td>
                                                        <span class="badge bg-info px-3 py-2">
                                                            <i class="bi bi-hash me-1"></i>{{ $receipt->ReceiptNumber }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end fw-semibold">
                                                        KES {{ number_format($receipt->AmountReceived, 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Posted
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>

                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function printPage() {
    window.print();
}

function printInvoice(collapseId) {
    const row = document.getElementById(collapseId);
    const invoiceNumber = row.closest('tr').previousElementSibling.querySelector('td.fw-semibold').innerText;
    const lease = row.closest('tr').previousElementSibling.querySelectorAll('td')[2].innerText;

    const content = row.innerHTML;
    const win = window.open('', '', 'width=900,height=700');

    win.document.write(`
        <html>
            <head>
                <title>Invoice Receipts - ${invoiceNumber}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; color: #000; }
                    h2, h4 { margin: 0; padding: 0; }
                    .header { margin-bottom: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                    th { background-color: #f0f0f0; }
                    .text-end { text-align: right; }
                    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; }
                    .bg-info { background-color: #17a2b8; color: #fff; }
                    .bg-success { background-color: #28a745; color: #fff; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>Rent Invoice Receipts</h2>
                    <h4>Invoice Number: ${invoiceNumber}</h4>
                    <h4>Lease: ${lease}</h4>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Receipt Number</th>
                            <th>Amount Received</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${content}
                    </tbody>
                </table>
            </body>
        </html>
    `);

    win.document.close();
    win.focus();
    win.print();
    win.close();
}
</script>
@endpush

