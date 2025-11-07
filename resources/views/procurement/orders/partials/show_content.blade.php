@php use Carbon\Carbon; @endphp

<style>
    .print-area .card { padding: 12px; }
    .print-area .totals .label, .print-area .totals .amount { font-weight: 700; }

    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body * {
            visibility: hidden !important;
        }

        .print-area, .print-area * {
            visibility: visible !important;
        }

        .print-area {
            position: static !important;
            width: calc(297mm - 20mm) !important;
            min-height: calc(210mm - 20mm) !important;
            margin: 0 auto !important;
            background: #fff !important;
            padding: 8mm !important;
            box-shadow: none !important;
            font-size: 10pt !important;
            color: #000 !important;
            box-sizing: border-box !important;
        }

        #printOrderBtn, .modal-header, .modal-footer, .modal-backdrop,
        nav, footer, .navbar, .sidebar, .alert, .pagination {
            display: none !important;
        }

        .modal, .modal-dialog {
            position: static !important;
            transform: none !important;
            box-shadow: none !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9pt !important;
        }

        table th, table td {
            border: 1px solid #000 !important;
            padding: 4px !important;
            text-align: left !important;
            vertical-align: top !important;
        }

        h4, h5 {
            font-size: 11pt !important;
            margin-bottom: 8px !important;
            text-transform: uppercase !important;
        }

        .card {
            border: 1px solid #ccc !important;
            padding: 10px !important;
            margin-bottom: 10px !important;
            border-radius: 6px !important;
        }

        .label { font-weight: bold !important; }
    }
</style>

<div class="container-fluid print-area">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Local Purchase Order</h4>
        <button type="button" class="btn btn-info" id="printOrderBtn">
            <i class="fa fa-print"></i> Print
        </button>
    </div>

    <div class="card">
        <h5>Supplier Details</h5>
        <div class="row">
            <div class="col-md-6 mb-2">
                <span class="label">Supplier:</span> {{ $orderInfo->SupplierName ?? 'N/A' }}
            </div>
            <div class="col-md-6 mb-2">
                <span class="label">Priority:</span> {{ $orderInfo->Priority ?? 'N/A' }}
            </div>
            <div class="col-md-6 mb-2">
                <span class="label">Payment Terms:</span> {{ $orderInfo->terms_description ?? 'N/A' }}
                @if(!$orderInfo->terms_description && $orderInfo->terms_id)
                    <small class="text-danger">Payment term ID {{ $orderInfo->terms_id }} missing in t_CodeDetails.</small>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <h5>Order Information</h5>
        <div class="row">
            <div class="col-md-4 mb-2">
                <span class="label">LPO Number:</span> {{ $orderInfo->OrderNo ?? 'N/A' }}
            </div>
            <div class="col-md-4 mb-2">
                <span class="label">Date:</span>
                {{ isset($orderInfo->OrderDate) ? Carbon::parse($orderInfo->OrderDate)->format('Y-m-d') : 'N/A' }}
            </div>
            <div class="col-md-4 mb-2">
                <span class="label">Reference Number:</span> {{ $orderInfo->ExtOrdNum ?? 'N/A' }}
            </div>
        </div>
    </div>

    <div class="card">
        <h5>Line Items</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Item Type</th>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Tax</th>
                        <th>Discount</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lineInfo as $index => $line)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $line->ItemType }}</td>
                            <td>{{ $line->ItemName }}</td>
                            <td>{{ $line->Description }}</td>
                            <td>{{ $line->fQuantity }}</td>
                            <td>{{ number_format($line->fUnitPriceExcl, 2) }}</td>
                            <td>{{ number_format($line->fTaxRate, 2) }}</td>
                            <td>{{ number_format($line->fLineDiscount, 2) }}</td>
                            <td>{{ number_format($line->LineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h5>Remarks</h5>
        <p>{{ $orderInfo->Notes ?? 'No additional remarks.' }}</p>
    </div>

    <div class="card">
        <h5>Totals</h5>
        <div class="row">
            <div class="col-md-4 offset-md-8 totals">
                <div class="d-flex justify-content-between">
                    <span class="label">Exclusive Total:</span>
                    <span class="amount">{{ number_format($orderInfo->OrdTotExcl ?? 0, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="label">Tax Amount:</span>
                    <span class="amount">{{ number_format($orderInfo->OrdTotTax ?? 0, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="label">Inclusive Total:</span>
                    <span class="amount">{{ number_format($orderInfo->OrdTotIncl ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
        (function(){
            const btn = document.getElementById('printOrderBtn');
            if(!btn) return;

            btn.addEventListener('click', function(){
                const area = document.querySelector('.print-area');
                if(!area) { window.print(); return; }

                const prevDisabled = btn.disabled;
                btn.disabled = true;

                // hide the print button (so it doesn't appear in the screenshot)
                const prevDisplay = btn.style.display;
                btn.style.display = 'none';

                // capture at higher scale for better quality
                html2canvas(area, { scale: 2, useCORS: true, backgroundColor: '#ffffff' })
                    .then(canvas => {
                        const dataURL = canvas.toDataURL('image/png');

                        // create an iframe in the current page to print without opening a new tab
                        const iframe = document.createElement('iframe');
                        iframe.style.position = 'fixed';
                        iframe.style.right = '0';
                        iframe.style.bottom = '0';
                        iframe.style.width = '297mm';
                        iframe.style.height = '210mm';
                        iframe.style.border = '0';
                        iframe.style.visibility = 'hidden';
                        document.body.appendChild(iframe);

                        const idoc = iframe.contentDocument || iframe.contentWindow.document;
                        const html = `<!doctype html><html><head><meta charset="utf-8"><title>Print LPO</title>
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <style>
                                @page { size: A4 landscape; margin: 0; }
                                html, body { margin: 0; padding: 0; height: 100%; }
                                .page { width: 297mm; height: 210mm; overflow: hidden; }
                                .page img { display: block; width: 100%; height: 100%; object-fit: contain; }
                                body { -webkit-print-color-adjust: exact; }
                                /* avoid any page breaks */
                                * { box-sizing: border-box; }
                            </style>
                        </head><body>
                            <div class="page"><img src="${dataURL}" alt="Purchase Order Screenshot"/></div>
                            <script>
                                window.onload = function(){
                                    try {
                                        // print from iframe
                                        window.focus();
                                        setTimeout(function(){ parent.focus(); parent.document.body.style.pointerEvents = 'none'; }, 50);
                                    } catch(e){}
                                };
                            <\/script>
                        </body></html>`;

                        idoc.open();
                        idoc.write(html);
                        idoc.close();

                        // wait a moment for iframe to render then call print on its window
                        setTimeout(function(){
                            try {
                                iframe.contentWindow.focus();
                                iframe.contentWindow.print();
                            } catch (e) {
                                console.error('Iframe print failed, falling back to window.print()', e);
                                window.print();
                            }

                            // cleanup iframe after some time
                            setTimeout(function(){
                                try { document.body.removeChild(iframe); } catch(e){}
                            }, 1500);
                        }, 500);
                    })
                    .catch(err => {
                        console.error('Screenshot print failed', err);
                        window.print();
                    })
                    .finally(() => {
                        // restore button visibility and state
                        btn.style.display = prevDisplay || '';
                        btn.disabled = prevDisabled;
                    });
            });
        })();
    </script>
</div>
