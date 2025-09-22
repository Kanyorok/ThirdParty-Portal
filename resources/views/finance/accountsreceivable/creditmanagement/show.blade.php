@extends('layouts.app')
@section('title','Credit Profile')

@section('content')
    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <a href="{{ route('creditmanagement.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('creditmanagement.edit',1) }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <button class="btn btn-sm btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>

        <div id="printRoot" class="card shadow-sm rounded-4 border-0 p-3 p-md-4">
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start">
                <div>
                    <div class="h5 mb-0">Credit Profile — <span class="fw-semibold">ABC Properties Ltd</span></div>
                    <div class="small text-muted">ID: 12345678 • abc@props.co.ke • +254 722 555 000</div>
                </div>
                <div class="text-md-end mt-2 mt-md-0">
                    <div class="small text-muted">Status</div>
                    <div><span class="badge bg-success">Active</span></div>
                    <div class="small text-muted mt-2">Last Review: 2025-08-01</div>
                </div>
            </div>

            <!-- KPIs -->
            <div class="row g-3 mt-3">
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Credit Limit</div>
                        <div class="fs-5 fw-semibold">KSh 5,000,000.00</div>
                        <div class="small text-muted">Terms: Net 30</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Used</div>
                        <div class="fs-5 fw-semibold text-danger">KSh 3,100,000.00</div>
                        <div class="small text-muted">Open AR</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Available</div>
                        <div class="fs-5 fw-semibold text-success">KSh 1,900,000.00</div>
                        <div class="small text-muted">As of 2025-08-20</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="small text-muted">Risk</div>
                        <div><span class="badge bg-warning text-dark">Medium</span> <span class="small text-muted">(Score 55)</span>
                        </div>
                        <div class="small text-muted mt-1">Review Cycle: 6 months</div>
                    </div>
                </div>
            </div>

            <!-- Utilization bar -->
            <div class="mt-3">
                <div class="d-flex justify-content-between">
                    <div class="small text-muted">Utilization</div>
                    <div class="small text-muted">62%</div>
                </div>
                <div class="progress" style="height:10px;">
                    <div class="progress-bar bg-warning" style="width:62%"></div>
                </div>
            </div>

            <!-- Aging & exposure -->
            <div class="row g-3 mt-3">
                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small text-uppercase mb-2">Aging (KES)</div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>Bucket</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>0–30</td>
                                    <td class="text-end">KSh 1,200,000.00</td>
                                </tr>
                                <tr>
                                    <td>31–60</td>
                                    <td class="text-end">KSh 1,100,000.00</td>
                                </tr>
                                <tr>
                                    <td>61–90</td>
                                    <td class="text-end">KSh 500,000.00</td>
                                </tr>
                                <tr class="table-light">
                                    <th>Total</th>
                                    <th class="text-end">KSh 2,800,000.00</th>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="small text-muted mt-2">Max Overdue Allowed: 30 days</div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">
                        <div class="text-muted small text-uppercase mb-2">Collateral / Notes</div>
                        <div class="small">Security: Bank Guarantee — KSh 1,000,000 valid to 2026‑12‑31</div>
                        <div class="small mt-2">Remarks: Good payment history, seasonal spikes in Q4.</div>
                        <div class="small mt-2">Allow Over‑Limit: <strong>With Approval</strong></div>
                    </div>
                </div>
            </div>

            <!-- Recent credit events -->
            <div class="card border-0 shadow-sm rounded-4 mt-3">
                <div class="card-body">
                    <div class="text-muted small text-uppercase mb-2">Recent Events</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>User</th>
                                <th class="text-end">Delta</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>2025-08-01</td>
                                <td>Profile reviewed, risk from Low→Medium</td>
                                <td>jane.nduta</td>
                                <td class="text-end">—</td>
                            </tr>
                            <tr>
                                <td>2025-05-10</td>
                                <td>Limit increased</td>
                                <td>daniel.m</td>
                                <td class="text-end">+ KSh 1,000,000.00</td>
                            </tr>
                            <tr>
                                <td>2025-02-02</td>
                                <td>Limit set</td>
                                <td>daniel.m</td>
                                <td class="text-end">KSh 4,000,000.00</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('styles')
    <style>
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif;
        }

        body, .card, .table {
            font-family: var(--font-sans);
        }

        .card {
            border: none;
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

            .btn, .navbar {
                display: none !important;
            }

            .shadow-sm {
                box-shadow: none !important;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        // (Optional) You can add small interactivity here later if needed.
    </script>
@endsection
