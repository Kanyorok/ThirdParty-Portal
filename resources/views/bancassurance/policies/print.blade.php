<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insurance Policy Offer Letter</title>

    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
            color: #000;
            line-height: 1.8;
            background: #fff;
            padding: 40px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta {
            margin-bottom: 20px;
        }

        .meta strong {
            width: 140px;
            display: inline-block;
        }

        .section-title {
            margin-top: 30px;
            margin-bottom: 10px;
            font-weight: 700;
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
            font-weight: 700;
        }

        .signature-section {
            margin-top: 50px;
        }

        .signature-block {
            margin-top: 35px;
        }

        .signature-line {
            margin-top: 25px;
            border-top: 1px solid #000;
            width: 60%;
        }

        .footer-note {
            margin-top: 40px;
            font-size: 12px;
            font-style: italic;
            text-align: center;
        }

        @media print {
            body {
                padding: 20mm;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Insurance Policy Offer Letter</h2>

    <div class="meta">
        <p><strong>Date:</strong> {{ now()->format('d/m/Y') }}</p>
        <p><strong>To:</strong> {{ $policy->customer->thirdParty->ThirdPartyName }}</p>
        <p><strong>Policy Product:</strong> {{ $policy->product->Name }}</p>
    </div>

    <p>
        Dear {{ $policy->customer->thirdParty->ThirdPartyName }},
    </p>

    <p>
        We are pleased to formally extend to you an offer of insurance cover for the
        above-mentioned product under the terms and conditions outlined below.
        This offer summarizes the coverage details, financial obligations, and policy
        duration governing the contract.
    </p>

    <div class="section-title">Policy Terms & Financial Summary</div>

    <table>
        <thead>
        <tr>
            <th>Description</th>
            <th>Amount / Details</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Sum Assured</td>
            <td>{{ number_format($policy->SumAssured, 2) }}</td>
        </tr>
        <tr>
            <td>Premium Amount</td>
            <td>{{ number_format($policy->PremiumAmount, 2) }}</td>
        </tr>
        <tr>
            <td>Payment Frequency</td>
            <td>{{ $policy->paymentFrequency->Description ?? '-' }}</td>
        </tr>
        <tr>
            <td>Policy Duration</td>
            <td>
                {{ $policy->PolicyStartDate->format('d/m/Y') }}
                –
                {{ $policy->PolicyEndDate->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td>Policy Status</td>
            <td>{{ $policy->Status }}</td>
        </tr>
        </tbody>
    </table>

    @if($policy->riderAddOn)
        <div class="section-title">Special Terms / Rider Add-On</div>
        <p>
            {{ $policy->riderAddOn->RiderName }}
        </p>
    @endif

    <p>
        Kindly review the terms outlined above. If acceptable, please sign in the space
        provided below and return the signed copy to our office. Upon acceptance, a
        formal insurance policy document shall be issued for execution.
    </p>

    <p>
        We look forward to providing you with reliable insurance coverage and
        value you as a client.
    </p>

    <p>
        Yours faithfully,<br>
        <strong>{{ $policy->insurer->Name }}</strong><br>
        Insurance Management Team
    </p>

    <div class="signature-section">
        <div class="signature-block">
            ______________________________<br>
            <strong>Policy Holder Signature</strong><br>
            Name: {{ $policy->customer->thirdParty->ThirdPartyName }}<br>
            Date: __________________
        </div>

        <div class="signature-block">
            ______________________________<br>
            <strong>Authorized Signatory</strong><br>
            Name: ________________________<br>
            Date: __________________
        </div>
    </div>

    <div class="footer-note">
        This document is system-generated and does not require a physical company stamp.
    </div>

</div>
<script>
    window.onload = () => window.print();
    window.onafterprint = () => window.close();
</script>
</body>
</html>


