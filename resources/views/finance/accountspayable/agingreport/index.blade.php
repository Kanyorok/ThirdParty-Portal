@extends('layouts.app')
@section('title', 'Aging Report — Accounts Payable')

@section('content')
    <div class="container my-3">
        <!-- Header actions -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt text-info me-2"></i> Accounts Payable Aging Report
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                    <i class="fas fa-plus me-1"></i> Generate Report
                </button>
                <button class="btn btn-outline-primary" id="btnPrint">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button class="btn btn-outline-secondary" id="btnExport">
                    <i class="fas fa-file-export me-1"></i> Export CSV
                </button>
            </div>
        </div>

        <!-- Summary tiles -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="small text-muted">Total Outstanding</div>
                        <div class="h5 fw-semibold mb-0" id="sumTotal">KSh —</div>
                        <div class="small text-muted">As of <span id="asOfDate">2025-08-15</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="small text-muted">> 90 Days</div>
                        <div class="h5 fw-semibold mb-0 text-danger" id="sum90p">KSh —</div>
                        <div class="small text-muted">High risk bucket</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="small text-muted">0–30 Days</div>
                        <div class="h5 fw-semibold mb-0" id="sum030">KSh —</div>
                        <div class="small text-muted">Current</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body">
                        <div class="small text-muted">31–90 Days</div>
                        <div class="h5 fw-semibold mb-0" id="sum31to90">KSh —</div>
                        <div class="small text-muted">Near overdue</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report table -->
        <div id="printRoot" class="card shadow-sm rounded-3 border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="agingTable">
                        <thead class="table-light">
                        <tr class="text-center">
                            <th>#</th>
                            <th class="text-start">Supplier</th>
                            <th>Report Date</th>
                            <th class="text-end">0–30 Days</th>
                            <th class="text-end">31–60 Days</th>
                            <th class="text-end">61–90 Days</th>
                            <th class="text-end">90+ Days</th>
                            <th class="text-end">Total Outstanding</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- Demo rows -->
                        <tr>
                            <td class="text-center">1</td>
                            <td class="text-start">
                                <div class="fw-semibold">ABC Supplies Ltd</div>
                                <div class="small text-muted">info@abc.co.ke • +254 700 111 222</div>
                            </td>
                            <td class="text-center">2025-08-15</td>
                            <td class="text-end amt" data-bucket="030" data-amt="20000">20,000</td>
                            <td class="text-end amt" data-bucket="3160" data-amt="8000">8,000</td>
                            <td class="text-end amt" data-bucket="6190" data-amt="5000">5,000</td>
                            <td class="text-end amt text-danger" data-bucket="90p" data-amt="12000">12,000</td>
                            <td class="text-end amt-row-total" data-amt="45000">45,000</td>
                            <td class="text-center">
                                <a href="{{ route('agingreport.show', 1) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td class="text-start">
                                <div class="fw-semibold">Global Equipment</div>
                                <div class="small text-muted">sales@global.com • +254 711 333 444</div>
                            </td>
                            <td class="text-center">2025-08-15</td>
                            <td class="text-end amt" data-bucket="030" data-amt="15000">15,000</td>
                            <td class="text-end amt" data-bucket="3160" data-amt="10500">10,500</td>
                            <td class="text-end amt" data-bucket="6190" data-amt="4500">4,500</td>
                            <td class="text-end amt text-danger" data-bucket="90p" data-amt="3000">3,000</td>
                            <td class="text-end amt-row-total" data-amt="33000">33,000</td>
                            <td class="text-center">
                                <a href="{{ route('agingreport.show', 2) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr class="table-light fw-semibold">
                            <td colspan="3" class="text-end">Totals</td>
                            <td class="text-end" id="t030">—</td>
                            <td class="text-end" id="t3160">—</td>
                            <td class="text-end" id="t6190">—</td>
                            <td class="text-end text-danger" id="t90p">—</td>
                            <td class="text-end" id="tTotal">—</td>
                            <td></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="small text-muted">
                    <strong>Legend:</strong> 0–30 = current, 31–60/61–90 = overdue, 90+ = critical supplier payments
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Generate Report -->
    <div class="modal fade" id="generateReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="fas fa-cogs me-2"></i> Generate Payables Aging Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('agingreport.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Report Date</label>
                            <input type="date" class="form-control" name="reportDate" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Supplier (optional)</label>
                            <input type="text" class="form-control" name="supplier" placeholder="Search supplier...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Branch (optional)</label>
                            <select class="form-select" name="branch">
                                <option value="">All</option>
                                <option value="HQ">HQ</option>
                                <option value="Branch A">Branch A</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select class="form-select" name="currency" required>
                                <option value="KES">KES</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success"><i class="fas fa-check me-1"></i> Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        body { font-family: ui-sans-serif, system-ui, "Segoe UI", Roboto, Arial; }
        .table-hover tbody tr:hover { background-color:#f8f9fa; }
        .btn { border-radius:.5rem; }
        @media print {
            body * { visibility: hidden; }
            #printRoot, #printRoot * { visibility: visible; }
            #printRoot { position: absolute; left: 0; top: 0; width: 100%; }
            @page { size: A4 portrait; margin: 14mm; }
            .navbar, .btn, .modal { display:none !important; }
        }
    </style>

    <script>
        const fmt = n => Number(n||0).toLocaleString();
        function sumBucket(selector){
            let s = 0;
            document.querySelectorAll(selector).forEach(td => s += Number(td.dataset.amt||0));
            return s;
        }

        (function(){
            const total030  = sumBucket('td.amt[data-bucket="030"]');
            const total3160 = sumBucket('td.amt[data-bucket="3160"]');
            const total6190 = sumBucket('td.amt[data-bucket="6190"]');
            const total90p  = sumBucket('td.amt[data-bucket="90p"]');
            const grand     = sumBucket('td.amt-row-total');

            document.getElementById('t030').textContent   = fmt(total030);
            document.getElementById('t3160').textContent  = fmt(total3160);
            document.getElementById('t6190').textContent  = fmt(total6190);
            document.getElementById('t90p').textContent   = fmt(total90p);
            document.getElementById('tTotal').textContent = fmt(grand);

            document.getElementById('sumTotal').textContent = 'KSh ' + fmt(grand);
            document.getElementById('sum90p').textContent   = 'KSh ' + fmt(total90p);
            document.getElementById('sum030').textContent   = 'KSh ' + fmt(total030);
            document.getElementById('sum31to90').textContent= 'KSh ' + fmt(total3160 + total6190);

            const asOf = document.querySelector('#agingTable tbody tr td:nth-child(3)')?.textContent?.trim();
            if (asOf) document.getElementById('asOfDate').textContent = asOf;

            document.getElementById('btnPrint').onclick = () => window.print();
            document.getElementById('btnExport').onclick = () => {
                let csv = [], rows = document.querySelectorAll('#agingTable tr');
                rows.forEach(row => {
                    let cols = Array.from(row.cells).map(c => `"${c.innerText.trim()}"`);
                    csv.push(cols.join(','));
                });
                let blob = new Blob([csv.join('\n')], {type:'text/csv'});
                let a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'ap_aging_report.csv';
                a.click();
            };
        })();
    </script>
@endsection
