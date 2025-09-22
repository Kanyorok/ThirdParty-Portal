@extends('layouts.app')
@section('title','General Ledger')

<style>
    body, table, .card, .form-control, .form-select, .btn {
        font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    /* ---------- Cards & Layout ---------- */
    .gl-card {
        border-radius: .85rem;
        box-shadow: 0 8px 22px rgba(0, 0, 0, .06);
        border: 1px solid rgba(0, 0, 0, .06);
    }

    .gl-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .35rem .65rem;
        border-radius: .75rem;
        background: #f6f9ff;
        border: 1px solid #e6edff;
        font-weight: 600;
    }

    .gl-label {
        font-size: .78rem;
        color: #6c757d;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .toolbar .btn {
        border-radius: .65rem;
    }

    .balance-chip {
        padding: .25rem .55rem;
        border-radius: .5rem;
        background: #eef5ff;
        font-variant-numeric: tabular-nums;
    }

    .ref-jv {
        font-weight: 700;
        text-decoration: none;
    }

    .ref-jv:hover {
        text-decoration: underline;
    }

    /* ---------- Table ---------- */
    .table-ledger thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
        border-bottom: 2px solid #e9ecef;
    }

    .table-ledger tbody tr:hover {
        background: #f7f9ff;
    }

    /* ---------- Print Rules ---------- */
    @media print {
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        html, body {
            width: 210mm;
            height: 297mm;
        }

        .container, .container * {
            max-width: 100% !important;
        }

        .no-print, .no-print * {
            display: none !important;
        }

        .card, .gl-card {
            box-shadow: none !important;
            border: none !important;
        }

        /* Hide columns not needed in print */
        th.col-date, td.col-date,
        th.col-desc, td.col-desc,
        th.col-branch, td.col-branch,
        th.col-dept, td.col-dept {
            display: none !important;
        }

        th, td {
            font-size: 11pt;
        }
    }
</style>

@section('content')
    <div class="container my-3">

        <!-- Main Card -->
        <div class="card gl-card rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-book text-info me-2"></i> General Ledger
                </h6>

                <!-- Toolbar -->
                <div class="toolbar no-print d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            <div class="card-body p-3">

                <!-- Filters (no-print) -->
                <div class="gl-card p-3 mb-3 no-print">
                    <form method="GET" action="{{ url()->current() }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label gl-label">From Date</label>
                                <input type="date" name="FromDate" value="{{ old('FromDate', request('FromDate')) }}"
                                       class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label gl-label">To Date</label>
                                <input type="date" name="ToDate" value="{{ old('ToDate', request('ToDate')) }}"
                                       class="form-control rounded-3" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label gl-label">GL Account</label>
                                <select name="GLAccount" class="form-select rounded-3">
                                    <option value="All">All Accounts</option>
                                    @foreach($glAccount as $account)
                                        <option value="{{ $account->Id }}">
                                            {{ $account->GLName }} - {{ $account->GLCode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label gl-label">Branch</label>
                                <select name="Branch" class="form-select rounded-3">
                                    <option value="All">All</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->Id }}">
                                            {{ $branch->Name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label gl-label">Department</label>
                                <select name="Department" class="form-select rounded-3">
                                    <option value="All">All</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->Id }}">
                                            {{ $department->Name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-9 d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-sync me-1"></i> Generate
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Summary Strip (printable) -->
                <div class="gl-card p-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="gl-label mb-1">Account</div>
                            <div class="gl-chip"><i class="fas fa-list-alt"></i>
                                <span>{{ $glAccountLabel ?? 'All Accounts' }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="gl-label mb-1">Period</div>
                            <div class="gl-chip"><i class="far fa-calendar"></i>
                                <span>{{ $fromDate ?? request('FromDate') ?? '—' }} → {{ $toDate ?? request('ToDate') ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="gl-label mb-1">Branch</div>
                            <div class="gl-chip"><i class="fas fa-code-branch"></i>
                                <span>{{ $branchLabel ?? (request('Branch') ?: 'All') }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="gl-label mb-1">Department</div>
                            <div class="gl-chip"><i class="fas fa-sitemap"></i>
                                <span>{{ $departmentLabel ?? (request('Department') ?: 'All') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opening Balance -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">Opening Balance</div>
                    <div class="balance-chip">{{ number_format(($openingBalance ?? 0), 2) }}</div>
                </div>

                <!-- Ledger Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle text-center table-ledger">
                        <thead class="table-light">
                        <tr>
                            <th class="col-date">Date</th>
                            <th>Ref No.</th>
                            <th class="col-desc">Description</th>
                            <th class="col-branch">Branch</th>
                            <th class="col-dept">Department</th>
                            <th class="text-end">DR</th>
                            <th class="text-end">CR</th>
                            <th class="text-end">Running Balance</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- Dummy Row 1 --}}
                        @if($reports->count())
                            @foreach ($reports as $report)
                                <tr>
                                    <td class="col-date">{{ $report->TransactionDate  ?? '-'}}</td>
                                    <td>
                                        <a href="#" class="ref-jv">{{ $report->ReferenceNumber ?? '-'}}</a>
                                    </td>
                                    <td class="col-desc text-truncate" style="max-width:480px;"
                                        title="Cash received from customer">
                                        {{$report->SystemDescription ?? '-'}}
                                    </td>
                                    <td class="col-branch">{{$report->BranchID ?? '-'}}</td>
                                    <td class="col-dept">{{$report->DepartmentID ?? '-'}}</td>
                                    @if($report['DRCR'] === 'DR')
                                        <td class="text-end">{{ number_format($report->Amount, 2) }}</td>
                                    @else
                                        <td class="text-end">{{ number_format(0, 2) }}</td>
                                    @endif

                                    @if($report['DRCR'] === 'CR')
                                        <td class="text-end">{{ number_format($report->Amount, 2) }}</td>
                                    @else
                                        <td class="text-end">{{ number_format(0, 2) }}</td>
                                    @endif

                                    <td class="text-end"><span class="balance-chip">{{ number_format(0, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-muted">
                                    <div class="text-center p-4 border rounded-3 bg-light">
                                        <p class="mb-3 text-muted fs-5">
                                            <i class="fas fa-info-circle me-2 text-info"></i>
                                            <i>No transactions found for the selected criteria.</i>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            </tfoot>
                    </table>
                </div>

                <!-- Legend -->
                <div class="small text-muted mt-2">
                    DR = Debit, CR = Credit. JV references open the originating Journal Voucher.
                </div>
            </div>
        </div>
    </div>
@endsection
