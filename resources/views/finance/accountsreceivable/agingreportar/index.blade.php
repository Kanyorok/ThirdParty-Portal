@extends('layouts.app')
@section('title', 'Aging Report — Accounts Receivable')

@section('content')
    <div class="container my-3">
        <!-- Header actions -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h5 class="mb-0">
                <i class="fas fa-calendar-alt text-info me-2"></i> Accounts Receivable Aging Report
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
                        <div class="small text-muted">As of <span id="asOfDate">2025-05-01</span></div>
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
                            <th class="text-start">Customer</th>
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
                                <div class="fw-semibold">ABC Distributors</div>
                                <div class="small text-muted">abc@distributors.co.ke • +254 700 000 111</div>
                            </td>
                            <td class="text-center">2025-05-01</td>
                            <td class="text-end amt" data-bucket="030" data-amt="25000">25,000</td>
                            <td class="text-end amt" data-bucket="3160" data-amt="10000">10,000</td>
                            <td class="text-end amt" data-bucket="6190" data-amt="5000">5,000</td>
                            <td class="text-end amt text-danger" data-bucket="90p" data-amt="2500">2,500</td>
                            <td class="text-end amt-row-total" data-amt="42500">42,500</td>
                            <td class="text-center">
                                <a href="{{ route('agingreportar.show', 1) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center">2</td>
                            <td class="text-start">
                                <div class="fw-semibold">XYZ Enterprises</div>
                                <div class="small text-muted">info@xyz.co.ke • +254 711 222 333</div>
                            </td>
                            <td class="text-center">2025-05-01</td>
                            <td class="text-end amt" data-bucket="030" data-amt="18000">18,000</td>
                            <td class="text-end amt" data-bucket="3160" data-amt="7500">7,500</td>
                            <td class="text-end amt" data-bucket="6190" data-amt="3000">3,000</td>
                            <td class="text-end amt text-danger" data-bucket="90p" data-amt="1200">1,200</td>
                            <td class="text-end amt-row-total" data-amt="29700">29,700</td>
                            <td class="text-center">
                                <a href="{{ route('agingreportar.show', 2) }}" class="btn btn-sm btn-outline-info">
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
                    <strong>Legend:</strong> 0–30 = current, 31–60/61–90 = overdue, 90+ = high risk
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
                        <i class="fas fa-cogs me-2"></i> Generate Aging Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('agingreportar.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Report Date</label>
                            <input type="date" class="form-control" name="reportDate" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Customer (optional)</label>
                            <input type="text" class="form-control" name="customer" placeholder="Search customer...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Branch (optional)</label>
                            <select class="form-select" name="branch">
                                <option value="">All</option>
                                <option value="Nairobi">Nairobi</option>
                                <option value="Mombasa">Mombasa</option>
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
        body {
            font-family: ui-sans-serif, system-ui, "Segoe UI", Roboto, Arial;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn {
            border-radius: .5rem;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #printRoot, #printRoot * {
                visibility: visible;
            }

            #printRoot {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            @page {
                size: A4 portrait;
                margin: 14mm;
            }

            .navbar, .btn, .modal {
                display: none !important;
            }
        }
    </style>

    <script>
        document.getElementById('btnPrint').onclick = () => window.print();
        document.getElementById('btnExport').onclick = () => {
            let csv = [], rows = document.querySelectorAll('#agingTable tr');
            rows.forEach(row => {
                let cols = Array.from(row.cells).map(c => `"${c.innerText.trim()}"`);
                csv.push(cols.join(','));
            });
            let blob = new Blob([csv.join('\n')], {type: 'text/csv'});
            let a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'aging_report.csv';
            a.click();
        };
    </script>
@endsection
