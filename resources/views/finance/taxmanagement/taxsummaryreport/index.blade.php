@extends('layouts.app')
@section('title', 'Tax Summary Report')

@section('content')
    <div class="container my-3">

        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="mb-0">
                <i class="fas fa-file-invoice-dollar text-info me-2"></i> Tax Summary Report
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary" id="btnPrint">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button class="btn btn-outline-secondary" id="btnExport">
                    <i class="fas fa-file-export me-1"></i> Export CSV
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body">
                <form class="row g-3 align-items-end" action="#" method="get">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Tax Type</label>
                        <select class="form-select" name="taxType">
                            <option value="">All</option>
                            <option>VAT</option>
                            <option>WHT</option>
                            <option>GST</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Period</label>
                        <input type="month" class="form-control" name="period" value="{{ date('Y-m') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Jurisdiction</label>
                        <select class="form-select" name="jurisdiction">
                            <option value="">All</option>
                            <option>Kenya</option>
                            <option>Uganda</option>
                            <option>Tanzania</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Branch</label>
                        <select class="form-select" name="branch">
                            <option value="">All</option>
                            <option>Head Office</option>
                            <option>Westlands</option>
                            <option>Mombasa</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-play me-1"></i> Generate
                        </button>
                        <button type="reset" class="btn btn-light border">Clear</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick tax filter pills -->
        <ul class="nav nav-pills mb-3" id="taxPills" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pill-all" type="button" role="tab">All</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pill-vat" type="button" role="tab">VAT</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pill-wht" type="button" role="tab">WHT</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pill-gst" type="button" role="tab">GST</button>
            </li>
        </ul>

        <!-- Summary tiles -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="small text-muted">Taxable Amount (All)</div>
                        <div class="h5 fw-semibold mb-0" id="sumTaxable">KSh —</div>
                        <div class="small text-muted">Period <span id="sumPeriod">{{ date('Y-m') }}</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="small text-muted">Tax Amount (All)</div>
                        <div class="h5 fw-semibold mb-0" id="sumTax">KSh —</div>
                        <div class="small text-muted">All jurisdictions</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="small text-muted">Filing Status</div>
                        <div class="h5 fw-semibold mb-0" id="sumStatus">—</div>
                        <div class="small text-muted">Pending / Filed / Overdue</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div id="printRoot" class="card shadow-sm border-0 rounded-3">
            <div class="card-body">
                <div class="tab-content">

                    <!-- All -->
                    <div class="tab-pane fade show active" id="pill-all" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle" id="tblAll">
                                <thead class="table-light">
                                <tr>
                                    <th>Tax Type</th>
                                    <th>Jurisdiction</th>
                                    <th class="text-end">Taxable Amount</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Dummy rows -->
                                <tr>
                                    <td><span class="badge bg-primary-subtle text-primary border">VAT</span></td>
                                    <td>Kenya</td>
                                    <td class="text-end taxable" data-amt="1250000">1,250,000.00</td>
                                    <td class="text-end tax" data-amt="200000">200,000.00</td>
                                    <td><span class="badge bg-warning text-dark">Pending Filing</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-teal text-dark border">WHT</span></td>
                                    <td>Kenya</td>
                                    <td class="text-end taxable" data-amt="450000">450,000.00</td>
                                    <td class="text-end tax" data-amt="22500">22,500.00</td>
                                    <td><span class="badge bg-success">Filed</span></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">GST</span></td>
                                    <td>Uganda</td>
                                    <td class="text-end taxable" data-amt="300000">300,000.00</td>
                                    <td class="text-end tax" data-amt="48000">48,000.00</td>
                                    <td><span class="badge bg-danger">Overdue</span></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="table-light fw-semibold">
                                    <td colspan="2" class="text-end">Totals</td>
                                    <td class="text-end" id="tAllTaxable">—</td>
                                    <td class="text-end" id="tAllTax">—</td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- VAT -->
                    <div class="tab-pane fade" id="pill-vat" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle" id="tblVAT">
                                <thead class="table-light">
                                <tr>
                                    <th>Jurisdiction</th>
                                    <th class="text-end">Taxable Amount</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Kenya</td>
                                    <td class="text-end taxable" data-amt="1250000">1,250,000.00</td>
                                    <td class="text-end tax" data-amt="200000">200,000.00</td>
                                    <td><span class="badge bg-warning text-dark">Pending Filing</span></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="table-light fw-semibold">
                                    <td class="text-end">Totals</td>
                                    <td class="text-end" id="tVATTaxable">—</td>
                                    <td class="text-end" id="tVATTax">—</td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- WHT -->
                    <div class="tab-pane fade" id="pill-wht" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle" id="tblWHT">
                                <thead class="table-light">
                                <tr>
                                    <th>Jurisdiction</th>
                                    <th class="text-end">Taxable Amount</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Kenya</td>
                                    <td class="text-end taxable" data-amt="450000">450,000.00</td>
                                    <td class="text-end tax" data-amt="22500">22,500.00</td>
                                    <td><span class="badge bg-success">Filed</span></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="table-light fw-semibold">
                                    <td class="text-end">Totals</td>
                                    <td class="text-end" id="tWHTTaxable">—</td>
                                    <td class="text-end" id="tWHTTax">—</td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- GST -->
                    <div class="tab-pane fade" id="pill-gst" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle" id="tblGST">
                                <thead class="table-light">
                                <tr>
                                    <th>Jurisdiction</th>
                                    <th class="text-end">Taxable Amount</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Uganda</td>
                                    <td class="text-end taxable" data-amt="300000">300,000.00</td>
                                    <td class="text-end tax" data-amt="48000">48,000.00</td>
                                    <td><span class="badge bg-danger">Overdue</span></td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr class="table-light fw-semibold">
                                    <td class="text-end">Totals</td>
                                    <td class="text-end" id="tGSTTaxable">—</td>
                                    <td class="text-end" id="tGSTTax">—</td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection

