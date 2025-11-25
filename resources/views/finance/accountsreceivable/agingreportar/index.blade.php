@extends('layouts.app')
@section('title', 'Aging Report — Accounts Receivable')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        body { font-family: ui-sans-serif, system-ui, "Segoe UI", Roboto, Arial; overflow-x: hidden; }
        .table-hover tbody tr:hover { background-color:#f8f9fa; }
        .btn { border-radius:.5rem; }
        .filter-card .form-label { font-size:.8rem; text-transform:uppercase; letter-spacing:.5px; }
        .filter-card input,
        .filter-card .select2-selection { border-radius:.6rem !important; }
        .summary-card .card-body { min-height:110px; }
        #printTableWrapper .card { border:none; }
        @media print {
            body * { visibility: hidden; }
            #printTableWrapper, #printTableWrapper * { visibility: visible; }
            #printTableWrapper { position:absolute; left:0; top:0; width:100%; margin:0; padding:0; }
            @page { size: A4 portrait; margin:12mm; }
        }
    </style>
@endsection

@section('content')
    <div class="container my-3">
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Unable to generate report</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt text-info me-2"></i> Accounts Receivable Aging Report
                </h5>
                <div class="small text-muted">
                    Showing invoices due between <strong>{{ $filters['from_date'] }}</strong> and <strong>{{ $filters['to_date'] }}</strong>
                    @if($selectedCustomer)
                        for <strong>{{ $selectedCustomer->ThirdPartyName }}</strong>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" id="btnPrint">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 d-print-none filter-card">
            <div class="card-body">
                <form id="filterForm" action="{{ route('agingreportar.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label text-muted">Due Date From</label>
                            <input type="date" class="form-control" name="from_date" value="{{ $filters['from_date'] }}" required>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label text-muted">Due Date To</label>
                            <input type="date" class="form-control" name="to_date" value="{{ $filters['to_date'] }}" required>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <label class="form-label text-muted">Creditor (optional)</label>
                            <select class="form-select w-100" id="customerSelect" name="customer_id" data-placeholder="All creditors">
                                <option value=""></option>
                            </select>
                            <small class="text-muted">Search by name, email or phone.</small>
                        </div>
                        <div class="col-md-2 col-sm-6 text-md-end">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-sync me-1"></i> Apply
                            </button>
                            @if($filters['customer_id'])
                                <a href="{{ route('agingreportar.index', ['from_date' => $filters['from_date'], 'to_date' => $filters['to_date']]) }}" class="btn btn-link p-0 mt-2 d-block text-decoration-none">Clear creditor</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="row g-3 mb-3 d-print-none">
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100 summary-card">
                        <div class="card-body">
                            <div class="small text-muted">Total Outstanding</div>
                            <div class="h5 fw-semibold mb-0">KSh {{ number_format($totals['overall'] ?? 0, 2) }}</div>
                            <div class="small text-muted">As of {{ $asOfDate }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100 summary-card">
                        <div class="card-body">
                            <div class="small text-muted">> 90 Days</div>
                            <div class="h5 fw-semibold mb-0 text-danger">KSh {{ number_format($totals['bucket_90p'] ?? 0, 2) }}</div>
                            <div class="small text-muted">High risk bucket</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100 summary-card">
                        <div class="card-body">
                            <div class="small text-muted">0–30 Days</div>
                            <div class="h5 fw-semibold mb-0">KSh {{ number_format($totals['bucket_030'] ?? 0, 2) }}</div>
                            <div class="small text-muted">Current</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100 summary-card">
                        <div class="card-body">
                            <div class="small text-muted">31–90 Days</div>
                            @php($midBucket = ($totals['bucket_3160'] ?? 0) + ($totals['bucket_6190'] ?? 0))
                            <div class="h5 fw-semibold mb-0">KSh {{ number_format($midBucket, 2) }}</div>
                            <div class="small text-muted">Near overdue</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded-3 border-0" id="printTableWrapper">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 d-print-none">
                        <div>
                            <div class="fw-semibold">Filter Summary</div>
                            <div class="small text-muted">
                                Due dates from {{ $filters['from_date'] }} to {{ $filters['to_date'] }}
                                @if($selectedCustomer)
                                    • Creditor: {{ $selectedCustomer->ThirdPartyName }}
                                @else
                                    • All creditors
                                @endif
                            </div>
                        </div>
                        <div class="small text-muted">
                            Generated on {{ now()->format('Y-m-d H:i') }}
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0" id="agingTable">
                            <thead class="table-light">
                            <tr class="text-center">
                                <th>#</th>
                                <th class="text-start">Creditor</th>
                                <th>As of</th>
                                <th class="text-end">0–30 Days</th>
                                <th class="text-end">31–60 Days</th>
                                <th class="text-end">61–90 Days</th>
                                <th class="text-end">90+ Days</th>
                                <th class="text-end">Total Outstanding</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-start">
                                        <div class="fw-semibold">{{ $row->customer_name }}</div>
                                        <div class="small text-muted">
                                            {{ $row->customer_email ?? '—' }}
                                            @if($row->customer_phone)
                                                • {{ $row->customer_phone }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $asOfDate }}</td>
                                    <td class="text-end">{{ number_format($row->bucket_030, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->bucket_3160, 2) }}</td>
                                    <td class="text-end">{{ number_format($row->bucket_6190, 2) }}</td>
                                    <td class="text-end text-danger">{{ number_format($row->bucket_90p, 2) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($row->total_outstanding, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('agingreportar.show', ['agingreportar' => $row->CustomerID, 'from_date' => $filters['from_date'], 'to_date' => $filters['to_date']]) }}"
                                           class="btn btn-sm btn-outline-info" title="View invoices">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No invoices found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                            <tfoot>
                            <tr class="table-light fw-semibold">
                                <td colspan="3" class="text-end">Totals</td>
                                <td class="text-end">KSh {{ number_format($totals['bucket_030'] ?? 0, 2) }}</td>
                                <td class="text-end">KSh {{ number_format($totals['bucket_3160'] ?? 0, 2) }}</td>
                                <td class="text-end">KSh {{ number_format($totals['bucket_6190'] ?? 0, 2) }}</td>
                                <td class="text-end text-danger">KSh {{ number_format($totals['bucket_90p'] ?? 0, 2) }}</td>
                                <td class="text-end">KSh {{ number_format($totals['overall'] ?? 0, 2) }}</td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="small text-muted d-print-none">
                        <strong>Legend:</strong> 0–30 = current, 31–60/61–90 = overdue, 90+ = critical receivables
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        (function () {
            function wireActions() {
                const printBtn = document.getElementById('btnPrint');
                if (printBtn && !printBtn.dataset.bound) {
                    printBtn.dataset.bound = '1';
                    printBtn.addEventListener('click', () => window.print());
                }

                const preselectedCustomer = @json($selectedCustomer ? [
                    'id' => $selectedCustomer->Id,
                    'text' => trim($selectedCustomer->ThirdPartyName .
                        ($selectedCustomer->Email ? ' (' . $selectedCustomer->Email . ')' : ''))
                ] : null);

                if (window.jQuery && $('#customerSelect').length && !$('#customerSelect').hasClass('select2-hidden-accessible')) {
                    const $customerSelect = $('#customerSelect').select2({
                        allowClear: true,
                        placeholder: $('#customerSelect').data('placeholder'),
                        width: '100%',
                        ajax: {
                            url: "{{ route('agingreportar.customers.lookup') }}",
                            dataType: 'json',
                            delay: 250,
                            data: params => ({ q: params.term || '' }),
                            processResults: data => data,
                        }
                    });

                    if (preselectedCustomer) {
                        const option = new Option(preselectedCustomer.text, preselectedCustomer.id, true, true);
                        $customerSelect.append(option).trigger('change');
                    }
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', wireActions);
            } else {
                wireActions();
            }

            document.addEventListener('partial:loaded', wireActions);
        })();
    </script>
@endsection
