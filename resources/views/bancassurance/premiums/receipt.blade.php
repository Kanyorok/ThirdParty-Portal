<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Premium Payment Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }

        body {
            padding: 30px;
        }

        .receipt-box {
            border: 1px solid #ccc;
            padding: 25px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="receipt-box">
    <div class="d-flex justify-content-between mb-3">
        <h4>🏦 Bancassurance Premium Receipt</h4>
        <button onclick="window.print()" class="btn btn-primary btn-sm no-print">🖨️ Print</button>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-6">
            <p><strong>Receipt No:</strong> {{ $payment->Id }}</p>
            <p><strong>Policy Number:</strong> {{ $payment->policies->PolicyNumber }}</p>
            <p><strong>CustomerId:</strong> {{ $payment->CustomerID }}</p>

        </div>
        <div class="col-md-6">
            <p><strong>Payment Date:</strong> {{ \Carbon\Carbon::parse($payment->PaymentDate)->format('d M Y') }}</p>
            <p><strong>Payment Mode:</strong> {{ $payment->paymentmodes->Description }}</p>
        </div>
    </div>

    <hr>

    <div class="text-center">
        <h2>KES {{ number_format($payment->Amount, 2) }}</h2>
        <p class="text-muted">Amount Paid</p>
    </div>

    <hr>

    <p class="text-muted text-center mb-0">
        This is a system-generated receipt for insurance premium collection.
    </p>
</div>

</body>
</html>