@section('styles')
    <style>
        :root { --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif; }
        body, .card, .table { font-family: var(--font-sans); }
        .card { border: none; }
        .table-hover tbody tr:hover { background-color: #f8f9fa; transition: background-color .2s; }
        .badge.bg-primary-subtle { background-color: #eef2ff; }
        .badge.bg-teal { background-color: #d1fae5; }
        .rounded-3 { border-radius: .75rem!important; }
        /* Print */
        @media print {
            body * { visibility: hidden; }
            #printRoot, #printRoot * { visibility: visible; }
            #printRoot { position: absolute; left: 0; top: 0; width: 100%; }
            @page { size: A4 portrait; margin: 14mm; }
            .navbar, .btn, .modal, .nav, form { display: none !important; }
        }
    </style>
@endsection

@section('scripts')
    <script>
        // minimal helpers
        const fmt = n => Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
        function sum(sel){ let s=0; document.querySelectorAll(sel).forEach(td=>s+=Number(td.dataset.amt||0)); return s; }

        // fill totals & tiles from "All" tab
        (function(){
            const tTaxable = sum('#pill-all .taxable');
            const tTax     = sum('#pill-all .tax');
            document.getElementById('tAllTaxable').textContent = fmt(tTaxable);
            document.getElementById('tAllTax').textContent     = fmt(tTax);

            // per pill totals
            document.getElementById('tVATTaxable').textContent = fmt(sum('#pill-vat .taxable'));
            document.getElementById('tVATTax').textContent     = fmt(sum('#pill-vat .tax'));

            document.getElementById('tWHTTaxable').textContent = fmt(sum('#pill-wht .taxable'));
            document.getElementById('tWHTTax').textContent     = fmt(sum('#pill-wht .tax'));

            document.getElementById('tGSTTaxable').textContent = fmt(sum('#pill-gst .taxable'));
            document.getElementById('tGSTTax').textContent     = fmt(sum('#pill-gst .tax'));

            // tiles
            document.getElementById('sumTaxable').textContent = 'KSh ' + fmt(tTaxable);
            document.getElementById('sumTax').textContent     = 'KSh ' + fmt(tTax);
            // simple status logic
            const overdue = document.querySelectorAll('#pill-all .badge.bg-danger').length;
            const pending = document.querySelectorAll('#pill-all .badge.bg-warning').length;
            const filed   = document.querySelectorAll('#pill-all .badge.bg-success').length;
            document.getElementById('sumStatus').textContent = `${filed} Filed • ${pending} Pending • ${overdue} Overdue`;

            // actions
            document.getElementById('btnPrint').onclick = () => window.print();
            document.getElementById('btnExport').onclick = () => {
                const table = document.querySelector('#pill-all table');
                let csv = [];
                for (const row of table.rows) {
                    const cells = Array.from(row.cells).map(td => {
                        let t = (td.innerText||'').trim().replace(/\s+/g,' ');
                        if (/[",\n]/.test(t)) t = `"${t.replace(/"/g,'""')}"`;
                        return t;
                    });
                    csv.push(cells.join(','));
                }
                const blob = new Blob([csv.join('\n')], {type:'text/csv;charset=utf-8;'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url; a.download = 'tax_summary.csv'; a.click();
                URL.revokeObjectURL(url);
            };
        })();
    </script>
@endsection
