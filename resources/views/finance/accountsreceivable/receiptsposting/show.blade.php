@extends('layouts.app')
@section('title','Receipt View')

@section('content')
    @php
        // ===== Dummy payload (replace with real data from controller) =====
        $company = [
            'name'    => 'Craft Silicon Limited',
            'tagline' => 'Financial Technology • Core Banking • Digital Channels',
            'address' => "Craft Silicon Campus, Musa Gitau Rd, off Waiyaki Way\nP.O. Box 13628-00800, Nairobi, Kenya",
            'email'   => 'info.kenya@craftsilicon.com',
            'phone'   => '+254 709 044 000',
        ];

        $receipt = [
            'receiptNo'      => 'RCPT-2025-001',
            'date'           => '2025-08-20',
            'customerName'   => 'ABC Properties Ltd',
            'customerID'     => '12345678',
            'paymentMethod'  => 'Bank Transfer',
            'referenceNo'    => 'TRX-883728',
            'remarks'        => 'Payment allocated across multiple invoices.',
            'currencySymbol' => 'KSh',
            'amountReceived' => 20000.00,
            'preparedBy'     => 'Finance Officer',
            'authorizedBy'   => 'Finance Manager',
        ];

        $allocations = [
            ['invoiceNo' => 'INV-2025-0001', 'invoiceAmount' => 13920.00, 'balanceBefore' => 13920.00, 'applied' => 13920.00],
            ['invoiceNo' => 'INV-2025-0002', 'invoiceAmount' => 2320.00,  'balanceBefore' => 2320.00,  'applied' =>  2080.00],
        ];

        $appliedTotal = array_sum(array_map(fn($a) => $a['applied'], $allocations));
        $unapplied    = max(0, $receipt['amountReceived'] - $appliedTotal); // carry as credit if > 0
    @endphp

    <div class="container my-3">
        <!-- Actions -->
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2">
            <a href="{{ route('receiptsposting.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" onclick="printA4()">
                    <i class="fas fa-print me-1"></i> Print A4
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="printSlip()">
                    <i class="fas fa-receipt me-1"></i> Print Slip
                </button>
            </div>
        </div>

        <div class="row g-3">
            <!-- ========= A4 RECEIPT ========= -->
            <div class="col-12">
                <div id="receiptA4" class="card shadow-sm border-0 p-4 rounded-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="h4 mb-0">{{ $company['name'] }}</div>
                            <div class="small text-muted">{{ $company['tagline'] }}</div>
                            <div class="small mt-1" style="white-space:pre-line">{{ $company['address'] }}</div>
                            <div class="small text-muted mt-1">{{ $company['email'] }} • {{ $company['phone'] }}</div>
                        </div>
                        <div class="text-end">
                            <div class="h5 fw-semibold mb-0">RECEIPT</div>
                            <div class="small text-muted">No.</div>
                            <div class="fs-6 fw-semibold">{{ $receipt['receiptNo'] }}</div>
                            <div class="small text-muted mt-1">Date</div>
                            <div class="fw-medium">{{ $receipt['date'] }}</div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Parties -->
                    <div class="row small">
                        <div class="col-md-6">
                            <div class="text-muted text-uppercase">Received From</div>
                            <div class="fw-semibold">{{ $receipt['customerName'] }}</div>
                            <div>ID Number: <span class="text-muted">{{ $receipt['customerID'] }}</span></div>
                        </div>
                        <div class="col-md-6 text-md-end mt-2 mt-md-0">
                            <div class="text-muted text-uppercase">Payment Details</div>
                            <div>Method: <span class="fw-medium">{{ $receipt['paymentMethod'] }}</span></div>
                            <div>Reference: <span class="text-muted">{{ $receipt['referenceNo'] }}</span></div>
                        </div>
                    </div>

                    <!-- Amount / Summary -->
                    <div class="row g-3 mt-3">
                        <div class="col-lg-7">
                            <div class="border rounded-3 p-3">
                                <div class="text-muted text-uppercase small mb-2">Applied to Invoices</div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Invoice</th>
                                            <th class="text-end">Invoice Amount</th>
                                            <th class="text-end">Balance Before</th>
                                            <th class="text-end">Amount Applied</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($allocations as $row)
                                            <tr>
                                                <td class="fw-medium">{{ $row['invoiceNo'] }}</td>
                                                <td class="text-end">{{ $receipt['currencySymbol'] }} {{ number_format($row['invoiceAmount'],2) }}</td>
                                                <td class="text-end">{{ $receipt['currencySymbol'] }} {{ number_format($row['balanceBefore'],2) }}</td>
                                                <td class="text-end text-success fw-semibold">{{ $receipt['currencySymbol'] }} {{ number_format($row['applied'],2) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" class="text-end">Applied Total</th>
                                            <th class="text-end">{{ $receipt['currencySymbol'] }} {{ number_format($appliedTotal,2) }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Amount Received</th>
                                            <th class="text-end">{{ $receipt['currencySymbol'] }} {{ number_format($receipt['amountReceived'],2) }}</th>
                                        </tr>
                                        @if($unapplied > 0)
                                            <tr>
                                                <th colspan="3" class="text-end">Unapplied (Customer Credit)</th>
                                                <th class="text-end text-warning">{{ $receipt['currencySymbol'] }} {{ number_format($unapplied,2) }}</th>
                                            </tr>
                                        @endif
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="small text-muted mt-2">Remarks: {{ $receipt['remarks'] }}</div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                                <div class="text-muted text-uppercase small">Amount in Words</div>
                                <div class="fw-medium">Twenty Thousand Kenya Shillings Only</div>

                                <div class="row small mt-3">
                                    <div class="col-6">
                                        <div class="text-muted">Prepared By</div>
                                        <div class="fw-medium">{{ $receipt['preparedBy'] }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Authorized By</div>
                                        <div class="fw-medium">{{ $receipt['authorizedBy'] }}</div>
                                    </div>
                                </div>

                                <div class="mt-auto small text-muted pt-2">
                                    Please quote receipt number <strong>{{ $receipt['receiptNo'] }}</strong> on all
                                    correspondence.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tear-off Remittance (print only) -->
                    <hr class="my-4 print-only">
                    <div class="border rounded-3 p-2 small print-only">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">Remittance Advice</div>
                                <div class="text-muted">Attach with payment</div>
                            </div>
                            <div>
                                <div><strong>Receipt:</strong> {{ $receipt['receiptNo'] }}</div>
                                <div>
                                    <strong>Amount:</strong> {{ $receipt['currencySymbol'] }} {{ number_format($receipt['amountReceived'],2) }}
                                </div>
                                <div><strong>Date:</strong> {{ $receipt['date'] }}</div>
                            </div>
                        </div>
                        <div class="mt-1">{{ $company['name'] }} • {{ $company['phone'] }}</div>
                    </div>
                </div>
            </div>

            <!-- ========= THERMAL/POS SLIP ========= -->
            <div class="col-12">
                <div id="receiptSlip" class="card shadow-sm border-0 p-3 rounded-4 d-none">
                    <div class="slip">
                        <div class="slip-title">{{ $company['name'] }}</div>
                        <div class="slip-sub">{{ $company['phone'] }} • {{ $company['email'] }}</div>
                        <div class="slip-sep"></div>

                        <div class="slip-row"><span>RECEIPT</span><span>#{{ $receipt['receiptNo'] }}</span></div>
                        <div class="slip-row"><span>Date</span><span>{{ $receipt['date'] }}</span></div>
                        <div class="slip-sep"></div>

                        <div class="slip-row"><span>Customer</span><span>{{ $receipt['customerName'] }}</span></div>
                        <div class="slip-row"><span>ID</span><span>{{ $receipt['customerID'] }}</span></div>
                        <div class="slip-row"><span>Method</span><span>{{ $receipt['paymentMethod'] }}</span></div>
                        <div class="slip-row"><span>Ref</span><span>{{ $receipt['referenceNo'] }}</span></div>
                        <div class="slip-sep"></div>

                        <div class="slip-row slip-bold">
                            <span>Amount Received</span><span>{{ $receipt['currencySymbol'] }} {{ number_format($receipt['amountReceived'],2) }}</span>
                        </div>
                        <div class="slip-row">
                            <span>Applied</span><span>{{ $receipt['currencySymbol'] }} {{ number_format($appliedTotal,2) }}</span>
                        </div>
                        @if($unapplied > 0)
                            <div class="slip-row">
                                <span>Unapplied</span><span>{{ $receipt['currencySymbol'] }} {{ number_format($unapplied,2) }}</span>
                            </div>
                        @endif
                        <div class="slip-sep"></div>

                        <div class="slip-sub">Invoices</div>
                        @foreach($allocations as $row)
                            <div class="slip-row">
                                <span>{{ $row['invoiceNo'] }}</span><span>{{ $receipt['currencySymbol'] }} {{ number_format($row['applied'],2) }}</span>
                            </div>
                        @endforeach
                        <div class="slip-sep"></div>

                        <div class="slip-center">Thank you!</div>
                        <div class="slip-center">— Keep this for your records —</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* System font stack (no CDN) */
        :root {
            --font-sans: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, Cantarell, "Noto Sans", "Helvetica Neue", Arial, sans-serif;
        }

        body, .card, .table {
            font-family: var(--font-sans);
        }

        .table td, .table th {
            vertical-align: middle;
        }

        /* Print helpers */
        .print-only {
            display: none;
        }

        @media print {
            .print-only {
                display: block !important;
            }
        }

        /* A4 print rules */
        @media print {
            body * {
                visibility: hidden;
            }

            .print-a4, .print-a4 * {
                visibility: visible;
            }

            .print-a4 {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            @page {
                size: A4 portrait;
                margin: 14mm;
            }

            .navbar, .btn {
                display: none !important;
            }

            .card, .shadow-sm {
                box-shadow: none !important;
                border: none !important;
            }
        }

        /* Slip layout (screen) */
        .slip {
            width: 320px; /* looks like 80mm roll on screen */
            margin: 0 auto;
            font-size: 13px;
        }

        .slip-title {
            text-align: center;
            font-weight: 700;
        }

        .slip-sub {
            text-align: center;
            color: #6c757d;
            font-size: 12px;
        }

        .slip-sep {
            border-top: 1px dashed #999;
            margin: 6px 0;
        }

        .slip-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .slip-bold {
            font-weight: 700;
        }

        .slip-center {
            text-align: center;
        }

        /* Slip print rules */
        @media print {
            .print-slip, .print-slip * {
                visibility: visible;
            }

            .print-slip {
                position: absolute;
                left: 0;
                top: 0;
                width: 58mm; /* adjust to 80mm for wider rolls */
                padding: 2mm 2mm 0 2mm;
            }

            @page {
                size: 58mm auto;
                margin: 0;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        function printA4() {
            // show A4, hide Slip
            document.getElementById('receiptA4').classList.remove('d-none');
            document.getElementById('receiptSlip').classList.add('d-none');

            // tag A4 as print root
            document.getElementById('receiptA4').classList.add('print-a4');
            document.getElementById('receiptSlip').classList.remove('print-slip');

            window.print();

            // cleanup
            document.getElementById('receiptA4').classList.remove('print-a4');
        }

        function printSlip() {
            // show Slip, hide A4
            document.getElementById('receiptSlip').classList.remove('d-none');
            document.getElementById('receiptA4').classList.add('d-none');

            // tag Slip as print root
            document.getElementById('receiptSlip').classList.add('print-slip');
            document.getElementById('receiptA4').classList.remove('print-a4');

            window.print();

            // cleanup
            document.getElementById('receiptSlip').classList.remove('print-slip');
            // bring A4 back after print for on-screen viewing (optional)
            document.getElementById('receiptA4').classList.remove('d-none');
        }
    </script>
@endsection
