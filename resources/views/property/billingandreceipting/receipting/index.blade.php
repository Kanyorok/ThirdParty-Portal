@extends('layouts.app')

@section('title', 'Rent Invoice Receipts')

@section('content')
<div class="container-fluid mt-4">

    {{-- ================= FILTERS ================= --}}
    <form method="GET" class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Invoice Number</label>
                    <input type="text"
                           name="invoice_number"
                           value="{{ request('invoice_number') }}"
                           class="form-control"
                           placeholder="Invoice Number">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Lease</label>
                    <input type="text"
                           name="lease"
                           value="{{ request('lease') }}"
                           class="form-control"
                           placeholder="Lease">
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary">Filter</button>

                    <a href="{{ route(request()->route()->getName()) }}"
                       class="btn btn-secondary">Reset</a>

                    <button type="button"
                            onclick="printPage()"
                            class="btn btn-outline-dark">
                        🖨️ Print
                    </button>
                </div>

            </div>
        </div>
    </form>

    {{-- ================= TABLE ================= --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Rent Invoice Receipts</h5>
        </div>

        <div class="card-body">

            @php
                $groupedReceipts = $receipts->groupBy('InvoiceNumber');
            @endphp

            @if ($groupedReceipts->isEmpty())
                <div class="alert alert-info">
                    No posted receipts found.
                </div>
            @else
            <div class="table-responsive" id="print-area">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>Invoice Number</th>
                            <th>Lease</th>
                            <th>Invoice Amount</th>
                            <th>Total Received</th>
                            <th>Balance</th>
                            <th>Print</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach ($groupedReceipts as $invoiceNumber => $items)

                        @php
                            $invoiceAmount  = $items->first()->InvoiceAmount ?? 0;
                            $totalReceived = $items->sum('AmountReceived');
                            $balance       = $invoiceAmount - $totalReceived;
                            $collapseId    = 'inv_' . md5($invoiceNumber);
                        @endphp

                        {{-- ================= INVOICE ROW ================= --}}
                        <tr class="table-primary">
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-dark"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#{{ $collapseId }}">
                                    +
                                </button>
                            </td>

                            <td class="fw-semibold">
                                {{ $invoiceNumber }}
                            </td>

                            <td>
                                {{ $items->first()->Lease }}
                            </td>

                            <td class="text-end">
                                {{ number_format($invoiceAmount, 2) }}
                            </td>

                            <td class="text-end text-success fw-semibold">
                                {{ number_format($totalReceived, 2) }}
                            </td>

                            <td class="text-end fw-semibold">
                                {{ number_format($balance, 2) }}
                            </td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-secondary"
                                        onclick="printInvoice('{{ $collapseId }}')">
                                    🖨️
                                </button>
                            </td>
                        </tr>

                        {{-- ================= RECEIPTS ================= --}}
                        <tr class="collapse bg-light" id="{{ $collapseId }}">
                            <td colspan="7" class="p-0">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Receipt Number</th>
                                            <th>Amount Received</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $i => $receipt)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>
                                                    <span class="badge bg-info text-dark">
                                                        {{ $receipt->ReceiptNumber }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($receipt->AmountReceived, 2) }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        Posted
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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

