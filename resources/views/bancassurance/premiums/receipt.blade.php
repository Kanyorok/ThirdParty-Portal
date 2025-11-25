<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Premium Payment Receipt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
      font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      padding: 60px 20px;
      color: #212529;
    }

    .receipt-container {
      background: #fff;
      max-width: 800px;
      margin: auto;
      border-radius: 18px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      position: relative;
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .receipt-header {
      background: linear-gradient(90deg, #0d6efd, #004aad);
      color: #fff;
      padding: 25px 35px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .receipt-header h4 {
      margin: 0;
      font-weight: 700;
      font-size: 1.4rem;
      letter-spacing: 0.3px;
    }

    .logo {
      height: 48px;
    }

    .btn-print {
      position: absolute;
      top: 20px;
      right: 20px;
      border-radius: 8px;
      z-index: 100;
    }

    .receipt-body {
      padding: 35px;
    }

    .section-title {
      color: #0d6efd;
      font-weight: 600;
      border-left: 4px solid #0d6efd;
      padding-left: 10px;
      margin-bottom: 15px;
      font-size: 1rem;
    }

    .details p {
      font-size: 15px;
      margin-bottom: 6px;
    }

    .amount-box {
      text-align: center;
      background: linear-gradient(145deg, #f3f9ff, #ffffff);
      border: 1px solid #e3f2fd;
      border-radius: 14px;
      padding: 30px;
      margin: 40px 0;
    }

    .amount-box h2 {
      color: #004aad;
      font-weight: 800;
      font-size: 2.2rem;
    }

    .amount-box p {
      color: #6c757d;
      font-size: 0.95rem;
      margin-top: 5px;
    }

    .footer-note {
      text-align: center;
      font-size: 0.9rem;
      color: #6c757d;
      border-top: 1px solid #dee2e6;
      padding-top: 15px;
    }

    /* PRINT STYLES */
    @media print {
      @page {
        size: A4;
        margin: 20mm;
      }

      body {
        background: #fff !important;
        padding: 0;
      }

      .receipt-container {
        max-width: 100%;
        margin: 0;
        border-radius: 0;
        box-shadow: none;
        border: 1px solid #ddd;
      }

      .receipt-header {
        background: #004aad !important;
        color: #fff !important;
        -webkit-print-color-adjust: exact;
      }

      .section-title {
        color: #004aad !important;
        border-left: 4px solid #004aad !important;
        -webkit-print-color-adjust: exact;
      }

      .amount-box {
        background: #eef5ff !important;
        -webkit-print-color-adjust: exact;
        border-color: #aac8ff !important;
      }

      .btn-print {
        display: none !important;
      }
    }
  </style>
</head>

<body>
  <div class="receipt-container">
    <button onclick="window.print()" class="btn btn-primary btn-sm btn-print">
      🖨️ Print Receipt
    </button>

    <div class="receipt-header">
      <div>
        <h4>🏦 Bancassurance Premium Receipt</h4>
        <small class="text-light">Official Payment Confirmation</small>
      </div>
      {{-- <img src="https://via.placeholder.com/140x40?text=Your+Logo" alt="Company Logo" class="logo"> --}}
    </div>

    <div class="receipt-body">

      <div class="row details mb-4">
        <div class="col-md-6">
          <div class="section-title">Payment Details</div>
          <p><strong>Receipt No:</strong> {{ $payment->Id }}</p>
          <p><strong>Policy Number:</strong> {{ $payment->policies->PolicyNumber }}</p>
          <p><strong>Customer ID:</strong> {{ $payment->CustomerID }}</p>
        </div>
        <div class="col-md-6">
          <div class="section-title">Transaction Info</div>
          <p><strong>Payment Date:</strong> {{ \Carbon\Carbon::parse($payment->PaymentDate)->format('d/m/Y') }}</p>
          <p><strong>Payment Mode:</strong> {{ $payment->paymentmodes->Description }}</p>
        </div>
      </div>

      <div class="amount-box">
        <h2>{{ number_format((float)$payment->Amount, 2) }}</h2>
        <p>Amount Paid</p>
      </div>

      <div class="footer-note">
        This receipt serves as proof of premium payment.<br>
        Generated automatically by the Bancassurance System.
      </div>
    </div>
  </div>
</body>

</html>